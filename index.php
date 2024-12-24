<?php
// index.php
session_start();

// If the user is already logged in, redirect to the dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome to MeetNow</title>
    <style>
        /* Inline CSS for simplicity */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #4facfe, #00f2fe);
            color: #333;
        }
        .header {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .container {
            padding: 50px 20px;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.9);
            min-height: 80vh;
        }
        .features {
            margin: 40px 0;
            text-align: left;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        .features h2 {
            color: #007bff;
        }
        .features ul {
            list-style-type: none;
            padding: 0;
        }
        .features li {
            background: #f9f9f9;
            margin: 10px 0;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
        }
        .features li img {
            width: 40px;
            margin-right: 15px;
        }
        .buttons {
            margin-top: 30px;
        }
        .buttons a {
            display: inline-block;
            margin: 10px;
            padding: 15px 30px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .buttons a:hover {
            background-color: #0056b3;
        }
        .footer {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            text-align: center;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
        @media (max-width: 768px) {
            .features li {
                flex-direction: column;
                align-items: flex-start;
            }
            .features li img {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to MeetNow</h1>
        <p>Your ultimate platform for seamless virtual meetings</p>
    </div>

    <div class="container">
        <div class="features">
            <h2>Our Features</h2>
            <ul>
                <li>
                    <img src="https://img.icons8.com/ios-filled/50/007bff/user-male-circle.png" alt="Authentication">
                    <div>
                        <h3>Secure Authentication</h3>
                        <p>Sign up and log in securely to access your personalized dashboard.</p>
                    </div>
                </li>
                <li>
                    <img src="https://img.icons8.com/ios-filled/50/007bff/meeting.png" alt="Meeting Rooms">
                    <div>
                        <h3>Manage Meeting Rooms</h3>
                        <p>Create and manage your own unique meeting rooms effortlessly.</p>
                    </div>
                </li>
                <li>
                    <img src="https://img.icons8.com/ios-filled/50/007bff/video-call.png" alt="Video Calls">
                    <div>
                        <h3>Video/Audio Conferencing</h3>
                        <p>Engage in high-quality video and audio calls with your team.</p>
                    </div>
                </li>
                <li>
                    <img src="https://img.icons8.com/ios-filled/50/007bff/chat.png" alt="Chat">
                    <div>
                        <h3>Real-Time Chat</h3>
                        <p>Communicate instantly with participants through our integrated chat system.</p>
                    </div>
                </li>
                <li>
                    <img src="https://img.icons8.com/ios-filled/50/007bff/screen-share.png" alt="Screen Sharing">
                    <div>
                        <h3>Screen Sharing</h3>
                        <p>Share your screen effortlessly during meetings for better collaboration.</p>
                    </div>
                </li>
            </ul>
        </div>

        <div class="buttons">
            <a href="signup.php">Sign Up</a>
            <a href="login.php">Log In</a>
        </div>
    </div>

    <div class="footer">
        &copy; <?php echo date("Y"); ?> MeetNow. All rights reserved.
    </div>
</body>
</html>
