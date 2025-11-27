<?php
/**
 * Weekly Course Breakdown API
 * 
 * This is a RESTful API that handles all CRUD operations for weekly course content
 * and discussion comments. It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structures (for reference):
 * 
 * Table: weeks
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - week_id (VARCHAR(50), UNIQUE) - Unique identifier (e.g., "week_1")
 *   - title (VARCHAR(200))
 *   - start_date (DATE)
 *   - description (TEXT)
 *   - links (TEXT) - JSON encoded array of links
 *   - created_at (TIMESTAMP)
 *   - updated_at (TIMESTAMP)
 * 
 * Table: comments
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - week_id (VARCHAR(50)) - Foreign key reference to weeks.week_id
 *   - author (VARCHAR(100))
 *   - text (TEXT)
 *   - created_at (TIMESTAMP)
 * 
 * HTTP Methods Supported:
 *   - GET: Retrieve week(s) or comment(s)
 *   - POST: Create a new week or comment
 *   - PUT: Update an existing week
 *   - DELETE: Delete a week or comment
 * 
 * Response Format: JSON
 */

// ============================================================================
// SETUP AND CONFIGURATION
// ============================================================================

// TODO: Set headers for JSON response and CORS
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// TODO: Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// TODO: Include the database connection class
require_once '../config/Database.php';

// TODO: Get the PDO database connection
$database = new Database();
$db = $database->getConnection();

// TODO: Get the HTTP request method
$method = $_SERVER['REQUEST_METHOD'];

// TODO: Get the request body for POST and PUT requests
$input = json_decode(file_get_contents('php://input'), true);

// TODO: Parse query parameters
$resource = isset($_GET['resource']) ? $_GET['resource'] : 'weeks';


// ============================================================================
// WEEKS CRUD OPERATIONS
// ============================================================================

/**
 * Function: Get all weeks or search for specific weeks
 * Method: GET
 * Resource: weeks
 */
function getAllWeeks($db) {
    // TODO: Initialize variables for search, sort, and order from query parameters
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'start_date';
    $order = isset($_GET['order']) && strtoupper($_GET['order']) === 'DESC' ? 'DESC' : 'ASC';

    // TODO: Start building the SQL query
    $query = "SELECT week_id, title, start_date, description, links, created_at FROM weeks";
    $params = [];

    // TODO: Check if search parameter exists
    if (!empty($search)) {
        $query .= " WHERE title LIKE ? OR description LIKE ?";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // TODO: Check if sort parameter exists
    $allowedSorts = ['title', 'start_date', 'created_at'];
    if (!in_array($sort, $allowedSorts)) {
        $sort = 'start_date';
    }

    // TODO: Check if order parameter exists
    $order = ($order === 'DESC') ? 'DESC' : 'ASC';

    // TODO: Add ORDER BY clause to the query
    $query .= " ORDER BY $sort $order";

    // TODO: Prepare the SQL query using PDO
    $stmt = $db->prepare($query);

    // TODO: Bind parameters if using search
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }

    // TODO: Fetch all results as an associative array
    $weeks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // TODO: Process each week's links field
    foreach ($weeks as &$week) {
        $week['links'] = json_decode($week['links'], true) ?? [];
    }

    // TODO: Return JSON response with success status and data
    sendResponse(['success' => true, 'data' => $weeks]);
}

/**
 * Function: Get a single week by week_id
 * Method: GET
 * Resource: weeks
 */
function getWeekById($db, $weekId) {
    // TODO: Validate that week_id is provided
    if (empty($weekId)) {
        sendError("week_id is required", 400);
        return;
    }

    // TODO: Prepare SQL query to select week by week_id
    $query = "SELECT week_id, title, start_date, description, links, created_at FROM weeks WHERE week_id = ?";
    $stmt = $db->prepare($query);

    // TODO: Bind the week_id parameter
    $stmt->bindParam(1, $weekId);

    // TODO: Execute the query
    $stmt->execute();

    // TODO: Fetch the result
    $week = $stmt->fetch(PDO::FETCH_ASSOC);

    // TODO: Check if week exists
    if ($week) {
        $week['links'] = json_decode($week['links'], true) ?? [];
        sendResponse(['success' => true, 'data' => $week]);
    } else {
        sendError("Week not found", 404);
    }
}

