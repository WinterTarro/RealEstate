<?php
// Include database configuration
require_once 'config.php';

/**
 * Sanitize user input
 * 
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

/**
 * Get all properties with optional filters
 * 
 * @param array $filters Optional filters
 * @return array Array of properties
 */
function getProperties($filters = []) {
    global $conn;
    
    $query = "SELECT p.*, u.name as seller_name, u.email as seller_email, u.phone as seller_phone 
              FROM properties p 
              JOIN users u ON p.seller_id = u.id WHERE 1=1";
    
    // Add filters if provided
    if (!empty($filters)) {
        if (isset($filters['min_price']) && !empty($filters['min_price'])) {
            $query .= " AND p.price >= " . floatval($filters['min_price']);
        }
        
        if (isset($filters['max_price']) && !empty($filters['max_price'])) {
            $query .= " AND p.price <= " . floatval($filters['max_price']);
        }
        
        if (isset($filters['bedrooms']) && !empty($filters['bedrooms'])) {
            $query .= " AND p.bedrooms >= " . intval($filters['bedrooms']);
        }
        
        if (isset($filters['bathrooms']) && !empty($filters['bathrooms'])) {
            $query .= " AND p.bathrooms >= " . intval($filters['bathrooms']);
        }
        
        if (isset($filters['property_type']) && !empty($filters['property_type'])) {
            $query .= " AND p.property_type = '" . sanitize($filters['property_type']) . "'";
        }
        
        if (isset($filters['city']) && !empty($filters['city'])) {
            $query .= " AND p.city LIKE '%" . sanitize($filters['city']) . "%'";
        }
        
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query .= " AND p.status = '" . sanitize($filters['status']) . "'";
        }
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = sanitize($filters['search']);
            $query .= " AND (p.title LIKE '%$search%' OR p.description LIKE '%$search%' OR p.address LIKE '%$search%' OR p.city LIKE '%$search%' OR p.state LIKE '%$search%')";
        }
        
        if (isset($filters['seller_id']) && !empty($filters['seller_id'])) {
            $query .= " AND p.seller_id = " . intval($filters['seller_id']);
        }
        
        if (isset($filters['featured']) && $filters['featured']) {
            $query .= " AND p.featured = 1";
        }
    }
    
    $query .= " ORDER BY p.created_at DESC";
    
    // Add limit if specified
    if (isset($filters['limit']) && !empty($filters['limit'])) {
        $query .= " LIMIT " . intval($filters['limit']);
    }
    
    $result = $conn->query($query);
    $properties = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
    }
    
    return $properties;
}

/**
 * Get a single property by ID
 * 
 * @param int $id Property ID
 * @return array Property data or empty array with default values if not found
 */
function getPropertyById($id) {
    global $conn;
    
    $id = intval($id);
    $query = "SELECT p.*, u.name as seller_name, u.email as seller_email, u.phone as seller_phone 
              FROM properties p 
              JOIN users u ON p.seller_id = u.id 
              WHERE p.id = $id";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    // Return default property structure instead of false
    return [
        'id' => $id,
        'seller_id' => 0,
        'title' => 'Unknown Property',
        'description' => '',
        'price' => 0,
        'bedrooms' => 0,
        'bathrooms' => 0,
        'area' => 0,
        'address' => '',
        'city' => '',
        'state' => '',
        'zip_code' => '',
        'latitude' => 0,
        'longitude' => 0,
        'property_type' => '',
        'status' => '',
        'featured' => 0,
        'image1' => '',
        'image2' => '',
        'image3' => '',
        'image4' => '',
        'created_at' => '',
        'updated_at' => '',
        'seller_name' => 'Unknown',
        'seller_email' => '',
        'seller_phone' => ''
    ];
}

/**
 * Get all user inquiries
 * 
 * @param int $userId User ID
 * @param string $role User role (buyer or seller)
 * @return array Array of inquiries
 */
function getUserInquiries($userId, $role, $filters = []) {
    global $conn;
    
    $userId = intval($userId);
    $whereConditions = [];
    
    if ($role == 'buyer') {
        $baseQuery = "SELECT i.*, p.title as property_title, p.image1, u.name as seller_name 
                     FROM inquiries i 
                     JOIN properties p ON i.property_id = p.id 
                     JOIN users u ON i.seller_id = u.id";
        $whereConditions[] = "i.buyer_id = $userId";
    } else if ($role == 'seller') {
        $baseQuery = "SELECT i.*, p.title as property_title, p.image1, u.name as buyer_name 
                     FROM inquiries i 
                     JOIN properties p ON i.property_id = p.id 
                     JOIN users u ON i.buyer_id = u.id";
        $whereConditions[] = "i.seller_id = $userId";
    } else if ($role == 'admin') {
        $baseQuery = "SELECT i.*, p.title as property_title, p.image1, 
                     ub.name as buyer_name, us.name as seller_name 
                     FROM inquiries i 
                     JOIN properties p ON i.property_id = p.id 
                     JOIN users ub ON i.buyer_id = ub.id 
                     JOIN users us ON i.seller_id = us.id";
    }
    
    // Add filters
    if (!empty($filters['status'])) {
        $whereConditions[] = "i.status = '" . sanitize($filters['status']) . "'";
    }
    
    if (!empty($filters['property_id'])) {
        $whereConditions[] = "i.property_id = " . intval($filters['property_id']);
    }
    
    // Combine conditions
    $query = $baseQuery;
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(" AND ", $whereConditions);
    }
    $query .= " ORDER BY i.status ASC, i.created_at DESC";
    
    $result = $conn->query($query);
    $inquiries = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $inquiries[] = $row;
        }
    }
    
    return $inquiries;
}

