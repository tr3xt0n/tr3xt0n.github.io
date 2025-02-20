<!DOCTYPE html>
<html lang="it">
<head>
    <!-- Meta data for character encoding and viewport settings -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CASA Cavaleri - Albenga Vadino</title>

    <!-- Loading TailwindCSS for styling the page -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

    <!-- Header Section: Main header of the page -->
    <header class="relative bg-cover bg-top h-96" style="background-image: url('images/hero-background.png');">
        <div class="absolute inset-0 bg-black opacity-50"></div>
        <nav class="relative container mx-auto flex justify-between items-center p-4">
            <!-- Logo and CASA Cavaleri name -->
            <div class="flex items-center">
                <img src="images/logo.jpeg" alt="Logo" class="h-10 mr-4">
                <span class="text-white text-2xl font-bold hidden md:inline">CASA Cavaleri</span>
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
                <li><a class="text-white hover:underline" href="index.html">Home</a></li>
                <li><a class="text-white hover:underline" href="about.html">Chi sono</a></li>
                <li><a class="text-white hover:underline" href="contact.html">Contattami</a></li>
            </ul>

            <!-- Mobile navigation links -->
            <ul id="mobileNavLinks" class="md:hidden absolute inset-x-0 top-16 bg-gray-900 text-white flex-col space-y-4 p-4 hidden">
                <li id="userProfile"></li>
                <li><a href="index.html" class="hover:underline">Home</a></li>
                <li><a href="about.html" class="hover:underline">Chi sono</a></li>
                <li><a href="contact.html" class="hover:underline">Contattami</a></li>
            </ul>
        </nav>

        <!-- Text content in the header for desktop -->
        <div class="relative container mx-auto text-white text-center mt-24 md:mt-32 px-4 hidden md:block">
            <h1 class="text-5xl font-bold">CASA Cavaleri</h1>
            <p class="mt-4 text-lg">Un Appartamento a 500 metri dal mare in una zona che brulica di servizi.</p>
            <p class="mt-4 text-lg">Vivi la tua vacanza in comodità</p>
        </div>
    </header>

    <!-- Main Content Section -->
    <main class="container mx-auto px-4 py-16 flex-grow">
        <div class="flex flex-col md:flex-row items-center justify-between">

            <?php
            // Including the database connection
            include('includes/db.php');

            // Get apartment ID from the URL and sanitize input
            $apartment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

            // Prepare and execute the query using a prepared statement to prevent SQL injection
            $stmt = $db->prepare("SELECT * FROM apartments WHERE id = :id");
            $stmt->bindParam(':id', $apartment_id, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch the result
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            

            if ($row) {
                // Fetch reservations related to this apartment
                $reservations = [];
                $reservationStmt = $db->prepare("SELECT * FROM reservations WHERE apartment_id = :apartment_id");
                $reservationStmt->bindParam(':apartment_id', $apartment_id, PDO::PARAM_INT);
                $reservationStmt->execute();

                // Fetch all reservations
                $reservations = $reservationStmt->fetchAll(PDO::FETCH_ASSOC);
                // Decodifica delle immagini dal JSON
                $imagePaths = json_decode($row['image_paths'], true);
            ?>

                <!-- Apartment Image Section -->
                <!--<div class="flex-shrink-0 md:w-1/2 mb-8 md:mb-0">
                    <img src="<?php /*echo htmlspecialchars($row['image_path']); */?>" alt="<?php /*echo htmlspecialchars($row['apartment_name']); */?>" class="w-full sm:w-96 md:w-128 lg:w-1/2">
                </div>-->
                <!-- Grid Image Section -->
            <div class="flex-shrink-0 md:w-1/3 mb-8 md:mb-0">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    <?php foreach ($imagePaths as $image): ?>
                        <div class="group relative">
                            <!-- Thumbnail Image -->
                            <img src="<?php echo htmlspecialchars($image); ?>" alt="Apartment Image" class="w-full h-full object-cover rounded-lg cursor-pointer transition-transform duration-300 group-hover:scale-105" onclick="openLightbox('<?php echo $image; ?>')">
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Lightbox Modal -->
                <div id="lightbox" class="fixed inset-0 bg-black bg-opacity-75 hidden flex justify-center items-center z-50">
                    <span class="absolute top-5 right-5 text-white text-3xl cursor-pointer" onclick="closeLightbox()">×</span>
                    <img id="lightbox-img" src="" alt="Enlarged Image" class="max-w-full max-h-full">
                </div>

                <script>
                    function openLightbox(imageSrc) {
                        document.getElementById('lightbox-img').src = imageSrc;
                        document.getElementById('lightbox').classList.remove('hidden');
                    }

                    function closeLightbox() {
                        document.getElementById('lightbox').classList.add('hidden');
                    }
                </script>
            </div>

                <!-- Apartment Details Section -->
                <div class="md:w-1/3">
                    <h2 class="text-3xl font-semibold text-gray-800 mb-4"><?php echo htmlspecialchars($row['apartment_name']); ?></h2>
                    <p class="text-lg text-gray-700 mb-4"><?php echo htmlspecialchars($row['description']); ?></p>
                    <ul class="text-lg text-gray-600 mb-6">
                        <li><strong>Capacità:</strong> <?php echo htmlspecialchars($row['capacity']); ?> persone</li>
                        <li><strong>Grandezza:</strong> <?php echo htmlspecialchars($row['area']); ?> m²</li>
                        <li><strong>Posizione:</strong> <?php echo htmlspecialchars($row['location']); ?></li>
                    </ul>
                    <div class="flex justify-between items-center">
                        <span class="text-2xl font-semibold text-gray-800">Prezzo Iniziale: €<?php echo htmlspecialchars($row['price']); ?>/notte</span>
                        <a href="booking.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Prenota</a>
                    </div>
                </div>

                <!-- Reservations List Section -->
                <div class="md:w-1/4">
                    <h3 class="text-2xl font-semibold text-gray-800 text-center mb-4">Prenotazioni Esistenti</h3>
                    <?php if (count($reservations) > 0): ?>
                        <table class="min-w-full table-auto border-collapse">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 border-b text-left">Data Inizio</th>
                                    <th class="px-4 py-2 border-b text-left">Data Fine</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservations as $reservation): ?>
                                    <tr>
<!--                                        <td class="px-4 py-2 border-b">--><?php //echo htmlspecialchars($reservation['start_date']); ?><!--</td>-->
                                        <td class="px-4 py-2 border-b"><?php
                                            $date = new DateTime($reservation['start_date']);
                                            echo $date->format('d/m/y') ?></td>
                                        <td class="px-4 py-2 border-b"><?php
                                            $date = new DateTime($reservation['end_date']);
                                            echo $date->format('d/m/y') ?></td>


                                        <!--<td class="px-4 py-2 border-b"><?php /*echo htmlspecialchars($reservation['end_date']); */?></td>-->
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-gray-600">No prenotazioni per questa casa al momento.</p>
                    <?php endif; ?>
                </div>
            <?php
            } else {
                echo "<p>Casa non trovata.</p>";
            }

            // Close the connection (optional, as PDO will automatically close the connection)
            //$db = null;
            ?>

        </div>
    </main>

    <!-- Footer Section -->
    <footer class="bg-gray-800 py-4 text-center text-white">
        <div class="container mx-auto">
            <p>&copy; 2025 CASA Cavaleri. Tutti i diritti riservati..</p>
        </div>
    </footer>

</body>
</html>
