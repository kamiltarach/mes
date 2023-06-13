<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Pobranie informacji o zalogowanym użytkowniku
$username = $_SESSION['username'];

// Połączenie z bazą danych
$conn = new mysqli('localhost', 'root', '', 'mes');
if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

// Pobranie informacji o rozmówcach z bazy danych
$query = "SELECT name, surmane FROM recipient";
$result = $conn->query($query);

// Przetwarzanie wyników zapytania
$recipients = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recipient = $row['name'] . ' ' . $row['surmane'];
        $recipients[] = $recipient;
    }
}

// Zamknięcie połączenia z bazą danych
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="style-dashboard.css">
    <link rel="icon" href="../logo.png">
</head>
<body>
    <h2>Dashboard - Witaj, <?php echo $username; ?>!</h2>

    <aside>
        <h3>Wybierz rozmówcę:</h3>
        <ul>
            <?php foreach ($recipients as $recipient) { ?>
                <li><a href="conversation.php?recipient=<?php echo urlencode($recipient); ?>"><?php echo $recipient; ?></a></li>
            <?php } ?>
        </ul>
    </aside>
    <p><a href="../mes.html">Powrót na strone główną</a></p>
</body>
</html>