/**
 * Function: Create a new week
 * Method: POST
 * Resource: weeks
 */
function createWeek($db, $data) {
    // TODO: Validate required fields
    if (!isset($data['week_id']) || !isset($data['title']) || !isset($data['start_date']) || !isset($data['description'])) {
        sendError("All fields are required: week_id, title, start_date, description", 400);
        return;
    }

    // TODO: Sanitize input data
    $week_id = trim($data['week_id']);
    $title = trim($data['title']);
    $description = trim($data['description']);
    $start_date = $data['start_date'];

    // TODO: Validate start_date format
    if (!validateDate($start_date)) {
        sendError("Invalid date format. Use YYYY-MM-DD", 400);
        return;
    }

    // TODO: Check if week_id already exists
    $check = $db->prepare("SELECT week_id FROM weeks WHERE week_id = ?");
    $check->execute([$week_id]);
    if ($check->rowCount() > 0) {
        sendError("Week with this week_id already exists", 409);
        return;
    }

    // TODO: Handle links array
    $links = isset($data['links']) && is_array($data['links']) ? json_encode($data['links']) : json_encode([]);

    // TODO: Prepare INSERT query
    $query = "INSERT INTO weeks (week_id, title, start_date, description, links) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);

    // TODO: Bind parameters
    $stmt->bindParam(1, $week_id);
    $stmt->bindParam(2, $title);
    $stmt->bindParam(3, $start_date);
    $stmt->bindParam(4, $description);
    $stmt->bindParam(5, $links);

    // TODO: Execute the query
    if ($stmt->execute()) {
        // TODO: Check if insert was successful
        $newWeek = [
            'week_id' => $week_id,
            'title' => $title,
            'start_date' => $start_date,
            'description' => $description,
            'links' => json_decode($links, true),
            'created_at' => date('Y-m-d H:i:s')
        ];
        sendResponse(['success' => true, 'data' => $newWeek], 201);
    } else {
        sendError("Failed to create week", 500);
    }
}

/**
 * Function: Update an existing week
 * Method: PUT
 * Resource: weeks
 */
function updateWeek($db, $data) {
    // TODO: Validate that week_id is provided
    if (!isset($data['week_id'])) {
        sendError("week_id is required", 400);
        return;
    }

    $week_id = $data['week_id'];

    // TODO: Check if week exists
    $check = $db->prepare("SELECT week_id FROM weeks WHERE week_id = ?");
    $check->execute([$week_id]);
    if ($check->rowCount() === 0) {
        sendError("Week not found", 404);
        return;
    }

    // TODO: Build UPDATE query dynamically based on provided fields
    $setClauses = [];
    $params = [];

    if (isset($data['title'])) {
        $setClauses[] = "title = ?";
        $params[] = trim($data['title']);
    }
    if (isset($data['start_date'])) {
        if (!validateDate($data['start_date'])) {
            sendError("Invalid date format. Use YYYY-MM-DD", 400);
            return;
        }
        $setClauses[] = "start_date = ?";
        $params[] = $data['start_date'];
    }
    if (isset($data['description'])) {
        $setClauses[] = "description = ?";
        $params[] = trim($data['description']);
    }
    if (isset($data['links'])) {
        $linksJson = is_array($data['links']) ? json_encode($data['links']) : json_encode([]);
        $setClauses[] = "links = ?";
        $params[] = $linksJson;
    }

    // TODO: If no fields to update, return error response with 400 status
    if (empty($setClauses)) {
        sendError("No fields provided to update", 400);
        return;
    }

    // TODO: Add updated_at timestamp to SET clauses
    $setClauses[] = "updated_at = CURRENT_TIMESTAMP";

    // TODO: Build the complete UPDATE query
    $query = "UPDATE weeks SET " . implode(", ", $setClauses) . " WHERE week_id = ?";
    $params[] = $week_id;

    // TODO: Prepare the query
    $stmt = $db->prepare($query);

    // TODO: Bind parameters dynamically
    foreach ($params as $i => $param) {
        $stmt->bindValue($i + 1, $param);
    }

    // TODO: Execute the query
    if ($stmt->execute()) {
        // Fetch updated week
        $updated = $db->prepare("SELECT * FROM weeks WHERE week_id = ?");
        $updated->execute([$week_id]);
        $week = $updated->fetch(PDO::FETCH_ASSOC);
        $week['links'] = json_decode($week['links'], true) ?? [];
        sendResponse(['success' => true, 'data' => $week]);
    } else {
        sendError("Failed to update week", 500);
    }
}

