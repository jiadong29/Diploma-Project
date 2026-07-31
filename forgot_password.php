<?php
session_start();
require_once 'config.php'; // Make sure to include your DB connection

// Step 1: Handle email submission
if (isset($_POST['verify_email'])) {
    $email = trim($_POST['email']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['reset_email'] = $email;
        $_SESSION['step'] = 'reset';
    } else {
        $error = "Email not found.";
    }
}

// Step 2: Handle password reset
if (isset($_POST['reset_password'])) {
    if ($_POST['password'] === $_POST['confirm_password']) {
        $email = $_SESSION['reset_email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $password, $email);
        $stmt->execute();
        session_destroy();
        
        header("Location: Project.php?reset=success");
        exit();
    } else {
        $error = "Passwords do not match.";
    }
}

// Determine which step to show
$step = isset($_SESSION['step']) && $_SESSION['step'] === 'reset' ? 'reset' : 'email';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="form-box active">
        <?php if (!empty($error)): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if ($step === 'email'): ?>
            <form action="" method="post">
                <h2>Forgot Password</h2>
                <input type="email" name="email" placeholder="Enter your email" required>
                <button type="submit" name="verify_email">Verify Email</button>
                <p><a href="Project.php">Back to Login</a></p>
            </form>

        <?php else: ?>
            <form action="" method="post">
                <h2>Reset Password</h2>
                <input type="password" name="password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" name="reset_password" onclick="return confirm('Are you sure you want to reset your password?')"> Reset Password</button>
                <p><a href="Project.php">Back to Login</a></p>
            </form>
            
        <?php endif; ?>
    </div>
</div>
</body>
</html>