<?php
// view.php

// Check if the file parameter is set in the URL
if (isset($_GET['file'])) {
    $file = basename($_GET['file']); // Sanitize the file name

    // Define the path to the uploads directory
    $filePath = 'uploads/' . $file;

    // Check if the file exists
    if (file_exists($filePath)) {
        // Get the file's MIME type
        $mimeType = mime_content_type($filePath);

        // Set headers to display the file in the browser
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . $file . '"');
        header('Content-Length: ' . filesize($filePath));

        // Read the file and send it to the output buffer
        readfile($filePath);
        exit;
    } else {
        echo 'File not found!';
    }
} else {
    echo 'No file specified!';
}
?>
