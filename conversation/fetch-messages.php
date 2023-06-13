<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Pobranie informacji o zalogowanym użytkowniku
$username = $_SESSION['username'];

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
$sql = "SELECT * FROM messages WHERE (sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?) ORDER BY timestamp ASC";

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

// Wiązanie parametrów z wartościami - recipient
//wyciaganie nazwiska z $recipient(imie i nazwisko)
$parts = explode(" ", $recipient);
$lastname = $parts[count($parts) - 1];

$queryRecipient = "SELECT recipient_id FROM recipient WHERE surname = '$lastname'";
$resultRecipient = $conn->query($queryRecipient);
$recipientID = $resultRecipient->fetch_assoc()['recipient_id'];

$stmt->bind_param("iiii", $senderID, $recipientID, $recipientID, $senderID);

// Wykonanie zapytania
if ($stmt->execute()) {
    // Pobranie wyników zapytania
    $result = $stmt->get_result();

    // Przygotowanie tablicy na wiadomości
    $messages = array();

    // Pobieranie wiadomości z wyników zapytania
    while ($row = $result->fetch_assoc()) {
        $message = array(
            'sender' => $row['sender_id'],
            'message' => $row['message']
        );
        $messages[] = $message;
    }

    // Zwrócenie wiadomości jako JSON
    echo json_encode($messages);
} else {
    echo "Błąd pobierania wiadomości z bazy danych: " . $stmt->error;
}

// Zamknięcie połączenia i zwolnienie zasobów
$stmt->close();
$conn->close();
?>
