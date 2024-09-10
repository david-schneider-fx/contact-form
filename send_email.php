<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $email_from = $_POST['email_from'];
    $email_to = $_POST['email_to'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // File upload handling
    $file_attached = false;
    $file_path = '';
    $file_name = '';
    
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['attachment']['tmp_name'];
        $file_name = $_FILES['attachment']['name'];
        $file_path = "uploads/" . basename($file_name);
        
        // Ensure upload directory exists
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        // Move the uploaded file to the 'uploads' directory
        if (move_uploaded_file($file_tmp_path, $file_path)) {
            $file_attached = true;
        } else {
            echo "Error uploading the file.";
            exit;
        }
    }

    // Email headers
    $headers = "From: " . $email_from . "\r\n";
    
    // If attachment is present, handle the attachment
    if ($file_attached) {
        $file_content = chunk_split(base64_encode(file_get_contents($file_path)));
        $boundary = md5(time());  // Generate a unique boundary string

        // Define headers for attachment
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

        // Email body with attachment
        $email_body = "--$boundary\r\n";
        $email_body .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
        $email_body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $email_body .= $message . "\r\n\r\n";

        // Attachment part
        $email_body .= "--$boundary\r\n";
        $email_body .= "Content-Type: application/octet-stream; name=\"" . $file_name . "\"\r\n";
        $email_body .= "Content-Transfer-Encoding: base64\r\n";
        $email_body .= "Content-Disposition: attachment; filename=\"" . $file_name . "\"\r\n\r\n";
        $email_body .= $file_content . "\r\n\r\n";
        $email_body .= "--$boundary--";
    } else {
        // Email body without attachment
        $email_body = $message;
    }

    // Send the email
    if (mail($email_to, $subject, $email_body, $headers)) {
        echo "Email successfully sent!";
    } else {
        echo "Failed to send the email.";
    }
}
?>
