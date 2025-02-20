<?php
// Connection to the database
include 'includes/db.php'; // Includes the database connection file (db.php) to establish the connection with the database

// Initialize search query
$sql = "SELECT * FROM apartments"; // SQL query to select all records from the 'apartments' table
$conditions = []; // Initialize an empty array to store any conditions for the SQL query

// If a search query is provided through the 'search' parameter
if (!empty($_GET['search'])) {
    // Sanitize the search input to prevent SQL injection
    $search = $con->real_escape_string($_GET['search']);
    
    // Add a condition to the array to search for apartment names that match the search term (case-insensitive search)
    $conditions[] = "apartment_name LIKE '%$search%'";
}

// Check if both 'checkin_date' and 'checkout_date' are provided
if (!empty($_GET['checkin_date']) && !empty($_GET['checkout_date'])) {
    // Get the check-in and check-out dates from the GET request
    $checkin_date = $_GET['checkin_date'];
    $checkout_date = $_GET['checkout_date'];

    // Fetch all reservations that overlap with the requested date range
    $reservations_sql = "SELECT * FROM reservations WHERE (start_date <= ? AND end_date >= ?)";
    $stmt = $db->prepare($reservations_sql); // Prepare the SQL statement with placeholders
    $stmt->execute([$checkout_date, $checkin_date]); // Execute the statement with the actual date parameters
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all the reservation records

    // Collect apartment IDs that are reserved during the requested date range
    $reserved_apartments = [];
    foreach ($reservations as $reservation) {
        $reserved_apartments[] = $reservation['apartment_id']; // Collect the apartment IDs that are reserved
    }

    // If any apartments are reserved during the requested date range, exclude them from the apartment search
    if (count($reserved_apartments) > 0) {
        // Add a condition to exclude apartments that are reserved during the requested dates
        $conditions[] = "id NOT IN (" . implode(",", $reserved_apartments) . ")";
    }
}

// If any conditions have been added (such as search or date range filters), apply them to the SQL query
if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions); // Combine conditions using 'AND' to build the full query
}

// Execute the final query and store the result
$result = $db->query($sql);
?>

<!-- Display Apartments -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <?php
    // If there are apartments found matching the query conditions
    if ($result->rowCount() > 0) {
        // Loop through each apartment row in the result set
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) { // Use fetch to get each row as an associative array
            // Display the apartment details in a styled card format
            echo '<div class="border rounded-lg shadow-lg overflow-hidden">';
            echo '<img src="' . $row['image_path'] . '" alt="' . $row['apartment_name'] . '" class="w-full h-48 object-cover">'; // Display the apartment image
            echo '<div class="p-4">';
            echo '<h2 class="text-xl font-bold">' . $row['apartment_name'] . '</h2>'; // Display the apartment name
            echo '<p class="text-gray-700">' . $row['description'] . '</p>'; // Display the apartment description
            echo '<p class="font-semibold text-lg mt-2">Prezzo (€): ' . $row['price'] . '/notte</p>'; // Display the price per night
            echo '<a href="apartmentDetail.php?id=' . $row['id'] . '" class="mt-4 block text-center text-blue-500">Dettagli</a>'; // Link to the detailed page of the apartment
            echo '</div>';
            echo '</div>';
        }
    } else {
        // If no apartments match the search criteria, display a message
        echo "<p>Nessun Appartamento con questi criteri.</p>";
    }
    ?>
</div>
