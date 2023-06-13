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
$sender = $_POST['sender'];
$recipient = $_POST['recipient'];
$message = $_POST['message'];

// Odczytanie danych wysłanej wiadomości
if (isset($sender) && isset($recipient) && isset($message)) {

    // Pobranie ID użytkownika (sender) na podstawie nazwy użytkownika
    $querySender = "SELECT id FROM users WHERE username = '$sender'";
    $resultSender = $mysqli->query($querySender);
    $senderID = $resultSender->fetch_assoc()['id'];
    
    // Pobranie ID rozmówcy (recipient) na podstawie nazwiska
    $queryRecipient = "SELECT recipient_id FROM recipient WHERE surmane='$recipient'";
    $resultRecipient = $mysqli->query($queryRecipient);
    $recipientID = $resultRecipient->fetch_assoc()['recipient_id'];

    // Zapisanie wiadomości do bazy danych
    $queryInsert = "INSERT INTO messages (sender_id, recipient_id, message) VALUES ('$senderID', '$recipientID', '$message')";
    if ($mysqli->query($queryInsert) === TRUE) {
        echo "Wiadomość została pomyślnie zapisana w bazie danych.";
    } else {
        echo "Błąd zapisu wiadomości: " . $mysqli->error;
    }
}

$mysqli->close();
?>