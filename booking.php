<?php
// Start the session to track the user


// Include the database connection file
include('includes/db.php');

// Check if the user is logged in by checking the session variable 'user_id'
// This can be customized based on your login system
session_start();
// Controlla se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=not_logged_in");
    exit;
}

//echo '<pre>' . print_r($_SESSION, TRUE) . '</pre>';

// Retrieve the apartment ID from the URL (using $_GET to get the value from the URL query string)
$apartment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;


// If no apartment ID is provided, display an error message and stop execution

if ($apartment_id == 0) {
    die('ID appartamento non valido.');
}


// Prepare and execute the SQL query to get the apartment details from the database
// Recupera i dettagli dell'appartamento
$stmt = $db->prepare("SELECT * FROM apartments WHERE id = :id");
$stmt->bindParam(':id', $apartment_id, PDO::PARAM_INT);
$stmt->execute();
// Fetch the apartment details as an associative array
$apartment = $stmt->fetch(PDO::FETCH_ASSOC);


// If the apartment is not found, display a message and stop the script
if (!$apartment) {
    die('Appartamento non trovato.');
}
// Variabili per il riepilogo
$checkin_date = $checkout_date = "";
$success = $error = "";

// Handle the reservation form submission when the user submits the form (POST request) // Recupera dati dalla pagina precedente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the check-in and check-out dates from the form
    $checkin_date = $_POST['checkin_date'];
    $checkout_date = $_POST['checkout_date'];
    $user_id = $_SESSION['user_id'];


    //echo '<pre>' . print_r($checkin_date, TRUE) . '</pre>';

    // Check if the apartment is available for the selected dates by querying the reservations table // Controlla se l'appartamento è disponibile nelle date selezionate
    $checkAvailabilitySql = "SELECT * FROM reservations WHERE apartment_id = :apartment_id AND (start_date <= :checkout_date AND end_date >= :checkin_date)";

    $stmt = $db->prepare($checkAvailabilitySql);
    $stmt->bindParam(':apartment_id', $apartment_id, PDO::PARAM_INT);
    $stmt->bindParam(':checkin_date', $checkin_date, PDO::PARAM_STR);
    $stmt->bindParam(':checkout_date', $checkout_date, PDO::PARAM_STR);
    $stmt->execute();
    $isBooked = $stmt->fetchColumn();


    if ($isBooked) {
        $error = "Questo appartamento è già prenotato per le date selezionate.";
    } else {
        // Inserisci la prenotazione nel database
        $stmt = $db->prepare("INSERT INTO reservations (apartment_id, user_id, start_date, end_date) 
                              VALUES (:apartment_id, :user_id, :checkin_date, :checkout_date)");
        $stmt->bindParam(':apartment_id', $apartment_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':checkin_date', $checkin_date, PDO::PARAM_STR);
        $stmt->bindParam(':checkout_date', $checkout_date, PDO::PARAM_STR);
        if ($stmt->execute()) {
            $success = "Prenotazione confermata!";
            // Redirect alla home con messaggio di successo
            header("Location: index.php?message=success");
            exit;
        } else {
            $error = "Errore nella prenotazione. Riprova.";
        }
    }
}
setlocale(LC_ALL, 'it_IT.UTF-8');
// Imposta la data corrente o recupera dal parametro GET
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');

// Recupera tutte le prenotazioni
$sql = "SELECT * FROM reservations";
$stmt = $db->prepare($sql);
$stmt->execute();
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mappa delle prenotazioni per giorno
$reservation_map = [];
foreach ($reservations as $reservation) {
    $start_date = strtotime($reservation['start_date']);
    $end_date = strtotime($reservation['end_date']);

    for ($current_day = $start_date; $current_day <= $end_date; $current_day = strtotime("+1 day", $current_day)) {
        $date_key = date('Y-m-d', $current_day);
        $reservation_map[$date_key] = ['status' => 'occupato']; // Se c'è una prenotazione, il giorno è occupato
    }
}

// Nome del mese
$month_name = date('F Y', strtotime("$year-$month-01"));


