<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin authentication
requireAuth('admin');

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['property_id']) || !isset($data['action'])) {
    jsonResponse(['status' => 'error', 'message' => 'Missing required parameters'], 400);
}

$propertyId = intval($data['property_id']);
$action = $data['action'];

// Get the property
$property = getPropertyById($propertyId);
if (!$property) {
    jsonResponse(['status' => 'error', 'message' => 'Property not found'], 404);
}

// Handle seller applications
if ($action === 'approve_seller' || $action === 'reject_seller') {
    if (!isset($data['application_id']) || !isset($data['user_id'])) {
        jsonResponse(['status' => 'error', 'message' => 'Missing required parameters'], 400);
        exit;
    }

    $applicationId = intval($data['application_id']);
    $userId = intval($data['user_id']);

    if ($action === 'approve_seller') {
        if (!isset($data['application_id']) || !isset($data['user_id'])) {
            jsonResponse(['status' => 'error', 'message' => 'Missing required parameters'], 400);
            exit;
        }

        $applicationId = intval($data['application_id']);
        $userId = intval($data['user_id']);

        // Update application status and user role
        $conn->begin_transaction();
        try {
            // Update application status
            $appQuery = "UPDATE seller_applications SET status = 'approved' WHERE id = ?";
            $stmt = $conn->prepare($appQuery);
            $stmt->bind_param('i', $applicationId);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update application status");
            }

            // Update user role
            $roleQuery = "UPDATE users SET role = 'seller' WHERE id = ?";
            $stmt = $conn->prepare($roleQuery);
            $stmt->bind_param('i', $userId);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update user role");
            }

            $conn->commit();
            jsonResponse(['status' => 'success', 'message' => 'Seller application approved']);
        } catch (Exception $e) {
            $conn->rollback();
            jsonResponse(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
        }
        exit;
    } elseif ($action === 'reject_seller') {
        // Update application status only
        $query = "UPDATE seller_applications SET status = 'rejected' WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $applicationId);
        if ($stmt->execute()) {
            jsonResponse(['status' => 'success', 'message' => 'Seller application rejected']);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Database error'], 500);
        }
        exit;
    }
}

switch ($action) {
    case 'approve':
        // Get the listing type first
        $listingQuery = "SELECT listing_type FROM properties WHERE id = ?";
        $stmt = $conn->prepare($listingQuery);
        $stmt->bind_param('i', $propertyId);
        $stmt->execute();
        $result = $stmt->get_result();
        $listing = $result->fetch_assoc();
        
        // Update property status based on listing type
        $newStatus = ($listing && $listing['listing_type'] === 'rent') ? 'for_rent' : 'for_sale';
        $query = "UPDATE properties SET status = ? WHERE id = ?";
        break;

    case 'reject':
        // Delete the property
        $query = "DELETE FROM properties WHERE id = ?";
        break;

    case 'delete':
        // Mark property for deletion
        $query = "UPDATE properties SET status = 'pending_deletion' WHERE id = ?";
        break;

    case 'feature':
    case 'unfeature':
        $featured = ($action === 'feature') ? 1 : 0;
        $query = "UPDATE properties SET featured = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $featured, $propertyId);
        
        if ($stmt->execute()) {
            jsonResponse(['status' => 'success', 'message' => 'Property featured status updated']);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Database error'], 500);
        }
        exit;

    default:
        jsonResponse(['status' => 'error', 'message' => 'Invalid action'], 400);
}

$stmt = $conn->prepare($query);

if ($action === 'approve') {
    $stmt->bind_param('si', $newStatus, $propertyId);
} else {
    $stmt->bind_param('i', $propertyId);
}

if ($stmt->execute()) {
    jsonResponse(['status' => 'success', 'message' => 'Property ' . $action . 'd successfully']);
} else {
    jsonResponse(['status' => 'error', 'message' => 'Database error'], 500);
}