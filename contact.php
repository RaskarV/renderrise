<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Match form fields
    $fullname     = $_POST['fullname'] ?? '';
    $email        = $_POST['email'] ?? '';
    $phone        = $_POST['phone'] ?? '';
    $project_type = $_POST['project_type'] ?? '';
    $message      = $_POST['message'] ?? '';

    // Validation
    if (empty($fullname) || empty($email) || empty($phone)) {
        echo "<script>alert('Please fill required fields');history.back();</script>";
        exit;
    }

    $smtpUsername = getenv('SMTP_USERNAME');
    $smtpPassword = getenv('SMTP_PASSWORD');

    if ($smtpUsername === false || $smtpUsername === '' || $smtpPassword === false || $smtpPassword === '') {
        http_response_code(500);
        echo "<script>alert('The contact form is not configured. Please contact the site administrator.');</script>";
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUsername;
        $mail->Password   = $smtpPassword;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Email setup
        $mail->setFrom($smtpUsername, 'Website Enquiry');
        $mail->addAddress('sales@renderrise.com.au');
        $mail->addReplyTo($email, $fullname);

        // 📌 FILE UPLOAD HANDLE
        if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] == 0) {

            $fileTmp  = $_FILES['file_upload']['tmp_name'];
            $fileName = $_FILES['file_upload']['name'];
            $fileSize = $_FILES['file_upload']['size'];

            // Max 10MB
            if ($fileSize <= 10 * 1024 * 1024) {
                $mail->addAttachment($fileTmp, $fileName);
            } else {
                echo "<script>alert('File size must be less than 10MB');history.back();</script>";
                exit;
            }
        }

        $mail->isHTML(true);
        $mail->Subject = "New Website Enquiry";

        $mail->Body = "
            <h2>Enquiry Details</h2>
            <p><b>Full Name:</b> $fullname</p>
            <p><b>Email:</b> $email</p>
            <p><b>Phone:</b> $phone</p>
            <p><b>Project Type:</b> $project_type</p>
            <p><b>Message:</b><br>" . nl2br(htmlspecialchars($message)) . "</p>
        ";

        $mail->AltBody = "
            Full Name: $fullname
            Email: $email
            Phone: $phone
            Project Type: $project_type
            Message: $message
        ";

        $mail->send();

        echo "<script>alert('Message sent successfully!');window.location.href='contact.html';</script>";

    } catch (Exception $e) {
        echo "<script>alert('Error: {$mail->ErrorInfo}');</script>";
    }
}
?>