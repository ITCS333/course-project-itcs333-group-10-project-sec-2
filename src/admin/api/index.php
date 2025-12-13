<?php
session_start();
$_SESSION['admin'] = true;
/**
 * Student Management API
 * 
 * This is a RESTful API that handles all CRUD operations for student management.
 * It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structure (for reference):
 * Table: students
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - student_id (VARCHAR(50), UNIQUE) - The student's university ID
 *   - name (VARCHAR(100))
 *   - email (VARCHAR(100), UNIQUE)
 *   - password (VARCHAR(255)) - Hashed password
 *   - created_at (TIMESTAMP)
 * 
 * HTTP Methods Supported:
 *   - GET: Retrieve student(s)
 *   - POST: Create a new student OR change password
 *   - PUT: Update an existing student
 *   - DELETE: Delete a student
 * 
 * Response Format: JSON
 */

// TODO: Set headers for JSON response and CORS
// Set Content-Type to application/json
// Allow cross-origin requests (CORS) if needed
// Allow specific HTTP methods (GET, POST, PUT, DELETE, OPTIONS)
// Allow specific headers (Content-Type, Authorization)
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");






// TODO: Handle preflight OPTIONS request
// If the request method is OPTIONS, return 200 status and exit
if($_SERVER["REQUEST_METHOD"] == "OPTIONS"){
    http_response_code(200);    
    exit();
}


// TODO: Include the database connection class
// Assume the Database class has a method getConnection() that returns a PDO instance
require_once '../config/Database.php';

// TODO: Get the PDO database connection
$database = new Database();
$conn = $database->getConnection();

// TODO: Get the HTTP request method
// Use $_SERVER['REQUEST_METHOD']
$method = $_SERVER['REQUEST_METHOD'];


// TODO: Get the request body for POST and PUT requests
// Use file_get_contents('php://input') to get raw POST data
// Decode JSON data using json_decode()
$inputData = null;
if ($method === "POST" || $method === "PUT") {
    $rawData = file_get_contents("php://input");
    $inputData = json_decode($rawData, true); 
}


// TODO: Parse query parameters for filtering and searching
$studentId = $_GET['student_id'] ?? null;
$search    = $_GET['search'] ?? null;
$email     = $_GET['email'] ?? null;

/**
 * Function: Get all students or search for specific students
 * Method: GET
 * 
 * Query Parameters:
 *   - search: Optional search term to filter by name, student_id, or email
 *   - sort: Optional field to sort by (name, student_id, email)
 *   - order: Optional sort order (asc or desc)
 */
function getStudents($conn) {
    // TODO: Check if search parameter exists
    // If yes, prepare SQL query with WHERE clause using LIKE
    // Search should work on name, student_id, and email fields
    $search = isset($_GET['search']) ? trim($_GET['search']) : null;
    $whereSql = "";

    if ($search) {
        $whereSql = "WHERE name LIKE :search OR student_id LIKE :search OR email LIKE :search";
    }
    
    // TODO: Check if sort and order parameters exist
    // If yes, add ORDER BY clause to the query
    // Validate sort field to prevent SQL injection (only allow: name, student_id, email)
    // Validate order to prevent SQL injection (only allow: asc, desc)
    $allowedSort = ["name", "student_id", "email"];
    $allowedOrder = ["asc", "desc"];

    $sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSort)
        ? $_GET['sort']
        : null;

    $order = isset($_GET['order']) && in_array(strtolower($_GET['order']), $allowedOrder)
        ? strtolower($_GET['order'])
        : "asc"; // default
        
    $orderSql = "";
    if ($sort) {
        $orderSql = "ORDER BY $sort $order";
    }    
    // TODO: Prepare the SQL query using PDO
    // Note: Do NOT select the password field
    $sql = "SELECT name, student_id, email FROM students $whereSql $orderSql";
    $stmt = $conn->prepare($sql);

    
    // TODO: Bind parameters if using search
    if ($search) {
    $searchTerm = "%$search%";
    $stmt->bindParam(":search", $searchTerm, PDO::PARAM_STR);
    }

    
    // TODO: Execute the query
    $stmt->execute();

    
    // TODO: Fetch all results as an associative array
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    // TODO: Return JSON response with success status and data
    return [
        "status" => 200,
        "success" => true,
        "data" => $students
    ];

}


