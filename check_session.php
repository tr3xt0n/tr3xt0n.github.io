<?php
// Start the session to access session variables
session_start();

// Check if the user is logged in by verifying if 'username' and 'user_type' exist in the session
if (isset($_SESSION['username']) && isset($_SESSION['user_type'])) {
    // If the user is logged in, send a JSON response with the user's data
    echo json_encode([
        'success' => true, // Indicate that the request was successful
        'username' => $_SESSION['username'], // Include the logged-in user's username in the response
        'user_type' => $_SESSION['user_type'] // Include the user's type (e.g., admin, regular user, etc.)
    ]);
} else {
    // If the user is not logged in, send a JSON response indicating failure
    echo json_encode(['success' => false]);
}
?>
