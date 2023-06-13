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
while ($row = $result->fetch_assoc()) {
    $name = utf8_encode($row['name']);
    $surname = utf8_encode($row['surmane']);
    $recipient = $name.' '.$surname;
    $recipients[] = $recipient;
}
// Zamknięcie połączenia z bazą danych
$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">
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
            <li>
                <a href="#" onclick="submitForm('<?php echo urlencode(utf8_decode($recipient)); ?>'); return false;"><?php echo $recipient; ?></a>
                <form id="form-<?php echo urlencode(utf8_decode($recipient)); ?>" action="conversation.php" method="POST" style="display: none;">
                    <input type="hidden" name="recipient" value="<?php echo urlencode(utf8_decode($recipient)); ?>">
                </form>
            </li>
        <?php } ?>
        <script>
            function submitForm(recipient) {
                document.getElementById('form-' + recipient).submit();
                window.location.href = 'conversation.php?recipient=' + encodeURIComponent(recipient);
            }
        </script>
        </ul>
    </aside>
    <p><a href="../mes.html">Powrót na strone główną</a></p>
</body>
</html>
