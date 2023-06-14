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
        $dbUsername = "root";
        $password = "";
        $database = "mes";

        $conn = new mysqli($servername, $dbUsername, $password, $database);

        // Sprawdzenie połączenia
        if ($conn->connect_error) {
            die("Błąd połączenia: " . $conn->connect_error);
        }

        // Przygotowanie i wykonanie zapytania wstawiającego do bazy danych
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, message) VALUES (?, ?, ?)");
        
        // Pobranie ID nadawcy i odbiorcy
        $senderID = getUserIdByUsername($conn, $username);
        $recipientID = getRecipientIdByLastName($conn, $recipient);

        $stmt->bind_param("iis", $senderID, $recipientID, $message);

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

// Funkcja do pobierania ID użytkownika na podstawie nazwy użytkownika
function getUserIdByUsername($conn, $username) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['id'];
}

// Funkcja do pobierania ID odbiorcy na podstawie nazwiska
function getRecipientIdByLastName($conn, $recipient) {
    $parts = explode(" ", $recipient);
    $lastname = $parts[count($parts) - 1];

    $stmt = $conn->prepare("SELECT recipient_id FROM recipient WHERE surname = ?");
    $stmt->bind_param("s", $lastname);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['recipient_id'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rozmowa z <?php echo $recipient; ?></title>
    <link rel="stylesheet" href="conversation.css">
    <link rel="icon" href="../logo.png">
</head>
<body>
    <h2>Rozmowa z <?php echo $recipient; ?></h2>
    <div id="message-container"></div>

    <form class="message-form" action="mes.php?recipient=<?php echo $recipient; ?>" method="POST">
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
                url: "mes.php?recipient=<?php echo $recipient; ?>",
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
                url: "fetch-messages.php?sender=<?php echo $username; ?>&recipient=<?php echo $recipient; ?>",
                type: "GET",
                success: function(response) {
                    try {
                        // Parsowanie odpowiedzi jako obiekt JSON
                        var messages = JSON.parse(response);

                        // Wyczyszczenie kontenera na wiadomości
                        messageContainer.innerHTML = "";

                        // Wyświetlanie wiadomości
                        for (var i = 0; i < messages.length; i++) {
                            var message = messages[i];

                            // Tworzenie elementu wiadomości
                            var messageElement = document.createElement("div");
                            messageElement.innerText = message.sender + ": " + message.message;

                            // Dodawanie odpowiedniego stylu CSS na podstawie nadawcy wiadomości
                            if (message.sender === "<?php echo $username; ?>") {
                                messageElement.classList.add("message-sender");
                            } else {
                                messageElement.classList.add("message-recipient");
                            }

                            // Dodawanie wiadomości do kontenera
                            messageContainer.appendChild(messageElement);
                        }

                        // Przewiń do ostatniej wiadomości
                        scrollToBottom();
                    } catch (error) {
                        console.log("Błąd parsowania odpowiedzi: " + error);
                        console.log(response);
                    }
                },
                error: function(xhr, status, error) {
                    console.log("Błąd pobierania wiadomości: " + error);
                }
            });
        }

        // Wywołaj funkcję fetchMessages co sekunde
        setInterval(fetchMessages, 1000);
    </script>
</body>
</html>
