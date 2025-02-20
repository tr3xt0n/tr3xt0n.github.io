<?php
session_start(); // Start the PHP session, enabling session variables to be used

// Include the database connection file to connect to the database
include('includes/db.php');

// Check if the user is already logged in by looking for a session variable 'username'
if (isset($_SESSION['username'])) {
    // If the user is already logged in, return a JSON response with the session data
    echo json_encode([
        'success' => true, // Indicate a successful login
        'username' => $_SESSION['username'], // Include the username of the logged-in user
        'user_type' => $_SESSION['user_type'] // Include the user type (e.g., admin, user)
    ]);
    exit; // Exit the script after sending the session data response, no need to continue further
}

// Get the raw POST data sent to the PHP script (JSON) and decode it into an associative array
$data = json_decode(file_get_contents('php://input'), true);

// Sanitize the username by trimming any excess whitespace
$username = trim($data['username']);
// Get the password directly from the decoded data (no trimming necessary)
$password = $data['password']; 

// Prepare a SQL query to fetch user data from the 'users' table based on the provided username
$stmt = $db->prepare("SELECT id, username, password, user_type FROM users WHERE username = ?");
// Execute the prepared statement with the username parameter
$stmt->execute([$username]);

// Fetch the result as an associative array
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if a user was found and if the provided password matches the hashed password in the database
if ($user && password_verify($password, $user['password'])) {
    // If credentials are valid, start a session and store user data in session variables
    $_SESSION['username'] = $user['username']; // Store the username in the session
    $_SESSION['user_type'] = $user['user_type']; // Store the user type (e.g., admin, user)
    $_SESSION['user_id'] = $user['id']; // Store the user ID in the session

    // Return a JSON response indicating a successful login with user data
    echo json_encode([
        'success' => true, // Indicate a successful login
        'username' => $user['username'], // Include the username of the logged-in user
        'user_type' => $user['user_type'] // Include the user type (e.g., admin, user)
    ]);
} else {
    // If the login fails (invalid username or password), return a JSON response indicating failure
    echo json_encode([
        'success' => false, // Indicate that the login attempt failed
        'message' => 'Invalid credentials' // Provide an error message
    ]);
}
?>
