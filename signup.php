<?php
include('includes/db.php'); // Include the database connection from an external file 'db.php'

// Set the response header to indicate that the content being returned is JSON
header('Content-Type: application/json');

// Retrieve and decode the JSON data sent in the request body
$data = json_decode(file_get_contents('php://input'), true);

// Extract the 'username', 'email', and 'password' values from the decoded JSON data
$username = $data['username'];
$email = $data['email'];

// Hash the password using bcrypt before saving it to the database for security
$password = password_hash($data['password'], PASSWORD_BCRYPT);

try {
    // Prepare the SQL query to insert the new user into the 'users' table
    $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    
    // Execute the query by passing the username, email, and hashed password as parameters
    $stmt->execute([$username, $email, $password]);

    // After successfully inserting the user, fetch the ID of the newly inserted user
    $userId = $db->lastInsertId(); // This retrieves the ID of the last inserted row (the new user)

    // Prepare another query to get the 'user_type' for the newly registered user
    $stmt = $db->prepare("SELECT user_type FROM users WHERE id = ?");
    
    // Execute the query with the user ID to fetch the user type
    $stmt->execute([$userId]);

    // Fetch the result as an associative array
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Respond with a JSON indicating success and include the user's type
    echo json_encode(['success' => true, 'user_type' => $user['user_type']]);
} catch (PDOException $e) {
    // If an error occurs, catch the exception and return a failure response with the error message
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
