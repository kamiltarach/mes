<?php
// Połączenie z bazą danych
$mysqli = new mysqli('localhost', 'root', '', 'mes');

// Sprawdzenie, czy połączenie się udało
if ($mysqli->connect_error) {
    die('Błąd połączenia: ' . $mysqli->connect_error);
}

// Sprawdzenie, czy formularz został wysłany
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pobranie danych z formularza
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Filtrowanie danych i przygotowanie zapytania
    $username = $mysqli->real_escape_string($username);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Przygotowanie zapytania SQL
    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashedPassword')";

    // Wykonanie zapytania
    if ($mysqli->query($sql) === true) {
        echo "Rejestracja zakończona sukcesem.";
        header('Location: login.php');
    } else {
        echo "<div class='error'>Błąd rejestracji: ".$mysqli->error."</div>";
    }

    // Zamknięcie połączenia
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rejestracja</title>
    <link rel="stylesheet" href="register.css">
    <link rel="icon" href="../logo.png">
</head>
<body>
    <h2>Rejestracja</h2>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
        <label for="username">Nazwa użytkownika:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Hasło:</label>
        <input type="password" id="password" name="password" required>

        <input type="submit" value="Zarejestruj">
    </form>
    <p><a href="../mes.html">Powrót do strony głównej</a></p>
</body>
</html>
