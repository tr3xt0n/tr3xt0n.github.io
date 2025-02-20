<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<?php

session_start();

// Include the database connection file
include('includes/db.php');

$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Nome del mese in italiano
setlocale(LC_TIME, 'it_IT.UTF-8');
$month_name = strftime('%B', strtotime("$year-$month-01"));


// Connessione al database
$sql = "SELECT r.*, a.price 
        FROM reservations r
        JOIN apartments a ON r.apartment_id = a.id
        WHERE (YEAR(r.start_date) = :year AND MONTH(r.start_date) = :month)
           OR (YEAR(r.end_date) = :year AND MONTH(r.end_date) = :month)
           OR (r.start_date <= :end_of_month AND r.end_date >= :start_of_month)";

$stmt = $db->prepare($sql);
$stmt->execute([
    ':year' => $year,
    ':month' => $month,
    ':start_of_month' => "$year-$month-01",
    ':end_of_month' => date("Y-m-t", strtotime("$year-$month-01"))
]);

$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ottieni il nome del mese
$month_name = date('F', strtotime("$year-$month-01")); // Es. "January", "February", ecc.


// Crea una mappa delle prenotazioni per il calendario
$reservation_map = [];
foreach ($reservations as $reservation) {
    $start_date = strtotime($reservation['start_date']);
    $end_date = strtotime($reservation['end_date']);

    for ($current_day = $start_date; $current_day <= $end_date; $current_day = strtotime("+1 day", $current_day)) {
        $reservation_map[date('Y-m-d', $current_day)] = [
            'status' => $reservation['status'],
            'price' => $reservation['price'] ?? 0 // Evita errori se il prezzo non è definito
        ];
    }
// Cicliamo le prenotazioni e aggiorniamo lo status
foreach ($reservations as $reservation) {
    $start_date = strtotime($reservation['start_date']);
    $end_date = strtotime($reservation['end_date']);

    for ($current_day = $start_date; $current_day <= $end_date; $current_day = strtotime("+1 day", $current_day)) {
        $date_key = date('Y-m-d', $current_day);

        // Se la data esiste nella mappa del mese, segniamola come "occupata"
        if (isset($reservation_map[$date_key])) {
            $reservation_map[$date_key]['status'] = 'occupato';
        }
    }
}
}
?>
<!-- Header con navigazione mesi -->
<div class="flex items-center justify-between bg-blue-500 text-white p-3 rounded-t-lg">
    <!-- Pulsante per mese precedente -->
    <a href="?month=<?php echo ($month - 1 <= 0 ? 12 : $month - 1); ?>&year=<?php echo ($month - 1 <= 0 ? $year - 1 : $year); ?>"
       class="px-6 py-2 bg-blue-700 rounded-lg hover:bg-blue-800 transition">
        &larr; Prev
    </a>

    <!-- Nome del mese e anno -->
    <h2 class="text-xl font-semibold uppercase">
        <?php echo ucfirst(strftime('%B %Y', strtotime("$year-$month-01"))); ?>
    </h2>

    <!-- Pulsante per mese successivo -->
    <a href="?month=<?php echo ($month + 1 > 12 ? 1 : $month + 1); ?>&year=<?php echo ($month + 1 > 12 ? $year + 1 : $year); ?>"
       class="px-6 py-2 bg-blue-700 rounded-lg hover:bg-blue-800 transition">
        Next &rarr;
    </a>
</div>


<!-- Calendario -->
<div class="overflow-x-auto">
<table class="w-full border-collapse border border-gray-300">
    <tr>
        <th class="border p-2">Lun</th>
        <th class="border p-2">Mar</th>
        <th class="border p-2">Mer</th>
        <th class="border p-2">Gio</th>
        <th class="border p-2">Ven</th>
        <th class="border p-2">Sab</th>
        <th class="border p-2">Dom</th>
    </tr>
    <tr>

        <?php
        // Trova il primo giorno del mese e il numero totale di giorni
        $first_day_of_month = strtotime("$year-$month-01");
        $days_in_month = date("t", $first_day_of_month);
        $first_day_of_week = date("N", $first_day_of_month); // 1 (Lun) - 7 (Dom)

        // Stampa celle vuote fino al primo giorno del mese
        for ($i = 1; $i < $first_day_of_week; $i++) {
            echo '<td class="border p-2 bg-gray-200"></td>';
        }

        // Stampa i giorni del mese con lo stato della prenotazione
        for ($day = 1; $day <= $days_in_month; $day++) {
            $current_date = date("Y-m-d", strtotime("$year-$month-$day"));
            $status = $reservation_map[$current_date]['status'] ?? 'libero';
            $price = $reservation_map[$current_date]['price'] ?? 'N/A';

            // Colore di sfondo in base allo stato
            $bg_color = match ($status) {
                'occupato' => 'bg-red-400',
                'inattesa' => 'bg-yellow-400',
                default => 'bg-green-400',
            };

            echo "<td class='border p-2 text-center $bg_color'>";
            echo "<p><strong>$day</strong></p>";
            echo "<p>Status: $status</p>";
            echo "<p>€$price/notte</p>";
            echo "</td>";

            // Vai a capo alla fine della settimana
            if (date("N", strtotime($current_date)) == 7) {
                echo "</tr><tr>";
            }
        }
        ?>
    </tr>
</table>
</div>
</body>
</html>