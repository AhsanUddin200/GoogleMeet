<?php
// profile.php
session_start();
require_once 'db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email = "";
$profile_image = 'uploads/default.png'; // Default image

$errors = [];
$success = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if updating profile
    if (isset($_POST['update_profile'])) {
        // Sanitize inputs
        $new_email = htmlspecialchars(trim($_POST['email']));

        // Validate email
        if (empty($new_email)) {
            $errors[] = "Email is required.";
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        // Handle image upload if a file is provided
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB

            $file_type = mime_content_type($_FILES['profile_image']['tmp_name']);
            $file_size = $_FILES['profile_image']['size'];

            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "Only JPG, PNG, and GIF files are allowed.";
            }

            if ($file_size > $max_size) {
                $errors[] = "File size must be less than 2MB.";
            }

            if (empty($errors)) {
                // Generate a unique file name
                $file_ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
                $destination = 'uploads/' . $new_filename;

                // Move the uploaded file
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                    // Update the database with new image path
                    $stmt = $conn->prepare("UPDATE meet_users SET email = ?, profile_image = ? WHERE id = ?");
                    if ($stmt === false) {
                        $errors[] = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
                    } else {
                        $stmt->bind_param("ssi", $new_email, $destination, $user_id);
                        if ($stmt->execute()) {
                            // Optionally, delete the old image file if not default
                            $stmt_old = $conn->prepare("SELECT profile_image FROM meet_users WHERE id = ?");
                            if ($stmt_old) {
                                $stmt_old->bind_param("i", $user_id);
                                $stmt_old->execute();
                                $stmt_old->bind_result($old_profile_image);
                                if ($stmt_old->fetch()) {
                                    if ($old_profile_image != 'uploads/default.png' && file_exists($old_profile_image)) {
                                        unlink($old_profile_image);
                                    }
                                }
                                $stmt_old->close();
                            }
                            $success = "Profile updated successfully.";
                        } else {
                            $errors[] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
                        }
                        $stmt->close();
                    }
                } else {
                    $errors[] = "Failed to upload image.";
                }
            }
        } else {
            // Only update email
            if (empty($errors)) {
                $stmt = $conn->prepare("UPDATE meet_users SET email = ? WHERE id = ?");
                if ($stmt === false) {
                    $errors[] = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
                } else {
                    $stmt->bind_param("si", $new_email, $user_id);
                    if ($stmt->execute()) {
                        $success = "Profile updated successfully.";
                    } else {
                        $errors[] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }

        // Fetch updated user details
        $stmt = $conn->prepare("SELECT email, profile_image FROM meet_users WHERE id = ?");
        if ($stmt === false) {
            $errors[] = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        } else {
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $stmt->bind_result($email, $profile_image);
                $stmt->fetch();
            } else {
                $errors[] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch user details on initial load
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $conn->prepare("SELECT email, profile_image FROM meet_users WHERE id = ?");
    if ($stmt === false) {
        $errors[] = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    } else {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $stmt->bind_result($email, $profile_image);
            $stmt->fetch();
        } else {
            $errors[] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        $stmt->close();
    }

    // If no profile image, use default
    if (empty($profile_image)) {
        $profile_image = 'uploads/default.png';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - MeetNow</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons (Optional) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 60px;
            background-color: #343a40;
            width: 250px;
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
            padding: 20px;
        }
        .card {
            max-width: 600px;
            margin: 0 auto;
        }
        .modal-header {
            background-color: #007bff;
            color: #fff;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #007bff;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .content {
                margin-left: 0;
            }
            .sidebar .profile img {
                width: 60px;
                height: 60px;
            }
            .profile-img {
                width: 100px;
                height: 100px;
            }
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
            <h2 class="mb-4">Your Profile</h2>

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

            <div class="card">
                <div class="card-body text-center">
                    <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image" class="profile-img">
                    <form method="POST" action="profile.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="email" class="form-label"><i class="fas fa-envelope me-2"></i>Email address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="profile_image" class="form-label"><i class="fas fa-image me-2"></i>Profile Picture</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                            <div class="form-text">Allowed formats: JPG, PNG, GIF. Max size: 2MB.</div>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Optional: Font Awesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
