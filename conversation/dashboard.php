<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Pobranie informacji o zalogowanym użytkowniku
$username = $_SESSION['username'];

// Wyświetlanie linków do rozmówców
$recipients = array(
    "Kamil Tarach",
    "Mścichuj Mickiewicz",
    "Marzanka Nizinna",
    "Fiutek Słowacki",
    "Marek Marucha",
    "Maciej Niemusiał",
    "Dawid Nadsiadło",
    "Jakub Przedczas",
    "Bogusław Nieposłuszny"
);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style-dashboard.css">
    <link rel="icon" href="logo.png">
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
</body>
</html>
