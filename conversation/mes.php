<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Połączenie z bazą danych
$mysqli = new mysqli("localhost", "root", "", "mes");
if ($mysqli->connect_error) {
    die("Błąd połączenia z bazą danych: " . $mysqli->connect_error);
}

// Pobranie informacji o zalogowanym użytkowniku
$username = $_SESSION['username'];

// Odczytanie danych wysłanej wiadomości
if (isset($_POST['sender']) && isset($_POST['recipient']) && isset($_POST['message'])) {
    $sender = $mysqli->real_escape_string($_POST['sender']);
    $recipient = $mysqli->real_escape_string($_POST['recipient']);
    $message = $mysqli->real_escape_string($_POST['message']);

    // Zapisanie wiadomości do bazy danych
    $query = "INSERT INTO messages (sender_id, recipient_id, message) VALUES ('$sender', '$recipient', '$message')";
    if ($mysqli->query($query) === TRUE) {
        echo "Wiadomość została pomyślnie zapisana w bazie danych.";
    } else {
        echo "Błąd zapisu wiadomości: " . $mysqli->error;
    }
}

$mysqli->close();
?>
