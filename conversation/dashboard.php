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
$query = "SELECT name, surname FROM recipient";
$result = $conn->query($query);

// Przetwarzanie wyników zapytania
$recipients = array();
while ($row = $result->fetch_assoc()) {
    $name = $row['name'];
    $surname = $row['surname'];
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

    <div class="container">
        <aside>
            <h3>Wybierz rozmówcę:</h3>
            <ul>
            <?php foreach ($recipients as $recipient) { ?>
                <li>
                    <a href="#" onclick="submitForm('<?php echo $recipient; ?>'); return false;"><?php echo $recipient; ?></a>
                    <form id="form-<?php echo $recipient; ?>" action="conversation.php" method="POST" style="display: none;">
                        <input type="hidden" name="recipient" value="<?php echo $recipient; ?>">
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

        <div class="content">
            <h3>Witaj na stronie Dashboard!</h3>
            <p>Tutaj możesz wybrać rozmówcę i rozpocząć konwersację.</p>
            <img src="image.jpg" alt="Obrazek" width="200" height="200">
            <p><a href="../mes.html">Powrót na stronę główną</a>
        </div>
    </div>

</body>
</html>