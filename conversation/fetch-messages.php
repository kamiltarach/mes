<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$recipient = $_GET['recipient'];
// Pobranie informacji o zalogowanym użytkowniku
$username = $_SESSION['username'];

// Przygotowanie połączenia do bazy danych (dostosuj dane logowania)
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$database = "mes";

// Utworzenie połączenia
$conn = new mysqli($servername, $dbUsername, $dbPassword, $database);

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

// Wiązanie parametrów z wartościami - sender
$querySender = "SELECT id FROM users WHERE username = ?";
$stmtSender = $conn->prepare($querySender);
$stmtSender->bind_param("s", $username);
$stmtSender->execute();
$resultSender = $stmtSender->get_result();
$senderID = $resultSender->fetch_assoc()['id'];

// Wiązanie parametrów z wartościami - recipient
$parts = explode(" ", $recipient);
$lastname = $parts[count($parts) - 1];

$queryRecipient = "SELECT recipient_id FROM recipient WHERE surname = ?";
$stmtRecipient = $conn->prepare($queryRecipient);
$stmtRecipient->bind_param("s", $lastname);
$stmtRecipient->execute();
$resultRecipient = $stmtRecipient->get_result();
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
$stmtSender->close();
$stmtRecipient->close();
$conn->close();
?>