/**
 * Get favorite properties for a buyer
 * 
 * @param int $buyerId Buyer ID
 * @return array Array of favorite properties
 */
function getFavoriteProperties($buyerId) {
    global $conn;
    
    $buyerId = intval($buyerId);
    $query = "SELECT p.*, f.id as favorite_id 
              FROM favorites f 
              JOIN properties p ON f.property_id = p.id 
              WHERE f.buyer_id = $buyerId";
    
    $result = $conn->query($query);
    $favorites = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $favorites[] = $row;
        }
    }
    
    return $favorites;
}

/**
 * Check if a property is in user's favorites
 * 
 * @param int $propertyId Property ID
 * @param int $userId User ID
 * @return bool True if property is in favorites
 */
function isPropertyFavorite($propertyId, $userId) {
    global $conn;
    
    $propertyId = intval($propertyId);
    $userId = intval($userId);
    
    $query = "SELECT id FROM favorites WHERE property_id = $propertyId AND buyer_id = $userId";
    $result = $conn->query($query);
    
    return ($result && $result->num_rows > 0);
}

/**
 * Get all reports for admin
 * 
 * @return array Array of reports
 */
function getAllReports() {
    global $conn;
    
    $query = "SELECT r.*, p.title as property_title, u.name as reporter_name 
              FROM reports r 
              JOIN properties p ON r.property_id = p.id 
              JOIN users u ON r.user_id = u.id 
              ORDER BY r.status ASC, r.created_at DESC";
    
    $result = $conn->query($query);
    $reports = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reports[] = $row;
        }
    }
    
    return $reports;
}

/**
 * Get all users (for admin)
 * 
 * @param string $role Optional role filter
 * @return array Array of users
 */
function getAllUsers($role = '') {
    global $conn;
    
    $query = "SELECT * FROM users WHERE 1=1";
    
    if (!empty($role)) {
        $query .= " AND role = '" . sanitize($role) . "'";
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $result = $conn->query($query);
    $users = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
    return $users;
}

/**
 * Format currency
 * 
 * @param float $amount Amount to format
 * @return string Formatted amount
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Format date
 * 
 * @param string $date Date to format
 * @return string Formatted date
 */
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Generate JSON response
 * 
 * @param array $data Data to encode
 * @param int $status HTTP status code
 */
function jsonResponse($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

/**
 * Get property statistics for dashboard
 * 
 * @param int $sellerId Seller ID (optional)
 * @return array Statistics
 */
function getPropertyStatistics($sellerId = null) {
    global $conn;
    
    $stats = [
        'total' => 0,
        'for_sale' => 0,
        'for_rent' => 0,
        'sold' => 0,
        'rented' => 0
    ];
    
    $where = '';
    if ($sellerId) {
        $where = "WHERE seller_id = " . intval($sellerId);
    }
    
    $query = "SELECT status, COUNT(*) as count FROM properties $where GROUP BY status";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stats[$row['status']] = $row['count'];
            $stats['total'] += $row['count'];
        }
    }
    
    return $stats;
}

/**
 * Get inquiry statistics
 * 
 * @param int $userId User ID
 * @param string $role User role
 * @return array Statistics
 */
function getInquiryStatistics($userId, $role) {
    global $conn;
    
    $stats = [
        'total' => 0,
        'new' => 0,
        'read' => 0,
        'replied' => 0,
        'closed' => 0
    ];
    
    $userId = intval($userId);
    $field = ($role == 'buyer') ? 'buyer_id' : 'seller_id';
    
    $query = "SELECT status, COUNT(*) as count FROM inquiries WHERE $field = $userId GROUP BY status";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stats[$row['status']] = $row['count'];
            $stats['total'] += $row['count'];
        }
    }
    
    return $stats;
}

/**
 * Get admin dashboard statistics
 * 
 * @return array Statistics
 */
function getAdminStatistics() {
    global $conn;
    
    $stats = [
        'total_properties' => 0,
        'total_users' => 0,
        'total_inquiries' => 0,
        'pending_reports' => 0,
        'users_by_role' => [
            'buyer' => 0,
            'seller' => 0,
            'admin' => 0
        ],
        'properties_by_type' => [],
        'properties_by_status' => []
    ];
    
    // Get total properties
    $query = "SELECT COUNT(*) as count FROM properties";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_properties'] = $row['count'];
    }
    
    // Get total users
    $query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stats['users_by_role'][$row['role']] = $row['count'];
            $stats['total_users'] += $row['count'];
        }
    }
    
    // Get total inquiries
    $query = "SELECT COUNT(*) as count FROM inquiries";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_inquiries'] = $row['count'];
    }
    
    // Get pending reports
    $query = "SELECT COUNT(*) as count FROM reports WHERE status = 'pending'";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $stats['pending_reports'] = $row['count'];
    }
    
    // Get properties by type
    $query = "SELECT property_type, COUNT(*) as count FROM properties GROUP BY property_type";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stats['properties_by_type'][$row['property_type']] = $row['count'];
        }
    }
    
    // Get properties by status
    $query = "SELECT status, COUNT(*) as count FROM properties GROUP BY status";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stats['properties_by_status'][$row['status']] = $row['count'];
        }
    }
    
    return $stats;
}
?>