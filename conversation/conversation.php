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

// Obsługa wysyłania wiadomości
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message'])) {
        $message = $_POST['message'];

        // Połączenie z bazą danych (dostosuj do swojej konfiguracji)
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "mes";
        
        $conn = new mysqli($servername, $username, $password, $database);
        
        // Sprawdzenie połączenia
        if ($conn->connect_error) {
            die("Błąd połączenia: " . $conn->connect_error);
        }
        
        // Przygotowanie i wykonanie zapytania wstawiającego do bazy danych
        $stmt = $conn->prepare("INSERT INTO messages (sender, recipient, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $recipient, $message);
        
        if ($stmt->execute()) {
            echo "Wiadomość została pomyślnie dodana do bazy danych.";
        } else {
            echo "Błąd podczas dodawania wiadomości do bazy danych: " . $stmt->error;
        }
        
        // Zamknięcie połączenia
        $stmt->close();
        $conn->close();
    }
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
    <a href="dashboard.php">Powrót</a>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        // Skrolowanie na dół
        function scrollToBottom() {
            var messageContainer = document.getElementById("message-container");
            messageContainer.scrollTop = messageContainer.scrollHeight;
        }

        // Obsługa formularza
        $(".message-form").submit(function(e) {
            e.preventDefault(); // Zapobieganie domyślnej akcji formularza
            sendMessage();
        });

        // Funkcja do wysyłania wiadomości
        function sendMessage() {
            var messageInput = document.getElementById("message-input");
            var messageContainer = document.getElementById("message-container");

            // Pobranie informacji o zalogowanym użytkowniku
            var sender = "<?php echo $username; ?>";

            // Pobranie treści wiadomości
            var message = messageInput.value;

            // Sprawdzenie, czy treść wiadomości nie jest pusta
            if (message.trim() === "") {
                alert("Wpisz wiadomość!");
                return;
            }

            // Tworzenie nowego elementu wiadomości
            var messageElement = document.createElement("div");
            messageElement.innerText = sender + ": " + message;
            messageElement.classList.add("message-sender");

            // Dodawanie wiadomości do kontenera na początku
            messageContainer.insertBefore(messageElement, messageContainer.firstChild);

            // Czyszczenie pola tekstowego po wysłaniu wiadomości
            messageInput.value = "";

            // Wywołanie funkcji do wysyłania wiadomości do serwera
            sendMessageToServer(sender, "<?php echo $recipient; ?>", message);
            scrollToBottom();
        }

        // Funkcja do wysyłania wiadomości do serwera
        function sendMessageToServer(sender, recipient, message) {
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

        // Funkcja do pobierania i wyświetlania wiadomości z serwera
        function fetchMessages() {
            var messageContainer = document.getElementById("message-container");

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
                    scrollToBottom();
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
