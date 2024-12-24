<?php
// dashboard.php
session_start();
require_once 'db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$errors = [];
$success = "";

// Handle room creation via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_room'])) {
    $room_name = htmlspecialchars(trim($_POST['room_name']));
    if (empty($room_name)) {
        $errors[] = "Room name is required.";
    } else {
        // Generate a unique room link
        $room_link = 'meet_' . uniqid();
        $stmt = $conn->prepare("INSERT INTO meet_rooms (room_name, room_link, created_by) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $room_name, $room_link, $user_id);
        if ($stmt->execute()) {
            $success = "Room created successfully.";
        } else {
            $errors[] = "Failed to create room.";
        }
        $stmt->close();
    }
}

// Fetch user's rooms
$stmt = $conn->prepare("SELECT room_name, room_link, created_at FROM meet_rooms WHERE created_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($room_name, $room_link, $created_at);
$rooms = [];
while ($stmt->fetch()) {
    $rooms[] = ['room_name' => $room_name, 'room_link' => $room_link, 'created_at' => $created_at];
}
$stmt->close();

// Fetch user's profile image
$stmt = $conn->prepare("SELECT profile_image FROM meet_users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_image);
$stmt->fetch();
$stmt->close();

// If no profile image, use default
if (empty($profile_image)) {
    $profile_image = 'uploads/default.png';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - MeetNow</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons (Optional) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 80px;
            background: linear-gradient(180deg, #2b354f 0%, #1a1f2c 100%);
            width: 250px;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            z-index: 1020;
        }
        .sidebar a {
            color: #fff;
            padding: 15px 20px;
            display: block;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background-color: #495057;
            text-decoration: none;
        }
        .sidebar .profile {
            text-align: center;
            padding: 20px 0;
        }
        .sidebar .profile img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #007bff;
            margin-bottom: 10px;
        }
        .content {
            margin-left: 250px;
            padding: 80px 30px 30px 30px;
        }
        .card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            transform: translateY(-5px);
            transition: all 0.3s ease-in-out;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
        }
        .modal-header {
            background-color: #007bff;
            color: #fff;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .content {
                margin-left: 0;
                padding: 70px 20px 20px 20px;
            }
            .sidebar .profile img {
                width: 60px;
                height: 60px;
            }
        }
        .navbar {
            background: linear-gradient(45deg, #007bff, #0056b3) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1030;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">MeetNow</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout (<?php echo htmlspecialchars($username); ?>)</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar with Profile Image -->
    <div class="sidebar bg-dark">
        <div class="profile">
            <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image">
            <h5 class="text-white mt-2"><?php echo htmlspecialchars($username); ?></h5>
        </div>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
        <a href="profile.php"><i class="fas fa-user me-2"></i>Profile</a>
        <a href="#"><i class="fas fa-cog me-2"></i>Settings</a>
        <!-- Add more links as needed -->
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="container-fluid">
            <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>

            <!-- Success and Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Create Room Button -->
            <div class="mb-4">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoomModal">
                    <i class="fas fa-plus-circle me-2"></i>Create New Room
                </button>
            </div>

            <!-- Rooms List -->
            <div class="row">
                <?php if (count($rooms) > 0): ?>
                    <?php foreach ($rooms as $room): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($room['room_name']); ?></h5>
                                    <p class="card-text">Created on: <?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($room['created_at']))); ?></p>
                                    <a href="room.php?link=<?php echo htmlspecialchars($room['room_link']); ?>" class="btn btn-success mt-auto"><i class="fas fa-video me-2"></i>Join Room</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            You have not created any rooms yet. Click on "Create New Room" to get started!
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Create Room Modal -->
    <div class="modal fade" id="createRoomModal" tabindex="-1" aria-labelledby="createRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="dashboard.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createRoomModalLabel">Create New Meeting Room</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="room_name" class="form-label">Room Name</label>
                            <input type="text" class="form-control" id="room_name" name="room_name" required>
                        </div>
                        <!-- Add more fields if needed -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="create_room" class="btn btn-primary">Create Room</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Optional: Font Awesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