/**
 * Function: Get a single student by student_id
 * Method: GET
 * 
 * Query Parameters:
 *   - student_id: The student's university ID
 */
function getStudentById($conn, $studentId) {
    // TODO: Prepare SQL query to select student by student_id
    $sql = "SELECT name, student_id, email FROM students WHERE student_id = :id LIMIT 1";
    $stmt = $conn->prepare($sql);

    
    // TODO: Bind the student_id parameter
    $stmt->bindParam(":id", $studentId, PDO::PARAM_STR);

    
    // TODO: Execute the query
    $stmt->execute();

    
    // TODO: Fetch the result
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    
    // TODO: Check if student exists
    // If yes, return success response with student data
    // If no, return error response with 404 status
    if ($student) {
        return [
            "status" => 200,
            "success" => true,
            "data" => $student
        ];
    } else {
        return [
            "status" => 404,
            "success" => false,
            "message" => "Student not found"
        ];
    }

}


/**
 * Function: Create a new student
 * Method: POST
 * 
 * Required JSON Body:
 *   - student_id: The student's university ID (must be unique)
 *   - name: Student's full name
 *   - email: Student's email (must be unique)
 *   - password: Default password (will be hashed)
 */
function createStudent($conn, $data) {
    // TODO: Validate required fields
    // Check if student_id, name, email, and password are provided
    // If any field is missing, return error response with 400 status
        if (
        empty($data['student_id']) ||
        empty($data['name']) ||
        empty($data['email']) ||
        empty($data['password'])
    ) {
        return[
            "status" => 400,
            "success" => false,
            "message" => "Missing required fields."
        ];
        
    }

    
    // TODO: Sanitize input data
    // Trim whitespace from all fields
    // Validate email format using filter_var()
    $studentId = trim($data['student_id']);
    $name = trim($data['name']);
    $email = trim($data['email']);
    $password = trim($data['password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
       return[
            "status" => 400,
            "success" => false,
            "message" => "Invalid email format."
        ];
        
    }

    
    // TODO: Check if student_id or email already exists
    // Prepare and execute a SELECT query to check for duplicates
    // If duplicate found, return error response with 409 status (Conflict)
    $checkSql = "SELECT student_id FROM students WHERE student_id = :id OR email = :email";
    $checkStmt = $conn->prepare($checkSql);

    $checkStmt->bindParam(':id', $studentId);
    $checkStmt->bindParam(':email', $email);

    $checkStmt->execute();

    if ($checkStmt->fetch()) {
        return[
            "status" => 409,
            "success" => false,
            "message" => "Student ID or email already exists."
        ];
        
    }

    
    // TODO: Hash the password
    // Use password_hash() with PASSWORD_DEFAULT
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    
    // TODO: Prepare INSERT query
    $sql = "INSERT INTO students (student_id, name, email, password)
        VALUES (:id, :name, :email, :pass)";
    $stmt = $conn->prepare($sql);

    // TODO: Bind parameters
    // Bind student_id, name, email, and hashed password
    $stmt->bindParam(':id', $studentId);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':pass', $hashedPassword);

    
    // TODO: Execute the query
    $success = $stmt->execute();

    
    // TODO: Check if insert was successful
    // If yes, return success response with 201 status (Created)
    // If no, return error response with 500 status
    if ($success) {
        return [
            "status" => 201,
            "success" => true,
            "message" => "Student created successfully."
        ];
    } else {
        return [
            "status" => 500,
            "success" => false,
            "message" => "Failed to create student."
        ];
    }

}


/**
 * Function: Update an existing student
 * Method: PUT
 * 
 * Required JSON Body:
 *   - student_id: The student's university ID (to identify which student to update)
 *   - name: Updated student name (optional)
 *   - email: Updated student email (optional)
 */
