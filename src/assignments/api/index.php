<?php
/**
 * Assignment Management API
 * 
 * This is a RESTful API that handles all CRUD operations for course assignments
 * and their associated discussion comments.
 * It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structures (for reference):
 * 
 * Table: assignments
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - title (VARCHAR(200))
 *   - description (TEXT)
 *   - due_date (DATE)
 *   - files (TEXT)
 *   - created_at (TIMESTAMP)
 *   - updated_at (TIMESTAMP)
 * 
 * Table: comments
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - assignment_id (VARCHAR(50), FOREIGN KEY)
 *   - author (VARCHAR(100))
 *   - text (TEXT)
 *   - created_at (TIMESTAMP)
 * 
 * HTTP Methods Supported:
 *   - GET: Retrieve assignment(s) or comment(s)
 *   - POST: Create a new assignment or comment
 *   - PUT: Update an existing assignment
 *   - DELETE: Delete an assignment or comment
 * 
 * Response Format: JSON
 */

// ============================================================================
// HEADERS AND CORS CONFIGURATION
// ============================================================================

// TODO: Set Content-Type header to application/json
header('Content-Type: application/json');

// TODO: Set CORS headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS'); 
header('Access-Control-Allow-Headers: Content-Type');

// TODO: Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }


// ============================================================================
// DATABASE CONNECTION
// ============================================================================

// TODO: Include the database connection class


// TODO: Create database connection


// TODO: Set PDO to throw exceptions on errors


try {
    $db = new PDO('mysql:host=localhost;dbname=your_database_name;charset=utf8mb4', 'your_username', 'your_password');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}



// ============================================================================
// REQUEST PARSING
// ============================================================================

// TODO: Get the HTTP request method
$method = $_SERVER['REQUEST_METHOD'];

// TODO: Get the request body for POST and PUT requests

$input = json_decode(file_get_contents('php://input'), true) ?? [];

// TODO: Parse query parameters
$queryParams = $_GET;
$resource = $queryParams['resource'] ?? null;


// ============================================================================
// ASSIGNMENT CRUD FUNCTIONS
// ============================================================================

/**
 * Function: Get all assignments
 * Method: GET
 * Endpoint: ?resource=assignments
 * 
 * Query Parameters:
 *   - search: Optional search term to filter by title or description
 *   - sort: Optional field to sort by (title, due_date, created_at)
 *   - order: Optional sort order (asc or desc, default: asc)
 * 
 * Response: JSON array of assignment objects
 */
