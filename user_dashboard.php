<?php
session_start(); // Start the session to access session variables

// Check if the user is logged in and has 'regular' user type
if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'regular') {
    // If the user is not logged in as a regular user, redirect them to the home page
    header('Location: index.html');
    exit; // Exit the script to prevent further execution after redirect
}

// Include the database connection
include('includes/db.php');

// Handle reservation deletion
if (isset($_GET['delete_id'])) {
    // If a 'delete_id' is provided in the query string, handle the reservation deletion
    $delete_id = (int)$_GET['delete_id']; // Ensure 'delete_id' is an integer for security

    // Prepare the DELETE query to delete the reservation where the reservation ID matches and the user ID matches the current user's ID
    $deleteStmt = $db->prepare("DELETE FROM reservations WHERE id = :id AND user_id = :user_id");
    $deleteStmt->bindParam(':id', $delete_id, PDO::PARAM_INT); // Bind the reservation ID parameter
    $deleteStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT); // Bind the current user's ID to the query

    // Execute the delete query
    if ($deleteStmt->execute()) {
        // If the query was successful, alert the user
        echo "<script>alert('Reservation deleted successfully');</script>";
    } else {
        // If there was an error deleting the reservation, alert the user
        echo "<script>alert('Error deleting reservation');</script>";
    }
}

// Retrieve all reservations for the current user, along with the apartment name
$reservationStmt = $db->prepare("SELECT r.id, r.apartment_id, r.start_date, r.end_date, a.apartment_name 
                                FROM reservations r 
                                JOIN apartments a ON r.apartment_id = a.id
                                WHERE r.user_id = :user_id");
$reservationStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT); // Bind the user ID to the query
$reservationStmt->execute(); // Execute the query
$reservations = $reservationStmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all reservations for the user

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - CASA Cavaleri</title>
    <!-- Link to the Tailwind CSS stylesheet for styling -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <!-- Header Section -->
    <header class="bg-blue-500 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl">User Dashboard</h1>
            <a href="index.html" class="text-white hover:underline px-3">Home Page</a> <!-- Link to home page -->
        </div>
    </header>

    <main class="container mx-auto p-4">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold mb-4">Benvenuto, <?php echo $_SESSION['username']; ?>!</h2>
            <p class="text-lg">This is your dashboard. Here you can view and manage your bookings and profile.</p>

            <!-- Reservations Table Section -->
            <div class="mt-6">
                <h3 class="text-2xl font-bold mb-4">Your Reservations</h3>

                <?php if (count($reservations) > 0): ?>
                    <!-- If there are reservations, display them in a table -->
                    <table class="min-w-full table-auto border-collapse">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 border-b">Apartment Name</th>
                                <th class="px-4 py-2 border-b">Start Date</th>
                                <th class="px-4 py-2 border-b">End Date</th>
                                <th class="px-4 py-2 border-b">Actions</th> <!-- Column for actions (like delete) -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): ?>
                                <!-- Loop through each reservation and display the details -->
                                <tr>
                                    <td class="px-4 py-2 border-b">
                                        <a href="apartment_details.php?id=<?php echo $reservation['apartment_id']; ?>" class="text-blue-500 hover:underline">
                                            <?php echo htmlspecialchars($reservation['apartment_name']); ?>
                                        </a>
                                    </td>
                                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($reservation['start_date']); ?></td>
                                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($reservation['end_date']); ?></td>
                                    <td class="px-4 py-2 border-b">
                                        <!-- Delete Button -->
                                        <a href="?delete_id=<?php echo $reservation['id']; ?>" class="bg-red-500 text-white py-1 px-4 rounded hover:bg-red-700" 
                                           onclick="return confirm('Are you sure you want to delete this reservation?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <!-- If there are no reservations, display this message -->
                    <p class="text-gray-600">You have no reservations.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="js/app.js"></script>

</body>
</html>