?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenotazione - <?php echo htmlspecialchars($apartment['apartment_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>

        function showSummary() {
            let checkin = document.getElementById('checkin_date').value;
            let checkout = document.getElementById('checkout_date').value;
            let pricePerNight = parseFloat(document.getElementById('price').value); // Prezzo per notte


            if (checkin && checkout) {
                let startDate = new Date(checkin);
                let endDate = new Date(checkout);
                let options = {day: '2-digit', month:'long', year:'numeric'};
                let checkinF = startDate.toLocaleDateString('it-IT', options);
                let checkoutF = endDate.toLocaleDateString('it-IT', options);

                if (endDate <= startDate) {
                    // Mostra messaggio di errore
                    document.getElementById('summary').innerHTML = `
                    <p class="text-red-500 font-bold">⚠️ Errore: La data di check-out deve essere successiva al check-in.</p>
                    `;
                    // Disabilita il pulsante di prenotazione
                    document.getElementById('submitBtn').disabled = true;
                    return; // Interrompe l'esecuzione della funzione
                } else {

                    // Calcola il numero di notti
                    let nights = (endDate - startDate) / (1000 * 3600 * 24);
                    // Calcola il totale della prenotazione
                    let totalCost = (nights * pricePerNight).toFixed(2);
                    //Aggiorna il riepilogo
                    document.getElementById('summary').innerHTML = `
                <p><strong>Check-in:</strong> ${checkinF}</p>
                <p><strong>Check-out:</strong> ${checkoutF}</p>
                <p><strong>Notti:</strong> ${nights}</p>
                <p><strong>Totale:</strong> €${totalCost}</p>
                 `;

                    // Cambia il testo del pulsante di prenotazione
                    document.getElementById('submitBtn').innerText = "Conferma Prenotazione";

                }


            }
        }


    </script>
</head>

<body class="bg-gray-100 py-8 px-4">
<nav class="relative container mx-auto flex justify-between items-center p-4">
    <!-- Logo and CASA Cavaleri name -->
    <div class="flex items-center">
        <img src="images/logo.jpeg" alt="Logo" class="h-10 mr-4">
        <span class="text-blue-500 text-2xl font-bold hidden md:inline"><?php echo htmlspecialchars($apartment['apartment_name']); ?></span>
    </div>

    <!-- Mobile menu button icon -->
    <button class="text-white md:hidden" id="mobileMenuButton">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <!-- Desktop navigation links -->
    <ul id="navLinks" class="hidden md:flex space-x-4">
        <li id="userProfile"></li>
        <li><a class="text-blue-500 hover:underline" href="index.html">Home</a></li>
        <li><a class="text-blue-500 hover:underline" href="about.html">Chi sono</a></li>
        <li><a class="text-blue-500 hover:underline" href="contact.html">Contattami</a></li>
    </ul>

    <!-- Mobile navigation links -->
    <ul id="mobileNavLinks"
        class="md:hidden absolute inset-x-0 top-16 bg-gray-900 text-white flex-col space-y-4 p-4 hidden">
        <li id="userProfile"></li>
        <li><a href="index.html" class="hover:underline">Home</a></li>
        <li><a href="about.html" class="hover:underline">Chi sono</a></li>
        <li><a href="contact.html" class="hover:underline">Contattami</a></li>
    </ul>
</nav>
<div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow-lg">
    <h1 class="text-3xl font-semibold text-center mb-4">Prenotazione
        di <?php echo htmlspecialchars($apartment['apartment_name']); ?></h1>

    <!-- Messaggi di errore/successo -->
    <?php if ($error): ?>
        <div class="text-red-500 text-center mb-4 p-2 bg-red-100 rounded">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="text-green-500 text-center mb-4 p-2 bg-green-100 rounded">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Form di prenotazione -->
    <form action="" method="POST" class="space-y-4">
        <div>
            <label for="checkin_date" class="block font-semibold">Check-in:</label>
            <input type="date" name="checkin_date" id="checkin_date" required onchange="showSummary()"
                   class="w-full p-2 border border-gray-300 rounded">
        </div>

        <div>
            <label for="checkout_date" class="block font-semibold">Check-out:</label>
            <input type="date" name="checkout_date" id="checkout_date" required onchange="showSummary()"
                   class="w-full p-2 border border-gray-300 rounded">
        </div>

        <!-- Campo nascosto per il prezzo per notte -->
        <input type="hidden" id="price" value="<?php echo htmlspecialchars($apartment['price']); ?>">

        <div id="summary" class="mt-4 p-4 bg-gray-50 border rounded-md shadow-sm">
            <!-- Riepilogo delle prenotazioni -->
        </div>

        <button type="submit" id="submitBtn"
                class="w-full py-2 px-4 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 mt-4">
            Prenota
        </button>
    </form>
</div>
<!-- Calendario delle Prenotazioni -->
<div class="max-w-3xl mx-auto bg-white p-6 mt-8 rounded-lg shadow-md">
    <!-- Intestazione con Navigazione -->
    <div class="flex justify-between items-center mb-4">
        <a href="?month=<?= ($month - 1 <= 0 ? 12 : $month - 1) ?>&year=<?= ($month - 1 <= 0 ? $year - 1 : $year) ?>"
           class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">
            Prev
        </a>
        <h2 class="text-xl font-bold"><?= $month_name ?></h2>
        <a href="?month=<?= ($month + 1 > 12 ? 1 : $month + 1) ?>&year=<?= ($month + 1 > 12 ? $year + 1 : $year) ?>"
           class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">
            Next
        </a>
    </div>

    <!-- Calendario -->
    <div class="grid grid-cols-7 gap-1 text-center">
        <?php
        $days_of_week = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
        foreach ($days_of_week as $day) {
            echo "<div class='font-bold'>$day</div>";
        }

        // Trova il primo giorno del mese e il numero di giorni
        $first_day_of_month = strtotime("$year-$month-01");
        $days_in_month = date("t", $first_day_of_month);
        $first_day_of_week = date("N", $first_day_of_month);

        // Spazi vuoti prima del primo giorno
        for ($i = 1; $i < $first_day_of_week; $i++) {
            echo "<div></div>";
        }

        // Genera i giorni
        for ($day = 1; $day <= $days_in_month; $day++) {
            $date_key = date('Y-m-d', strtotime("$year-$month-$day"));
            $status = $reservation_map[$date_key]['status'] ?? 'libero';

            // Stili in base allo stato
            $color_class = ($status == 'occupato') ? 'bg-red-500 text-white' :
                (($status == 'inattesa') ? 'bg-yellow-500 text-white' : 'bg-green-500 text-white');

            echo "<div class='p-2 rounded-lg $color_class'>$day</div>";
        }
        ?>
    </div>
</div>


</body>
</html>