function getAllAssignments($db) {
    // TODO: Start building the SQL query
    $sql = "SELECT id, title, description, due_date AS dueDate, files, created_at, updated_at FROM assignments";
    $params = [];
    
    // TODO: Check if 'search' query parameter exists in $_GET
    if (!empty($_GET['search'])) {
        $search = "%" . sanitizeInput($_GET['search']) . "%";
        $sql .= " WHERE title LIKE :search OR description LIKE :search";
        $params[':search'] = $search;
    }
    
    // TODO: Check if 'sort' and 'order' query parameters exist
  $allowedSorts = ['title', 'due_date', 'created_at'];
    $sort = validateAllowedValue($_GET['sort'] ?? 'due_date', $allowedSorts) ? $_GET['sort'] : 'due_date';
    $order = strtoupper($_GET['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

    $sql .= " ORDER BY $sort $order";

   
    
    // TODO: Prepare the SQL statement using $db->prepare()
     $stmt = $db->prepare($sql);
    
    // TODO: Bind parameters if search is used
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    // TODO: Execute the prepared statement
    $stmt->execute();
    
    // TODO: Fetch all results as associative array
    $assignments = $stmt->fetchAll();
    
    // TODO: For each assignment, decode the 'files' field from JSON to array
    foreach ($assignments as &$assignment) {
        $assignment['files'] = $assignment['files'] ? json_decode($assignment['files'], true) : [];
    }
    
    // TODO: Return JSON response
    sendResponse(["assignments" => $assignments]);

}


/**
 * Function: Get a single assignment by ID
 * Method: GET
 * Endpoint: ?resource=assignments&id={assignment_id}
 * 
 * Query Parameters:
 *   - id: The assignment ID (required)
 * 
 * Response: JSON object with assignment details
 */
function getAssignmentById($db, $assignmentId) {
    // TODO: Validate that $assignmentId is provided and not empty
    if (empty($assignmentId)) {
        sendResponse(['error' => 'Assignment ID is required'], 400);
    }
    
    // TODO: Prepare SQL query to select assignment by id
    $stmt = $db->prepare("SELECT id, title, description, due_date AS dueDate, files FROM assignments WHERE id = ?");
    
    // TODO: Bind the :id parameter
    $stmt->bindParam(':id', $assignmentId, PDO::PARAM_INT);
    
    // TODO: Execute the statement
    $stmt->execute([$assignmentId]);
    
    // TODO: Fetch the result as associative array
    $assignment = $stmt->fetch();
    
    // TODO: Check if assignment was found
    if (!$assignment) {
        sendResponse(['error' => 'Assignment not found'], 404);
    
    // TODO: Decode the 'files' field from JSON to array
    $assignment['files'] = $assignment['files'] 
        ? json_decode($assignment['files'], true) 
        : [];
    
    // TODO: Return success response with assignment data
    sendResponse($assignment);
}
}

/**
 * Function: Create a new assignment
 * Method: POST
 * Endpoint: ?resource=assignments
 * 
 * Required JSON Body:
 *   - title: Assignment title (required)
 *   - description: Assignment description (required)
 *   - due_date: Due date in YYYY-MM-DD format (required)
 *   - files: Array of file URLs/paths (optional)
 * 
 * Response: JSON object with created assignment data
 */
function createAssignment($db, $data) {
    // TODO: Validate required fields
    $required = ['title', 'description', 'due_date'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendResponse(['error' => ucfirst($field) . ' is required'], 400);
        }
    }
    
    // TODO: Sanitize input data
    $title = sanitizeInput($data['title']);
    $description = sanitizeInput($data['description']);
    
    // TODO: Validate due_date format
    if (!validateDate($data['due_date'])) {
        sendResponse(['error' => 'Invalid due_date format'], 400);
    }
    
    // TODO: Generate a unique assignment ID
    
    
    // TODO: Handle the 'files' field
    $filesJson = isset($data['files']) && is_array($data['files'])
        ? json_encode($data['files'])
        : '[]';
    
    // TODO: Prepare INSERT query
    $stmt = $db->prepare("
        INSERT INTO assignments (title, description, due_date, files, created_at, updated_at)
        VALUES (:title, :description, :due_date, :files, NOW(), NOW())
    ");
    
    // TODO: Bind all parameters
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':due_date', $data['due_date']);
    $stmt->bindParam(':files', $filesJson);
    
    // TODO: Execute the statement
    $stmt->execute();
    
    // TODO: Check if insert was successful
    if (!$success) {
        sendResponse(['error' => 'Failed to create assignment'], 500);
    }
    
    // TODO: If insert failed, return 500 error
    sendResponse([
        'message' => 'Assignment created successfully',
        'id' => $db->lastInsertId()
    ], 201);
}


/**
 * Function: Update an existing assignment
 * Method: PUT
 * Endpoint: ?resource=assignments
 * 
 * Required JSON Body:
 *   - id: Assignment ID (required, to identify which assignment to update)
 *   - title: Updated title (optional)
 *   - description: Updated description (optional)
 *   - due_date: Updated due date (optional)
 *   - files: Updated files array (optional)
 * 
 * Response: JSON object with success status
 */
function updateAssignment($db, $data) {
    // TODO: Validate that 'id' is provided in $data
    if (empty($data['id'])) {
        sendResponse(['error' => 'Assignment ID is required'], 400);
    }
    
    // TODO: Store assignment ID in variable
    $id = $data['id'];
    
    // TODO: Check if assignment exists
    $check = $db->prepare("SELECT id FROM assignments WHERE id = ?");
    $check->execute([$id]);
    if ($check->rowCount() === 0) {
        sendResponse(['error' => 'Assignment not found'], 404);
    }

    $setParts = [];
    $params = [':id' => $id];
    
    // TODO: Build UPDATE query dynamically based on provided fields
    $setParts = [];
    $params = [':id' => $id];
    
    // TODO: Check which fields are provided and add to SET clause
    if (!empty($data['title'])) {
        $setParts[] = "title = :title";
        $params[':title'] = sanitizeInput($data['title']);
    }
    if (!empty($data['description'])) {
        $setParts[] = "description = :description";
        $params[':description'] = sanitizeInput($data['description']);
    }
    if (!empty($data['due_date'])) {
        if (!validateDate($data['due_date'])) {
            sendResponse(['error' => 'Invalid due_date'], 400);
        }
        $setParts[] = "due_date = :due_date";
        $params[':due_date'] = $data['due_date'];
    }
    if (isset($data['files'])) {
        $setParts[] = "files = :files";
        $params[':files'] = is_array($data['files']) ? json_encode($data['files']) : '[]';
    }
    
    // TODO: If no fields to update (besides updated_at), return 400 error
    if (empty($setParts)) {
        sendResponse(['error' => 'No fields to update'], 400);
    }
    
    // TODO: Complete the UPDATE query
    $setParts[] = "updated_at = NOW()";
    $setClause = implode(', ', $setParts);
    
    // TODO: Prepare the statement
    $stmt = $db->prepare("UPDATE assignments SET $setClause WHERE id = :id");
    
    // TODO: Bind all parameters dynamically
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }
    
    // TODO: Execute the statement
    $stmt->execute();
    
    // TODO: Check if update was successful
    if ($stmt->rowCount() === 0) {
        sendResponse(["error" => "Assignment not found or no changes made"], 404);
    }
    
    // TODO: If no rows affected, return appropriate message
    sendResponse(["message" => "Assignment updated successfully"]);
}


