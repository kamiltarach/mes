<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'mes';

// Sprawdzanie, czy przesłano formularz
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Pobieranie wartości z parametrów POST
  $recipient = $_POST['recipient'];
  $message = $_POST['message'];

  // Nawiązywanie połączenia z bazą danych
  $conn = new mysqli($host, $user, $password, $database);

  // Sprawdzanie błędów połączenia
  if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
  }

  // Tworzenie zapytania SQL
  $sql = "INSERT INTO messages (recipient, message) VALUES ('$recipient', '$message')";

  // Wykonanie zapytania
  if ($conn->query($sql) === TRUE) {
    echo "Wiadomość została zapisana w bazie danych";
  } else {
    echo "Błąd zapisu wiadomości: " . $conn->error;
  }

  // Zamykanie połączenia
  $conn->close();
}
?>
