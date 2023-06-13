<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Pobranie informacji o zalogowanym użytkowniku
$username = $_SESSION['username'];

// Odczytanie wybranego rozmówcy z parametru POST
$recipient = $_POST['recipient'] ?? '';

// Obsługa wysyłania wiadomości
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message'])) {
        $message = $_POST['message'];

        // Przygotowanie połączenia do bazy danych (dostosuj dane logowania)
        $servername = "localhost";
        $username = "root";
        $password = "password";
        $database = "your_database_name";

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

        // Wiązanie parametrów z wartościami
        $sender_id = 1; // ID zalogowanego użytkownika - dostosuj do swojej implementacji
        $recipient_id = 2; // ID rozmówcy - dostosuj do swojej implementacji
        $stmt->bind_param("iis", $sender_id, $recipient_id, $message);

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