/**
 * Function: Delete an assignment
 * Method: DELETE
 * Endpoint: ?resource=assignments&id={assignment_id}
 * 
 * Query Parameters:
 *   - id: Assignment ID (required)
 * 
 * Response: JSON object with success status
 */
function deleteAssignment($db, $assignmentId) {
    // TODO: Validate that $assignmentId is provided and not empty
    if (empty($assignmentId)) {
        sendResponse(['error' => 'Assignment ID required'], 400);
    }
    
    // TODO: Check if assignment exists
    $check = $db->prepare("SELECT id FROM assignments WHERE id = ?");
    $check->execute([$assignmentId]);
    if ($check->rowCount() === 0) {
        sendResponse(['error' => 'Assignment not found'], 404);
    }
    
    // TODO: Delete associated comments first (due to foreign key constraint)
    $db->prepare("DELETE FROM comments WHERE assignment_id = ?")->execute([$assignmentId]);
    
    // TODO: Prepare DELETE query for assignment
    $stmt = $db->prepare("DELETE FROM assignments WHERE id = ?");
    
    // TODO: Bind the :id parameter
    $stmt->bindValue(1, $assignmentId, PDO::PARAM_INT);
    
    // TODO: Execute the statement
    $stmt->execute();
    
    // TODO: Check if delete was successful
     if ($stmt->rowCount() === 0) {
        sendResponse(["error" => "Assignment not found"], 404);
    }
    
    // TODO: If delete failed, return 500 error
    sendResponse(['message' => 'Assignment deleted']);
}


// ============================================================================
// COMMENT CRUD FUNCTIONS
// ============================================================================

/**
 * Function: Get all comments for a specific assignment
 * Method: GET
 * Endpoint: ?resource=comments&assignment_id={assignment_id}
 * 
 * Query Parameters:
 *   - assignment_id: The assignment ID (required)
 * 
 * Response: JSON array of comment objects
 */
