<?php
// Start the session to access session variables
session_start();

// Check if the user is logged in and if the user is an admin
if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'admin') {
    // Redirect non-admin users to the homepage if they are not logged in as admin
    header('Location: index.html');
    exit; // Stop further script execution
}

// Include the database connection file
include('includes/db.php');

// Handle reservation deletion if the delete_id is passed in the URL
if (isset($_GET['delete_id'])) {
    // Sanitize and cast the delete_id to an integer for security
    $delete_id = (int)$_GET['delete_id'];

    // Prepare the SQL query to delete the reservation based on the ID
    $deleteStmt = $db->prepare("DELETE FROM reservations WHERE id = :id");
    // Bind the :id parameter to the delete_id value
    $deleteStmt->bindParam(':id', $delete_id, PDO::PARAM_INT);

    // Execute the statement and check for success
    if ($deleteStmt->execute()) {
        // Show a success message if the reservation was deleted successfully
        echo "<script>alert('Reservation deleted successfully');</script>";
    } else {
        // Show an error message if something went wrong
        echo "<script>alert('Error deleting reservation');</script>";
    }
}

// Retrieve all reservations from the database
$reservationStmt = $db->prepare("SELECT * FROM reservations");
$reservationStmt->execute(); // Execute the query
$reservations = $reservationStmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all results as an associative array, php data object
?>
<?php
// Esegui la query per ottenere il nome dell'utente insieme ai dettagli della prenotazione
$sql = "SELECT reservations.*, users.username 
                                FROM reservations
                                JOIN users ON reservations.user_id = users.id";

$stmt = $db->prepare($sql);
$stmt->execute();

// Recupera tutte le prenotazioni con il nome utente
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CASA Cavaleri</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <!-- Header section for the Admin Dashboard -->
    <header class="bg-blue-500 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl">Admin Dashboard</h1>
            <!-- Link to navigate back to the homepage -->
            <a href="index.html" class="text-white hover:underline px-3">Home Page</a>
        </div>
    </header>

    <!-- Main content of the page -->
    <main class="container mx-auto p-4">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <!-- Welcome text for the admin -->
            <h2 class="text-2xl font-bold mb-4">Benvenuto,<?php echo $_SESSION['user_type']; ?> <?php echo $_SESSION['username']; ?></h2>
            <p class="text-lg">Questa è la dashboard in cui puoi gestire i dati degli utenti, visualizzare le prenotazioni e altro ancora.</p>

            <!-- Reservation Table Section -->
            <div class="mt-6">
                <h3 class="text-2xl font-bold mb-4">Prenotazioni</h3>

                <!-- Check if there are any reservations -->
                <?php if (count($reservations) > 0): ?>
                    <!-- Reservation table -->
                    <table class="min-w-full table-auto border-collapse">
                        <thead>
                            <tr>
                                <!-- Table headers for the reservation data -->
                                <th class="px-4 py-2 border-b">Casa</th>
                                <th class="px-4 py-2 border-b">User ID</th>
                                <th class="px-4 py-2 border-b">User Name</th>
                                <th class="px-4 py-2 border-b">Checkin</th>
                                <th class="px-4 py-2 border-b">Checkout</th>
                                <th class="px-4 py-2 border-b">Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                        <!-- Loop through each reservation to display it in the table -->
                            <?php foreach ($reservations as $reservation): ?>

                                <tr>
                                    <!-- Display each reservation's details -->
                                    <td class="px-4 py-2 border-b text-center"><?php echo htmlspecialchars($reservation['apartment_id']); ?></td>
                                    <td class="px-4 py-2 border-b text-center"><?php echo htmlspecialchars($reservation['user_id']); ?></td>
                                    <td class="px-4 py-2 border-b text-center"><?php echo htmlspecialchars($reservation['username']); ?></td>
                                    <td class="px-4 py-2 border-b text-center"><?php
                                        $date = new DateTime($reservation['start_date']);
                                        echo $date->format('d/m/y') ?></td>
                                    <td class="px-4 py-2 border-b text-center"><?php
                                        $date = new DateTime($reservation['end_date']);
                                        echo $date->format('d/m/y') ?></td>
                                    <td class="px-4 py-2 border-b text-center">





                                        <!-- Delete Button -->
                                        <!-- Link that triggers reservation deletion -->
                                        <a href="?delete_id=<?php echo $reservation['id']; ?>" class="bg-red-500 text-white py-1 px-4 rounded hover:bg-red-700" onclick="return confirm('Are you sure you want to delete this reservation?');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <!-- Message if there are no reservations -->
                    <p class="text-gray-600">No reservations found.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Link to external JavaScript file (if needed for additional functionality) -->
    <script src="js/app.js"></script>

</body>
</html>
