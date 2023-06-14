<?php
session_start();

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

    // Przygotowanie zapytania SQL
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];

        // Sprawdzenie hasła
        if (password_verify($password, $hashedPassword)) {
            // Poprawne logowanie
            $_SESSION['username'] = $username;
            header("Location: ../conversation/dashboard.php");
        } else {
            echo "<div class='error'>Błędne hasło.</div>";
        }
    } else {
        echo "Użytkownik o podanej nazwie nie istnieje.";
    }

    // Zamknięcie połączenia
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logowanie</title>
    <link rel="stylesheet" href="login_register.css">
    <link rel="icon" href="logo.png">
</head>
<body>
    <h2>Logowanie</h2>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
        <label for="username">Nazwa użytkownika:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Hasło:</label>
        <input type="password" id="password" name="password" required>

        <input type="submit" value="Zaloguj">
    </form>
    <p><a href="../mes.html">Powrót do strony głównej</a></p>
</body>
</html>