function getCommentsByAssignment($db, $assignmentId) {
    // TODO: Validate that $assignmentId is provided and not empty
    if (empty($assignmentId)) {
        sendResponse(['error' => 'assignment_id required'], 400);
    }
    
    // TODO: Prepare SQL query to select all comments for the assignment
    $stmt = $db->prepare("
        SELECT author, text, created_at 
        FROM comments 
        WHERE assignment_id = ? 
        ORDER BY created_at DESC
    ");
    
    // TODO: Bind the :assignment_id parameter
    $stmt->bindValue(1, $assignmentId, PDO::PARAM_INT);
    
    // TODO: Execute the statement
    $stmt->execute();
    
    // TODO: Fetch all results as associative array
    $comments = $stmt->fetchAll();
    
    // TODO: Return success response with comments data
    sendResponse(['comments' => $comments]);
}


/**
 * Function: Create a new comment
 * Method: POST
 * Endpoint: ?resource=comments
 * 
 * Required JSON Body:
 *   - assignment_id: Assignment ID (required)
 *   - author: Comment author name (required)
 *   - text: Comment content (required)
 * 
 * Response: JSON object with created comment data
 */
function createComment($db, $data) {
    // TODO: Validate required fields
    if (empty($data['assignment_id']) || empty($data['author']) || empty($data['text'])) {
        sendResponse(['error' => 'Missing required fields'], 400);
    }
    
    // TODO: Sanitize input data
    $author = sanitizeInput($data['author']);
    $text = sanitizeInput($data['text']);
    
    // TODO: Validate that text is not empty after trimming
    if (trim($text) === '') {
        sendResponse(['error' => 'Comment cannot be empty'], 400);
    }
    
    // TODO: Verify that the assignment exists
    $check = $db->prepare("SELECT id FROM assignments WHERE id = ?");
    $check->execute([$data['assignment_id']]);
    if ($check->rowCount() === 0) {
        sendResponse(['error' => 'Assignment not found'], 404);
    
    // TODO: Prepare INSERT query for comment
    $stmt = $db->prepare("
        INSERT INTO comments (assignment_id, author, text, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    
    // TODO: Bind all parameters
    $stmt->bindParam(1, $data['assignment_id']);
    $stmt->bindParam(2, $author);
    $stmt->bindParam(3, $text);
    
    // TODO: Execute the statement
    $stmt->execute();
    
    // TODO: Get the ID of the inserted comment
    $newCommentId = $db->lastInsertId();
    
    // TODO: Return success response with created comment data
    sendResponse(['message' => 'Comment created']);
}


/**
 * Function: Delete a comment
 * Method: DELETE
 * Endpoint: ?resource=comments&id={comment_id}
 * 
 * Query Parameters:
 *   - id: Comment ID (required)
 * 
 * Response: JSON object with success status
 */
function deleteComment($db, $commentId) {
    // TODO: Validate that $commentId is provided and not empty
    if (empty($commentId)) {
        sendResponse(['error' => 'Comment ID required'], 400);
    }
    
    // TODO: Check if comment exists
    $check = $db->prepare("SELECT id FROM comments WHERE id = ?");
    $check->execute([$commentId]);
    if ($check->rowCount() === 0) {
        sendResponse(['error' => 'Comment not found'], 404);
    }
    
    // TODO: Prepare DELETE query
    $stmt = $db->prepare("DELETE FROM comments WHERE id = ?");
    
    // TODO: Bind the :id parameter
    $stmt->bindValue(1, $commentId, PDO::PARAM_INT);
    
    // TODO: Execute the statement
    $stmt->execute();
    
    // TODO: Check if delete was successful
    if ($stmt->rowCount() === 0) {
        sendResponse(['error' => 'Delete failed - no rows affected'], 500);
    }
    
    // TODO: If delete failed, return 500 error
    sendResponse(['message' => 'Comment deleted']);
}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Get the 'resource' query parameter to determine which resource to access
    if (!$resource) {
        sendResponse(['error' => 'Resource not specified'], 400);
    }
    
    // TODO: Route based on HTTP method and resource type
    
    if ($method === 'GET') {
        // TODO: Handle GET requests
        
        if ($resource === 'assignments') {
            // TODO: Check if 'id' query parameter exists
            if (!empty($queryParams['id'])) {
                getAssignmentById($db, $queryParams['id']);
            } else {
                getAllAssignments($db);
            }}
            
        } elseif ($resource === 'comments') {
            // TODO: Check if 'assignment_id' query parameter exists
            if (empty($queryParams['assignment_id'])) {
                sendResponse(['error' => 'assignment_id required'], 400);
            }
            getCommentsByAssignment($db, $queryParams['assignment_id']);
        }
        } else {
            // TODO: Invalid resource, return 400 error
            sendResponse(['error' => 'Invalid resource'], 400);
        }
        
    } elseif ($method === 'POST') {
        // TODO: Handle POST requests (create operations)
        
        if ($resource === 'assignments') {
            // TODO: Call createAssignment($db, $data)
            createAssignment($db, $input);
        } elseif ($resource === 'comments') {
            // TODO: Call createComment($db, $data)
            createComment($db, $input);
        } else {
            // TODO: Invalid resource, return 400 error
            sendResponse(['error' => 'Invalid POST resource'], 400);
        }
        
    } elseif ($method === 'PUT') {
        // TODO: Handle PUT requests (update operations)
        
        if ($resource === 'assignments') {
            // TODO: Call updateAssignment($db, $data)
            updateAssignment($db, $input);
        } else {
            // TODO: PUT not supported for other resources
            sendResponse(['error' => 'PUT not supported'], 405);
        }
        
    } elseif ($method === 'DELETE') {
        // TODO: Handle DELETE requests
        
        if ($resource === 'assignments') {
            // TODO: Get 'id' from query parameter or request body
            deleteAssignment($db, $queryParams['id']);
        } elseif ($resource === 'comments') {
            // TODO: Get comment 'id' from query parameter
            deleteComment($db, $queryParams['id']);
        } else {
            // TODO: Invalid resource, return 400 error
            sendResponse(['error' => 'Invalid DELETE'], 400);
        }
        
    } else {
        // TODO: Method not supported
        sendResponse(['error' => 'Method not allowed'], 405);
    }
    
 catch (PDOException $e) {
    // TODO: Handle database errors
    sendResponse(['error' => 'Server error'], 500);
} catch (Exception $e) {
    // TODO: Handle general errors
    error_log("General Error: " . $e->getMessage() . " | File: " . $e->getFile() . ":" . $e->getLine());

    sendResponse([
        'error' => 'An unexpected server error occurred'
    ], 500);
}


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Helper function to send JSON response and exit
 * 
 * @param array $data - Data to send as JSON
 * @param int $statusCode - HTTP status code (default: 200)
 */
function sendResponse($data, $statusCode = 200) {
    // TODO: Set HTTP response code
    http_response_code($statusCode);
    
    // TODO: Ensure data is an array
 if (!is_array($data)) {
        $data = ['data' => $data];  // Wrap non-arrays
    }
    
    // TODO: Echo JSON encoded data
       echo json_encode($data, JSON_UNESCAPED_UNICODE);
    
    // TODO: Exit to prevent further execution
    exit();
}


/**
 * Helper function to sanitize string input
 * 
 * @param string $data - Input data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data) {
    // TODO: Trim whitespace from beginning and end
    
    $data = trim($data);
    // TODO: Remove HTML and PHP tags
    $data = strip_tags($data);
    
    // TODO: Convert special characters to HTML entities
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    // TODO: Return the sanitized data
    return $data;
}


/**
 * Helper function to validate date format (YYYY-MM-DD)
 * 
 * @param string $date - Date string to validate
 * @return bool - True if valid, false otherwise
 */
function validateDate($date) {
    // TODO: Use DateTime::createFromFormat to validate
    $d = DateTime::createFromFormat('Y-m-d', $date);
    
    // TODO: Return true if valid, false otherwise
    return $d !== false && ($errors === false || ($errors['warning_count'] == 0 && $errors['error_count'] == 0));
}


/**
 * Helper function to validate allowed values (for sort fields, order, etc.)
 * 
 * @param string $value - Value to validate
 * @param array $allowedValues - Array of allowed values
 * @return bool - True if valid, false otherwise
 */
function validateAllowedValue($value, $allowedValues) {
    // TODO: Check if $value exists in $allowedValues array
    $isAllowed = in_array($value, $allowedValues, true);
    
    // TODO: Return the result
    return $isAllowed;
}

?>
