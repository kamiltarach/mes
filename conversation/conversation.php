<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Pobranie informacji o zalogowanym użytkowniku
$username = $_SESSION['username'];

// Odczytanie wybranego rozmówcy z parametru GET
if (isset($_GET['recipient'])) {
    $recipient = $_GET['recipient'];
} else {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rozmowa z <?php echo $recipient; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="../logo.png">
</head>
<body>
    <h2>Rozmowa z <?php echo $recipient; ?></h2>

    <div id="message-container"></div>

    <form class="message-form" action="mes.php" method="POST">
      <input type="text" id="message-input" name="message" placeholder="Wpisz wiadomość">
      <button type="submit" id="send-button">Wyślij</button>
    </form>
    <a href="dashboard.php">Powrot</a>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        //skrolowanie na dół
    function scrollToBottom() {
      var messageContainer = document.getElementById("message-container");
      messageContainer.scrollTop = messageContainer.scrollHeight;
    }

    // Obsługa kliknięcia na hiperłącze rozmówcy
    $(".recipient-link").click(function(e) {
      e.preventDefault(); // Zapobieganie domyślnej akcji kliknięcia

      // Usunięcie zaznaczenia ze wszystkich hiperłączy
      $(".recipient-link").removeClass("selected");

      // Zaznaczenie wybranego rozmówcy
      $(this).addClass("selected");

      // Pobranie nazwy rozmówcy
      var recipient = $(this).data("recipient");

      // Aktualizacja nazwy rozmówcy
      updateRecipientName(recipient);
    });

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

    // Pobranie informacji o zalogowanym użytkowniku
    var sender = "<?php echo $username; ?>";

    // Tworzenie nowego elementu wiadomości
    var messageElement = document.createElement("div");
    messageElement.innerText = sender + ": " + messageInput.value;
    messageElement.classList.add("message-sender");

    // Dodawanie wiadomości do kontenera na początku
    messageContainer.insertBefore(messageElement, messageContainer.firstChild);

    // Czyszczenie pola tekstowego po wysłaniu wiadomości
    messageInput.value = "";

    // Wysłanie wiadomości do bazy danych
    sendMessageToDatabase(sender, recipient, messageElement.innerText);
    }

    // Funkcja do wysyłania wiadomości do bazy danych
    function sendMessageToDatabase(sender, recipient, message) {
    // Wykorzystaj AJAX, aby wysłać dane do skryptu PHP
    $.ajax({
        url: "mes.php",
        type: "POST",
        data: {
        sender: sender,
        recipient: recipient,
        message: message
        },
        success: function(response) {
        console.log(response);
        },
        error: function(xhr, status, error) {
        console.log("Błąd zapisu wiadomości: " + error);
        }
    });
    }

    // Obsługa kliknięcia na hiperłącze rozmówcy
    $(".recipient-link").click(function(e) {
      e.preventDefault(); // Zapobieganie domyślnej akcji kliknięcia

      // Usunięcie zaznaczenia ze wszystkich hiperłączy
      $(".recipient-link").removeClass("selected");

      // Zaznaczenie wybranego rozmówcy
      $(this).addClass("selected");

      // Zapisanie wybranego rozmówcy w atrybucie data-recipient kontenera
      var recipient = $(this).data("recipient");
      $("#recipient-container").attr("data-selected-recipient", recipient);
    });

    // Obsługa formularza
    $(".message-form").submit(function(e) {
      e.preventDefault(); // Zapobieganie domyślnej akcji formularza
      sendMessage();
    });

        // Funkcja do pobierania i wyświetlania wiadomości z bazy danych
    function fetchMessages() {
    var messageContainer = document.getElementById("message-container");

    // Pobranie informacji o zalogowanym użytkowniku
    var username = "<?php echo $username; ?>";

    // Wykorzystaj AJAX, aby pobrać wiadomości z serwera
    $.ajax({
        url: "fetch-messages.php", // Twój skrypt PHP do pobierania wiadomości
        type: "GET",
        success: function(response) {
        // Wyczyszczenie kontenera na wiadomości
        messageContainer.innerHTML = "";

        // Parsowanie odpowiedzi jako obiekt JSON
        var messages = JSON.parse(response);

        // Wyświetlanie wiadomości
        for (var i = 0; i < messages.length; i++) {
            var message = messages[i];

            // Tworzenie elementu wiadomości
            var messageElement = document.createElement("div");
            messageElement.innerText = message.sender + ": " + message.message;

            // Dodawanie odpowiedniego stylu CSS na podstawie nadawcy wiadomości
            if (message.sender === username) {
            messageElement.classList.add("message-sender");
            } else {
            messageElement.classList.add("message-recipient");
            }

            // Dodawanie wiadomości do kontenera
            messageContainer.appendChild(messageElement);
        }

        // Przewiń do ostatniej wiadomości
        messageContainer.scrollTop = messageContainer.scrollHeight;
        },
        error: function(xhr, status, error) {
        console.log("Błąd pobierania wiadomości: " + error);
        }
    });
    }
    // Wywołaj funkcję fetchMessages co 0,5 sekundy
    setInterval(fetchMessages, 500);
    </script>
</body>
</html>
