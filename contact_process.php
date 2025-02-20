<?php
// Import necessary PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include Composer's autoloader to automatically load the PHPMailer classes
require 'vendor/autoload.php'; // Composer's autoload file

// Check if the form was submitted using the POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve and sanitize form data using htmlspecialchars() to prevent XSS attacks and trim() to remove extra spaces
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Validate form fields to ensure they are not empty
    if (empty($name) || empty($email) || empty($message)) {
        die('All fields are required.'); // If any field is empty, stop the script and show an error message
    }

    // Validate email format using PHP's filter_var function
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Invalid email format.'); // If the email is not valid, stop the script and show an error message
    }

    // Load email configuration from an external file (config.php)
    $config = require 'config.php';

    // Create a new instance of PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Set SMTP server settings for sending email via Gmail
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com'; // SMTP server (Gmail's SMTP server)
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = $config['email']; // Gmail address from the configuration file
        $mail->Password = $config['password']; // App-specific password from the configuration file
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use STARTTLS encryption for security
        $mail->Port = 587; // Set the port for SMTP (587 is commonly used for STARTTLS)

        // Set up the email content
        $mail->setFrom($email, $name); // Set the "From" address with the user's email and name
        $mail->addAddress('anaiscoding@gmail.com'); // Add the recipient's email address (e.g., your own email)
        $mail->Subject = 'New Contact Form Submission'; // Set the email subject
        $mail->Body = "You have received a new message from your website:\n\nName: $name\nEmail: $email\n\nMessage:\n$message"; // Set the email body with the user's input

        // Send the email
        $mail->send(); // Attempt to send the email

        // If successful, output a success message to the user
        echo '<p>Thank you for reaching out. Your message has been sent successfully!</p>';

    } catch (Exception $e) {
        // If an error occurs during the email sending process, output the error message
        echo "<p>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
    }

} else {
    // If the form wasn't submitted with POST, output an error message
    echo '<p>Invalid request method.</p>';
}
?>