function updateStudent($conn, $data) {
    // TODO: Validate that student_id is provided
    // If not, return error response with 400 status
    if (!isset($data['student_id']) || empty($data['student_id'])) {
       return [
            "status" => 400,
            "success" => false,
            "error" => "student_id is required"
        ];
    }

    $student_id = $data['student_id'];

    // TODO: Check if student exists
    // Prepare and execute a SELECT query to find the student
    // If not found, return error response with 404 status
    $checkStmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $checkStmt->execute([$student_id]);
    $existingStudent = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingStudent) {
       return [
            "status" => 404,
            "success" => false,
            "error" => "student not found"
        ];
    }

    
    // TODO: Build UPDATE query dynamically based on provided fields
    // Only update fields that are provided in the request
    $fieldsToUpdate = [];
    $params = [];

    if (!empty($data['name'])) {
        $fieldsToUpdate[] = "name = ?";
        $params[] = $data['name'];
    }

    if (!empty($data['email'])) {
        $fieldsToUpdate[] = "email = ?";
        $params[] = $data['email'];
    }

    if (empty($fieldsToUpdate)) {
       return [
            "status" => 400,
            "success" => false,
            "error" => "No fields provided to update"
        ];
    }

    
    // TODO: If email is being updated, check if new email already exists
    // Prepare and execute a SELECT query
    // Exclude the current student from the check
    // If duplicate found, return error response with 409 status
    if (!empty($data['email'])) {
        $emailCheck = $conn->prepare("SELECT * FROM students WHERE email = ? AND student_id != ?");
        $emailCheck->execute([$data['email'], $student_id]);
        $duplicateEmail = $emailCheck->fetch(PDO::FETCH_ASSOC);

        if ($duplicateEmail) {
            return [
                "status" => 409,
                "success" => false,
                "error" => "Email already exists for another student"
            ];

        }
    }

    
    // TODO: Bind parameters dynamically
    // Bind only the parameters that are being updated
    $query = "UPDATE students SET " . implode(", ", $fieldsToUpdate) . " WHERE student_id = ?";
    $params[] = $student_id; // add ID as last parameter
    $stmt = $conn->prepare($query);

    
    // TODO: Execute the query
    $success = $stmt->execute($params);

    // TODO: Check if update was successful
    // If yes, return success response
    // If no, return error response with 500 status
    if ($success) {
        return [
            "message" => "Student updated successfully",
            "status" => 200,
            "success" => true
        ];
    } else {
        return [
            "status" => 500,
            "success" => false,
            "error" => "Failed to update student"
        ];

    }

}


/**
 * Function: Delete a student
 * Method: DELETE
 * 
 * Query Parameters or JSON Body:
 *   - student_id: The student's university ID
 */
function deleteStudent($conn, $studentId) {
    // TODO: Validate that student_id is provided
    // If not, return error response with 400 status
    if (!isset($studentId) || empty($studentId)) {
        return [
            "status" => 400,
            "success" => false,
            "error" => "student_id is required"
        ];

        
    }

    // TODO: Check if student exists
    // Prepare and execute a SELECT query
    // If not found, return error response with 404 status
    $checkStmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $checkStmt->execute([$studentId]);
    $student = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        return [
            "status" => 404,
            "success" => false,
            "error" => "student not found"
        ];

    }
    
    // TODO: Prepare DELETE query
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");

    
    // TODO: Bind the student_id parameter
    $stmt->bindValue(1, $studentId, PDO::PARAM_STR);

    
    // TODO: Execute the query
    $success = $stmt->execute();

    
    // TODO: Check if delete was successful
    // If yes, return success response
    // If no, return error response with 500 status
    if ($success) {
    return [
        "message" => "Student deleted successfully",
        "status" => 200,
        "success" => true
    ];
    } else {
        return [
            "status" => 500,
            "success" => false,
            "error" => "Failed to delete student"
        ];

    }

}


/**
 * Function: Change password
 * Method: POST with action=change_password
 * 
 * Required JSON Body:
 *   - student_id: The student's university ID (identifies whose password to change)
 *   - current_password: The student's current password
 *   - new_password: The new password to set
 */
