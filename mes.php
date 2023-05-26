<!DOCTYPE html>
<html>
<head>
  <title>Messenger</title>
  <link rel="stylesheet" href="mes.css">
</head>
<body>
  <div id="message-container">
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
  </div>
  <input type="text" id="message-input" placeholder="Wpisz wiadomość" />
  <select id="recipient-select">
    <option value="">Wybierz rozmówcę</option>
    <option value="user1">Użytkownik 1</option>
    <option value="user2">Użytkownik 2</option>
    <option value="user3">Użytkownik 3</option>
  </select>
  <button id="send-button">Wyślij</button>

  <script>
    // Funkcja do obsługi przycisku "Wyślij"
    function sendMessage() {
      var messageInput = document.getElementById("message-input");
      var recipientSelect = document.getElementById("recipient-select");
      var messageContainer = document.getElementById("message-container");

      // Pobieranie wybranego rozmówcy
      var recipient = recipientSelect.value;

      // Sprawdzanie, czy wybrano rozmówcę
      if (recipient === "") {
        alert("Wybierz rozmówcę!");
        return;
      }

      // Tworzenie nowego elementu wiadomości
      var messageElement = document.createElement("div");
      messageElement.innerText = recipient + ": " + messageInput.value;

      // Dodawanie wiadomości do kontenera na początku
      messageContainer.insertBefore(messageElement, messageContainer.firstChild);

      // Czyszczenie pola tekstowego po wysłaniu wiadomości
      messageInput.value = "";

      // Wysłanie wiadomości do bazy danych
      sendMessageToDatabase(recipient, messageElement.innerText);
    }

    // Funkcja do wysyłania wiadomości do bazy danych
    function sendMessageToDatabase(recipient, message) {
      // Po stronie serwera użyj języka programowania, takiego jak PHP, aby nawiązać połączenie z bazą danych i zapisywać wiadomości.
      // Oto przykład w języku PHP:
      <?php
      if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

        // Pobieranie wartości z parametrów POST
        $recipient = $_POST['recipient'];
        $message = $_POST['message'];

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
    }

    // Przypisanie funkcji do przycisku "Wyślij"
    var sendButton = document.getElementById("send-button");
    sendButton.addEventListener("click", sendMessage);

    // Obsługa wysyłania wiadomości po wciśnięciu Enter
    var messageInput = document.getElementById("message-input");
    messageInput.addEventListener("keypress", function (event) {
      if (event.keyCode === 13) {
        sendMessage();
      }
    });
  </script>
</body>
</html>
