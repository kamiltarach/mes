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
$conn = mysqli_connect($servername, $dbUsername, $dbPassword, $database);

// Sprawdzenie połączenia
if (!$conn) {
    die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
}

// Przygotowanie zapytania SQL
$sql = "SELECT m.sender_id, m.message
        FROM messages AS m 
        INNER JOIN users AS u ON u.id = m.sender_id 
        WHERE (m.sender_id = ? AND m.recipient_id = ?) 
        OR (m.sender_id = ? AND m.recipient_id = ?) 
        ORDER BY m.timestamp ASC";

// Utworzenie prepared statement
$stmt = mysqli_prepare($conn, $sql);

// Sprawdzenie poprawności prepared statement
if (!$stmt) {
    die("Błąd przygotowywania zapytania SQL: " . mysqli_error($conn));
}

// Wiązanie parametrów z wartościami - sender
$querySender = "SELECT username FROM users WHERE id = ?";
$stmtSender = mysqli_prepare($conn, $querySender);
mysqli_stmt_bind_param($stmtSender, "i", $senderID);

// Pobranie ID zalogowanego użytkownika (sender)
$querySenderID = "SELECT id FROM users WHERE username = ?";
$stmtSenderID = mysqli_prepare($conn, $querySenderID);
mysqli_stmt_bind_param($stmtSenderID, "s", $username);
mysqli_stmt_execute($stmtSenderID);
$resultSenderID = mysqli_stmt_get_result($stmtSenderID);
$senderID = mysqli_fetch_assoc($resultSenderID)['id'];

// Wiązanie parametrów z wartościami - recipient
$queryRecipient = "SELECT recipient_id FROM recipient WHERE CONCAT(name, ' ',surname) = ?";
$queryRecipient = html_entity_decode($queryRecipient);
$stmtRecipient = mysqli_prepare($conn, $queryRecipient);
mysqli_stmt_bind_param($stmtRecipient, "s", $recipient);

// Wiązanie parametrów z wartościami - sender i recipient
mysqli_stmt_bind_param($stmt, "iiii", $senderID, $recipientID, $recipientID, $senderID);

// Pobranie ID odbiorcy (recipient)
mysqli_stmt_execute($stmtRecipient);
$resultRecipient = mysqli_stmt_get_result($stmtRecipient);
if (mysqli_num_rows($resultRecipient) > 0) {
    $recipientID = mysqli_fetch_assoc($resultRecipient)['recipient_id'];
} else {
    die("Nie odnaleziono użytkownika o podanym odbiorcy.");
}

// Wykonanie zapytania
if (mysqli_stmt_execute($stmt)) {
    // Pobranie wyników zapytania
    $result = mysqli_stmt_get_result($stmt);

    // Przygotowanie tablicy na wiadomości
    $messages = array();

    // Pobieranie wiadomości z wyników zapytania
    while ($row = mysqli_fetch_assoc($result)) {
        $senderID = $row['sender_id'];
        $message = array(
            'sender' => '',
            'message' => $row['message']
        );

        // Pobranie nazwy nadawcy
        mysqli_stmt_execute($stmtSender);
        mysqli_stmt_bind_result($stmtSender, $senderName);
        mysqli_stmt_fetch($stmtSender);

        $message['sender'] = $senderName;

        $messages[] = $message;
    }

    // Zwrócenie wiadomości jako odpowiedź JSON
    echo json_encode($messages);
} else {
    die("Błąd wykonania zapytania: " . mysqli_stmt_error($stmt));
}

// Zamknięcie połączenia i zasobów
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmtRecipient);
mysqli_stmt_close($stmtSenderID);
mysqli_close($conn);
?>
