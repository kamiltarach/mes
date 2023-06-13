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

// Wiązanie parametrów z wartościami
$sender_id = 1; // ID zalogowanego użytkownika - dostosuj do swojej implementacji
$recipient_id = 2; // ID rozmówcy - dostosuj do swojej implementacji
$stmt->bind_param("iiii", $sender_id, $recipient_id, $recipient_id, $sender_id);

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