function changePassword($conn, $data) {
    // TODO: Validate required fields
    // Check if student_id, current_password, and new_password are provided
    // If any field is missing, return error response with 400 status
    if (
    !isset($data['student_id']) || empty($data['student_id']) ||
    !isset($data['current_password']) || empty($data['current_password']) ||
    !isset($data['new_password']) || empty($data['new_password'])
    ) {
        return [
            "status" => 400,
            "success" => false,
            "error" => "student_id, current_password, and new_password are required"
        ];

    }

    // TODO: Validate new password strength
    // Check minimum length (at least 8 characters)
    // If validation fails, return error response with 400 status
    if (strlen($data['new_password']) < 8) {
        return [
            "status" => 400,
            "success" => false,
            "error" => "New password must be at least 8 characters long"
        ];

    }

    
    // TODO: Retrieve current password hash from database
    // Prepare and execute SELECT query to get password
    $stmt = $conn->prepare("SELECT password FROM students WHERE student_id = ?");
    $stmt->execute([$data['student_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        return [
            "status" => 404,
            "success" => false,
            "error" => "Student not found"
        ];

        
    }

    
    // TODO: Verify current password
    // Use password_verify() to check if current_password matches the hash
    // If verification fails, return error response with 401 status (Unauthorized)
    if (!password_verify($data['current_password'], $student['password'])) {
        return [
            "status" => 401,
            "success" => false,
            "error" => "Current password is incorrect"
        ];

    }

    
    // TODO: Hash the new password
    // Use password_hash() with PASSWORD_DEFAULT
    $newHash = password_hash($data['new_password'], PASSWORD_DEFAULT);

    // TODO: Update password in database
    // Prepare UPDATE query
    $updateStmt = $conn->prepare("UPDATE students SET password = ? WHERE student_id = ?");

    
    // TODO: Bind parameters and execute
    $success = $updateStmt->execute([$newHash, $data['student_id']]);

    
    // TODO: Check if update was successful
    // If yes, return success response
    // If no, return error response with 500 status
    if ($success) {
        return [
            "message" => "Password changed successfully",
            "status" => 200,
            "success" => true
        ];

    } else {
        return [
            "status" => 500,
            "success" => false,
            "error" => "Failed to update password"
        ];

    }

}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Route the request based on HTTP method
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // TODO: Check if student_id is provided in query parameters
        // If yes, call getStudentById()
        // If no, call getStudents() to get all students (with optional search/sort)
        
        if (isset($_GET['student_id'])) {
            $response = getStudentById($conn, $_GET['student_id']);
        } else {
            $response = getStudents($conn);
        }
        

        
    } elseif ($method === 'POST') {
        // TODO: Check if this is a change password request
        // Look for action=change_password in query parameters
        // If yes, call changePassword()
        // If no, call createStudent()
        
        if (isset($_GET['action']) && $_GET['action'] === 'change_password') {
            $response = changePassword($conn, $inputData);
        } else {
            $response = createStudent($conn, $inputData);
        }
    
        
    } elseif ($method === 'PUT') {
        // TODO: Call updateStudent()
        $response = updateStudent($conn, $inputData);
    } elseif ($method === 'DELETE') {
        // TODO: Get student_id from query parameter or request body
        // Call deleteStudent()
        $studentId = $_GET['student_id'] ?? ($inputData['student_id'] ?? null);
        $response = deleteStudent($conn, $studentId);
        
    } else {
        // TODO: Return error for unsupported methods
        // Set HTTP status to 405 (Method Not Allowed)
        // Return JSON error message
        $response = ["error" => "Method not allowed"];
    }
    
} catch (PDOException $e) {
    // TODO: Handle database errors
    // Log the error message (optional)
    // Return generic error response with 500 status
    $response = ["error" => "Database error occurred"];
    
} catch (Exception $e) {
    // TODO: Handle general errors
    // Return error response with 500 status
    $response = ["error" => "Server error occurred"];

}


// ============================================================================
// HELPER FUNCTIONS (Optional but Recommended)
// ============================================================================

/**
 * Helper function to send JSON response
 * 
 * @param mixed $data - Data to send
 * @param int $statusCode - HTTP status code
 */
function sendResponse($data, $statusCode) {
    // TODO: Set HTTP response code
    http_response_code($statusCode);

    
    // TODO: Echo JSON encoded data
    echo json_encode($data);

    
    // TODO: Exit to prevent further execution
    exit;

}


/**
 * Helper function to validate email format
 * 
 * @param string $email - Email address to validate
 * @return bool - True if valid, false otherwise
 */
function validateEmail($email) {
    // TODO: Use filter_var with FILTER_VALIDATE_EMAIL
    // Return true if valid, false otherwise
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;

}


/**
 * Helper function to sanitize input
 * 
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data) {
    // TODO: Trim whitespace
    // TODO: Strip HTML tags using strip_tags()
    // TODO: Convert special characters using htmlspecialchars()
    // Return sanitized data
    $data = trim($data);
    $data = strip_tags($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;




}

$statusCode = $response['status'] ?? 200;

unset($response['status']);

sendResponse($response, $statusCode);

?>
