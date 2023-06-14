<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Pobranie informacji o zalogowanym użytkowniku
$sender = $_SESSION['username'];

// Odczytanie wybranego rozmówcy z parametru POST
$recipient = $_POST['recipient'] ?? '';

// Obsługa wysyłania wiadomości
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message'])) {
        $message = $_POST['message'];

        // Przygotowanie połączenia do bazy danych (dostosuj dane logowania)
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "mes";

        // Utworzenie połączenia
        $conn = new mysqli($servername, $username, $password, $database);

        // Sprawdzenie połączenia
        if ($conn->connect_error) {
            die("Błąd połączenia z bazą danych: " . $conn->connect_error);
        }

        // Przygotowanie zapytania SQL
        $sql = "INSERT INTO messages (sender_id, recipient_id, message) VALUES (?, ?, ?)";

        // Utworzenie prepared statement
        $stmt = $conn->prepare($sql);

        // Sprawdzenie poprawności prepared statement
        if ($stmt === false) {
            die("Błąd przygotowywania zapytania SQL: " . $conn->error);
        }

        // Wiązanie parametrów z wartościami - sedner/user
        $querySender = "SELECT id FROM users WHERE username = '$username'";
        $resultSender = $conn->query($querySender);
        $senderID = $resultSender->fetch_assoc()['id'];

        //wyciaganie nazwiska z $recipient(imie i nazwisko)
        $parts = explode(" ", $recipient);
        $lastname = $parts[count($parts) - 1];
    
        $queryRecipient = "SELECT recipient_id FROM recipient WHERE surname = '$lastname'";
        $resultRecipient = $conn->query($queryRecipient);
        $recipientID = $resultRecipient->fetch_assoc()['recipient_id'];

        $stmt->bind_param("iis", $senderID, $recipientID, $message);

        // Wykonanie zapytania
        if ($stmt->execute()) {
            echo "Wiadomość została zapisana w bazie danych";
        } else {
            echo "Błąd zapisu wiadomości do bazy danych: " . $stmt->error;
        }

        // Zamknięcie połączenia i zwolnienie zasobów
        $stmt->close();
        $conn->close();
    }
}
?>
