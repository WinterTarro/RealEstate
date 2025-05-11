<?php
// Detect environment
$isLocalEnvironment = (strpos(__DIR__, '/home/runner') === false);

// Database configuration
if ($isLocalEnvironment) {
    // Local XAMPP environment with MySQL
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'real_estate');
    
    // Establish database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set character set
    $conn->set_charset("utf8");
} else {
    // Replit environment - create a dummy connection for demonstration
    class DummyConnection {
        public $connect_error = null;
        public $insert_id = 0;
        private static $dummyProperties = [];
        private static $dummyUsers = [];
        private static $initialized = false;
        
        public function __construct() {
            if (!self::$initialized) {
                // Initialize with default properties
                self::$dummyProperties = [
                    [
                        'id' => 1,
                        'title' => 'Sample Property 1',
                        'description' => 'This is a sample property for demonstration.',
                        'price' => 450000,
                        'bedrooms' => 3,
                        'bathrooms' => 2,
                        'area' => 1800,
                        'address' => '123 Sample St',
                        'city' => 'Sample City',
                        'state' => 'CA',
                        'zip_code' => '12345',
                        'latitude' => 34.0522,
                        'longitude' => -118.2437,
                        'property_type' => 'house',
                        'status' => 'for_sale',
                        'featured' => 1,
                        'image1' => 'https://via.placeholder.com/800x600.png?text=Sample+Property',
                        'seller_id' => 2,
                        'seller_name' => 'John Seller',
                        'seller_email' => 'john@example.com',
                        'seller_phone' => '555-123-4567'
                    ],
                    [
                        'id' => 2,
                        'title' => 'Sample Property 2',
                        'description' => 'Another sample property for demonstration.',
                        'price' => 350000,
                        'bedrooms' => 2,
                        'bathrooms' => 2,
                        'area' => 1200,
                        'address' => '456 Sample Ave',
                        'city' => 'Sample City',
                        'state' => 'CA',
                        'zip_code' => '12345',
                        'latitude' => 34.0535,
                        'longitude' => -118.2450,
                        'property_type' => 'apartment',
                        'status' => 'for_sale',
                        'featured' => 1,
                        'image1' => 'https://via.placeholder.com/800x600.png?text=Sample+Property+2',
                        'seller_id' => 3,
                        'seller_name' => 'Sarah Agent',
                        'seller_email' => 'sarah@example.com',
                        'seller_phone' => '555-987-6543'
                    ]
                ];
                
                self::$dummyUsers = [
                    [
                        'id' => 1,
                        'name' => 'Admin User',
                        'email' => 'admin@realestate.com',
                        'role' => 'admin',
                        'phone' => '555-555-5555',
                        'created_at' => '2023-01-01 12:00:00',
                        'updated_at' => '2023-01-01 12:00:00'
                    ],
                    [
                        'id' => 2,
                        'name' => 'John Seller',
                        'email' => 'john@example.com',
                        'role' => 'seller',
                        'phone' => '555-123-4567',
                        'created_at' => '2023-01-01 12:00:00',
                        'updated_at' => '2023-01-01 12:00:00'
                    ],
                    [
                        'id' => 3,
                        'name' => 'Sarah Agent',
                        'email' => 'sarah@example.com',
                        'role' => 'seller',
                        'phone' => '555-987-6543',
                        'created_at' => '2023-01-01 12:00:00',
                        'updated_at' => '2023-01-01 12:00:00'
                    ],
                    [
                        'id' => 4,
                        'name' => 'Mike Buyer',
                        'email' => 'mike@example.com',
                        'role' => 'buyer',
                        'phone' => '555-555-5555',
                        'created_at' => '2023-01-01 12:00:00',
                        'updated_at' => '2023-01-01 12:00:00'
                    ]
                ];
                
                self::$initialized = true;
            }
        }
        
        public function query($query) {
            // Return dummy data for demonstration
            if (strpos($query, 'COUNT(*)') !== false) {
                return new DummyResult(5); // Count of 5 for any count query
            }
            if (strpos($query, 'SELECT') !== false && strpos($query, 'properties') !== false) {
                return new DummyResult($this->getDummyProperties());
            }
            if (strpos($query, 'SELECT') !== false && strpos($query, 'users') !== false) {
                return new DummyResult($this->getDummyUsers());
            }
            return new DummyResult([]);
        }
        
        public function prepare($query) {
            $stmt = new DummyStatement();
            $stmt->setQuery($query);
            return $stmt;
        }
        
        public function real_escape_string($str) {
            return $str; // No need for escaping in dummy
        }
        
        public function set_charset($charset) {
            return true;
        }
        
        public function error() {
            return '';
        }
        
        // Add a new property to the dummy properties array
        public function addProperty($params) {
            $nextId = count(self::$dummyProperties) + 1;
            $this->insert_id = $nextId;
            
            // Map parameters to property fields based on the order in the query
            $property = [
                'id' => $nextId,
                'seller_id' => $params[0],
                'title' => $params[1],
                'description' => $params[2],
                'price' => $params[3],
                'bedrooms' => $params[4],
                'bathrooms' => $params[5],
                'area' => $params[6],
                'address' => $params[7],
                'city' => $params[8],
                'state' => $params[9],
                'zip_code' => $params[10],
                'latitude' => $params[11],
                'longitude' => $params[12],
                'property_type' => $params[13],
                'status' => $params[14],
                'featured' => $params[15],
                'image1' => $params[16],
                'image2' => $params[17],
                'image3' => $params[18],
                'image4' => $params[19],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Add seller info
            foreach (self::$dummyUsers as $user) {
                if ($user['id'] == $property['seller_id']) {
                    $property['seller_name'] = $user['name'];
                    $property['seller_email'] = $user['email'];
                    $property['seller_phone'] = $user['phone'];
                    break;
                }
            }
            
            self::$dummyProperties[] = $property;
            return true;
        }
        
        // Dummy data methods
        private function getDummyProperties() {
            return self::$dummyProperties;
        }
        
        private function getDummyUsers() {
            return self::$dummyUsers;
        }
    }
    
    class DummyResult {
        private $data;
        public $num_rows;
        private $index = 0;
        
        public function __construct($data) {
            if (is_array($data)) {
                $this->data = $data;
                $this->num_rows = count($data);
            } else {
                $this->data = [['count' => $data]];
                $this->num_rows = 1;
            }
        }
        
        public function fetch_assoc() {
            if ($this->index >= $this->num_rows) {
                return null;
            }
            return $this->data[$this->index++];
        }
    }
    
    class DummyStatement {
        private $params = [];
        private $query = '';
        private $types = '';
        
        public function bind_param($types, ...$params) {
            $this->types = $types;
            $this->params = $params;
            return true;
        }
        
        public function execute() {
            global $conn;
            
            // Check if this is an insert into properties
            if (strpos($this->query, 'INSERT INTO properties') !== false) {
                // Set the insert_id for the DummyConnection
                $conn->insert_id = time(); // Use timestamp as a unique ID
                
                // Create a new property entry from the parameters
                if (count($this->params) >= 20) {
                    // Add new property to dummy properties
                    $conn->addProperty($this->params);
                }
            }
            
            return true;
        }
        
        public function get_result() {
            return new DummyResult([]);
        }
        
        public function close() {
            return true;
        }
        
        public function setQuery($query) {
            $this->query = $query;
        }
    }
    
    // Create dummy connection
    $conn = new DummyConnection();
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define site constants
define('SITE_NAME', 'Real Estate Listings');
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/');

// Error reporting settings
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('America/Los_Angeles');
?>
