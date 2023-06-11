<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'mes';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $recipient = $_POST['recipient'];
  $message = $_POST['message'];

  $conn = new mysqli($host, $user, $password, $database);

  if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
  }
  $sql = "INSERT INTO messages (rozmowca, wiadomosc) VALUES ('$recipient', '$message')";

  if ($conn->query($sql) === TRUE) {
    echo "Wiadomość została zapisana w bazie danych";
  } else {
    echo "Błąd zapisu wiadomości: " . $conn->error;
  }

  // Zamykanie połączenia
  $conn->close();
}
?>
