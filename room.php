<?php
// room.php
session_start();
require_once 'db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get room link from URL
if (!isset($_GET['link'])) {
    header("Location: dashboard.php");
    exit();
}

$room_link = htmlspecialchars(trim($_GET['link']));

// Fetch room details
$stmt = $conn->prepare("SELECT id, room_name FROM meet_rooms WHERE room_link = ?");
$stmt->bind_param("s", $room_link);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows == 0) {
    echo "Room not found.";
    exit();
}
$stmt->bind_result($room_id, $room_name);
$stmt->fetch();
$stmt->close();

// Handle chat submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $message = htmlspecialchars(trim($_POST['message']));
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO meet_messages (room_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $room_id, $user_id, $message);
        $stmt->execute();
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($room_name); ?> - Meeting Room</title>
    <style>
        /* Modern design update */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 20px;
        }
        .container { 
            width: 95%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 25px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            display: flex;
            gap: 25px;
        }
        .video-section { 
            width: 70%;
        }
        .chat-section { 
            width: 30%;
            border-left: 2px solid #eef2f7;
            padding-left: 25px;
        }
        h2 { 
            text-align: center;
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
        }
        h3 {
            color: #2c3e50;
            font-size: 20px;
            margin-bottom: 15px;
        }
        iframe { 
            width: 100%;
            height: 550px;
            border: 0;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .chat-box { 
            height: 450px;
            overflow-y: auto;
            border: 1px solid #e1e8ed;
            border-radius: 12px;
            padding: 15px;
            background: #f8fafc;
            margin-bottom: 15px;
        }
        .chat-message { 
            margin-bottom: 12px;
            padding: 8px 12px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .chat-message strong { 
            color: #3498db;
            font-weight: 600;
        }
        form { 
            display: flex;
            gap: 10px;
        }
        input[type=text] { 
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        input[type=text]:focus {
            outline: none;
            border-color: #3498db;
        }
        .back-button {
        position: absolute;
        top: 30px;
        left: 30px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #ffffff;
        border-radius: 8px;
        color: #2c3e50;
        text-decoration: none;
        font-weight: 500;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .back-button:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .back-button svg {
        width: 20px;
        height: 20px;
    }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .video-section, .chat-section {
                width: 100%;
            }
            .chat-section {
                border-left: none;
                border-top: 2px solid #eef2f7;
                padding-left: 0;
                padding-top: 20px;
                margin-top: 20px;
            }
        }
    </style>
    <script>
        // Simple AJAX for chat
        function loadMessages() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_messages.php?room_id=<?php echo $room_id; ?>', true);
            xhr.onload = function() {
                if (this.status == 200) {
                    document.getElementById('chat-box').innerHTML = this.responseText;
                    // Scroll to the bottom
                    var chatBox = document.getElementById('chat-box');
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            };
            xhr.send();
        }

        // Refresh messages every 3 seconds
        setInterval(loadMessages, 3000);
        window.onload = loadMessages;
    </script>
</head>
<body>
    <div class="container">
        <div class="video-section">
            <h2><?php echo htmlspecialchars($room_name); ?></h2>
            <!-- Embed Jitsi Meet -->
            <iframe
                src="https://meet.jit.si/<?php echo urlencode($room_link); ?>?autoplay=true"
                allow="camera; microphone; fullscreen; display-capture"
            ></iframe>
        </div>
        <div class="chat-section">
            <h3>Chat</h3>
            <div id="chat-box" class="chat-box">
                <!-- Messages will be loaded here -->
            </div>
            <form method="POST" action="">
                <input type="text" name="message" placeholder="Type your message..." required>
                <button type="submit" name="send_message">Send</button>
            </form>
        </div>
    </div>
    <a href="dashboard.php" class="back-button">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M19 12H5M12 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Back to Dashboard
        </a>

</body>
</html>