/**
 * Function: Delete a week
 * Method: DELETE
 * Resource: weeks
 */
function deleteWeek($db, $weekId) {
    // TODO: Validate that week_id is provided
    if (empty($weekId)) {
        sendError("week_id is required", 400);
        return;
    }

    // TODO: Check if week exists
    $check = $db->prepare("SELECT week_id FROM weeks WHERE week_id = ?");
    $check->execute([$weekId]);
    if ($check->rowCount() === 0) {
        sendError("Week not found", 404);
        return;
    }

    // TODO: Delete associated comments first (to maintain referential integrity)
    $deleteComments = $db->prepare("DELETE FROM comments WHERE week_id = ?");
    $deleteComments->execute([$weekId]);

    // TODO: Prepare DELETE query for week
    $stmt = $db->prepare("DELETE FROM weeks WHERE week_id = ?");

    // TODO: Bind the week_id parameter
    $stmt->bindParam(1, $weekId);

    // TODO: Execute the query
    if ($stmt->execute()) {
        sendResponse(['success' => true, 'message' => "Week and associated comments deleted successfully"]);
    } else {
        sendError("Failed to delete week", 500);
    }
}


// ============================================================================
// COMMENTS CRUD OPERATIONS
// ============================================================================

/**
 * Function: Get all comments for a specific week
 * Method: GET
 * Resource: comments
 */
function getCommentsByWeek($db, $weekId) {
    // TODO: Validate that week_id is provided
    if (empty($weekId)) {
        sendError("week_id is required", 400);
        return;
    }

    // TODO: Prepare SQL query to select comments for the week
    $query = "SELECT id, week_id, author, text, created_at FROM comments WHERE week_id = ? ORDER BY created_at ASC";
    $stmt = $db->prepare($query);

    // TODO: Bind the week_id parameter
    $stmt->bindParam(1, $weekId);

    // TODO: Execute the query
    $stmt->execute();

    // TODO: Fetch all results as an associative array
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // TODO: Return JSON response with success status and data
    sendResponse(['success' => true, 'data' => $comments]);
}

/**
 * Function: Create a new comment
 * Method: POST
 * Resource: comments
 */
function createComment($db, $data) {
    // TODO: Validate required fields
    if (!isset($data['week_id']) || !isset($data['author']) || !isset($data['text'])) {
        sendError("week_id, author, and text are required", 400);
        return;
    }

    // TODO: Sanitize input data
    $week_id = trim($data['week_id']);
    $author = trim($data['author']);
    $text = trim($data['text']);

    // TODO: Validate that text is not empty after trimming
    if (empty($text)) {
        sendError("Comment text cannot be empty", 400);
        return;
    }

    // TODO: Check if the week exists
    $check = $db->prepare("SELECT week_id FROM weeks WHERE week_id = ?");
    $check->execute([$week_id]);
    if ($check->rowCount() === 0) {
        sendError("Week not found", 404);
        return;
    }

    // TODO: Prepare INSERT query
    $query = "INSERT INTO comments (week_id, author, text) VALUES (?, ?, ?)";
    $stmt = $db->prepare($query);

    // TODO: Bind parameters
    $stmt->bindParam(1, $week_id);
    $stmt->bindParam(2, $author);
    $stmt->bindParam(3, $text);

    // TODO: Execute the query
    if ($stmt->execute()) {
        $commentId = $db->lastInsertId();
        $newComment = [
            'id' => $commentId,
            'week_id' => $week_id,
            'author' => $author,
            'text' => $text,
            'created_at' => date('Y-m-d H:i:s')
        ];
        sendResponse(['success' => true, 'data' => $newComment], 201);
    } else {
        sendError("Failed to create comment", 500);
    }
}

/**
 * Function: Delete a comment
 * Method: DELETE
 * Resource: comments
 */
