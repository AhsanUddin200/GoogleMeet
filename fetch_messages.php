<?php
// fetch_messages.php
session_start();
require_once 'db.php';

// Check if room_id is set
if (!isset($_GET['room_id'])) {
    exit(); 
}

$room_id = intval($_GET['room_id']);

// Fetch messages
$stmt = $conn->prepare("SELECT meet_users.username, meet_messages.message, meet_messages.sent_at 
                        FROM meet_messages 
                        JOIN meet_users ON meet_messages.user_id = meet_users.id 
                        WHERE meet_messages.room_id = ? 
                        ORDER BY meet_messages.sent_at ASC");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$stmt->bind_result($username, $message, $sent_at);

while ($stmt->fetch()) {
    echo "<div class='chat-message'><strong>" . htmlspecialchars($username) . ":</strong> " . htmlspecialchars($message) . " <span style='font-size: 0.8em; color: #888;'>(" . $sent_at . ")</span></div>";
}

$stmt->close();
?>
