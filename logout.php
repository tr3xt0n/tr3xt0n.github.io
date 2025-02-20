<?php
session_start(); // Start the session. This is required to access or modify session variables.

// Clear all session variables. This removes all data stored in the session.
session_unset(); 

// Destroy the session completely. This will remove the session data and the session ID.
session_destroy(); 

// Return a JSON response indicating the logout was successful.
echo json_encode(['success' => true]);
?>