function deleteComment($db, $commentId) {
    // TODO: Validate that id is provided
    if (empty($commentId)) {
        sendError("Comment id is required", 400);
        return;
    }

    // TODO: Check if comment exists
    $check = $db->prepare("SELECT id FROM comments WHERE id = ?");
    $check->execute([$commentId]);
    if ($check->rowCount() === 0) {
        sendError("Comment not found", 404);
        return;
    }

    // TODO: Prepare DELETE query
    $stmt = $db->prepare("DELETE FROM comments WHERE id = ?");

    // TODO: Bind the id parameter
    $stmt->bindParam(1, $commentId, PDO::PARAM_INT);

    // TODO: Execute the query
    if ($stmt->execute()) {
        sendResponse(['success' => true, 'message' => "Comment deleted successfully"]);
    } else {
        sendError("Failed to delete comment", 500);
    }
}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Determine the resource type from query parameters
    $resource = isset($_GET['resource']) ? strtolower($_GET['resource']) : 'weeks';

    // Route based on resource type and HTTP method
    if ($resource === 'weeks') {
        
        if ($method === 'GET') {
            // TODO: Check if week_id is provided in query parameters
            $weekId = isset($_GET['week_id']) ? $_GET['week_id'] : null;
            if ($weekId) {
                getWeekById($db, $weekId);
            } else {
                getAllWeeks($db);
            }
            
        } elseif ($method === 'POST') {
            // TODO: Call createWeek() with the decoded request body
            createWeek($db, $input);
            
        } elseif ($method === 'PUT') {
            // TODO: Call updateWeek() with the decoded request body
            updateWeek($db, $input);
            
        } elseif ($method === 'DELETE') {
            // TODO: Get week_id from query parameter or request body
            $weekId = isset($_GET['week_id']) ? $_GET['week_id'] : (isset($input['week_id']) ? $input['week_id'] : null);
            deleteWeek($db, $weekId);
            
        } else {
            // TODO: Return error for unsupported methods
            sendError("Method not allowed", 405);
        }
    }
    
    // ========== COMMENTS ROUTES ==========
    elseif ($resource === 'comments') {
        
        if ($method === 'GET') {
            // TODO: Get week_id from query parameters
            $weekId = isset($_GET['week_id']) ? $_GET['week_id'] : null;
            getCommentsByWeek($db, $weekId);
            
        } elseif ($method === 'POST') {
            // TODO: Call createComment() with the decoded request body
            createComment($db, $input);
            
        } elseif ($method === 'DELETE') {
            // TODO: Get comment id from query parameter or request body
            $commentId = isset($_GET['id']) ? $_GET['id'] : (isset($input['id']) ? $input['id'] : null);
            deleteComment($db, $commentId);
            
        } else {
            // TODO: Return error for unsupported methods
            sendError("Method not allowed", 405);
        }
    }
    
    // ========== INVALID RESOURCE ==========
    else {
        // TODO: Return error for invalid resource
        sendError("Invalid resource. Use 'weeks' or 'comments'", 400);
    }
    
} catch (PDOException $e) {
    // TODO: Handle database errors
    error_log($e->getMessage());
    
    // TODO: Return generic error response with 500 status
    sendError("Database error occurred", 500);
    
} catch (Exception $e) {
    // TODO: Handle general errors
    error_log($e->getMessage());
    sendError("An unexpected error occurred", 500);
}


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Helper function to send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    // TODO: Set HTTP response code
    http_response_code($statusCode);
    
    // TODO: Echo JSON encoded data
    echo json_encode($data);
    
    // TODO: Exit to prevent further execution
    exit();
}

/**
 * Helper function to send error response
 */
function sendError($message, $statusCode = 400) {
    // TODO: Create error response array
    $error = ['success' => false, 'error' => $message];
    
    // TODO: Call sendResponse() with the error array and status code
    sendResponse($error, $statusCode);
}

/**
 * Helper function to validate date format (YYYY-MM-DD)
 */
function validateDate($date) {
    // TODO: Use DateTime::createFromFormat() to validate
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Helper function to sanitize input
 */
function sanitizeInput($data) {
    // TODO: Trim whitespace
    $data = trim($data);
    
    // TODO: Strip HTML tags using strip_tags()
    $data = strip_tags($data);
    
    // TODO: Convert special characters using htmlspecialchars()
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    // TODO: Return sanitized data
    return $data;
}

/**
 * Helper function to validate allowed sort fields
 */
function isValidSortField($field, $allowedFields) {
    // TODO: Check if $field exists in $allowedFields array
    return in_array($field, $allowedFields, true);
}

?>
