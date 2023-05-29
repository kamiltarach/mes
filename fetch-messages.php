<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'mes';

// Nawiązywanie połączenia z bazą danych
$conn = new mysqli($host, $user, $password, $database);

// Sprawdzanie błędów połączenia
if ($conn->connect_error) {
  die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

// Pobieranie wiadomości z bazy danych
$sql = "SELECT * FROM messages";
$result = $conn->query($sql);

// Wyświetlanie wiadomości
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    echo "<div class='message'>" . $row["recipient"] . ": " . $row["message"] . "</div>";
  }
} else {
  echo "Brak wiadomości.";
}

// Zamykanie połączenia
$conn->close();
?>
