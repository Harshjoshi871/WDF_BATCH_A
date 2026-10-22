<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method.");
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$course = trim($_POST["course"] ?? "");
$message = trim($_POST["message"] ?? "");

$name = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$phone = preg_replace("/[^0-9]/", "", $phone);
$course = htmlspecialchars($course, ENT_QUOTES, "UTF-8");
$message = htmlspecialchars($message, ENT_QUOTES, "UTF-8");

$errors = [];

if (empty($name)) {
    $errors[] = "Name is required.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

if (!preg_match("/^[0-9]{10}$/", $phone)) {
    $errors[] = "Phone number must contain exactly 10 digits.";
}

if (empty($course)) {
    $errors[] = "Please select a course.";
}

if (!empty($errors)) {
    echo "<!DOCTYPE html>";
    echo "<html>";
    echo "<head>";
    echo "<title>Error</title>";
    echo "<link rel='stylesheet' href='style.css'>";
    echo "</head>";
    echo "<body>";

    echo "<div class='result error'>";
    echo "<h2>Registration Failed</h2>";
    echo "<ul>";

    foreach ($errors as $error) {
        echo "<li>" . $error . "</li>";
    }

    echo "</ul>";
    echo "<a href='index.html'>Go Back</a>";
    echo "</div>";

    echo "</body>";
    echo "</html>";

    exit;
}

$file = "students.csv";

$fileHandle = fopen($file, "a");

if ($fileHandle === false) {
    die("Unable to open storage file.");
}

if (flock($fileHandle, LOCK_EX)) {

    fputcsv($fileHandle, [
        $name,
        $email,
        $phone,
        $course,
        $message,
        date("Y-m-d H:i:s")
    ]);

    flock($fileHandle, LOCK_UN);
    fclose($fileHandle);

} else {
    fclose($fileHandle);
    die("Unable to lock storage file.");
}

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Success</title>";
echo "<link rel='stylesheet' href='style.css'>";
echo "</head>";
echo "<body>";

echo "<div class='result success'>";
echo "<h2>Registration Successful!</h2>";
echo "<p>Your information has been stored successfully.</p>";
echo "<a href='index.html'>Register Another Student</a>";
echo "</div>";

echo "</body>";
echo "</html>";

?>