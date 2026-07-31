<?php
session_start(); 
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: Project.php");
    exit();
}

$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''      
];
$activeForm = $_SESSION['active_form'] ?? 'login';

session_unset();

function showError($error){
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full-Stack Login & Register Form With User & Admin Page | Codehal</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
            <form action="login_register.php" method="post">
                <h2>Login</h2>
                <?php echo showError($errors['login']); ?>
                <input type="name" name="name" placeholder="User Name" required>
                <input type="password" name="password" placeholder="Password" required>
                <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
                <button type="submit" name="login">Login</button>
                <p>Don't have an account? <a href="#" onclick="showForm('Register-form')">Register</a></p> 
            </form>
        </div>

        <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="Register-form">
            <form action="login_register.php" method="post">
                <h2>Register</h2>
                <?php echo showError($errors['register']); ?>
                <input type="name" name="name" placeholder="Name" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="Cpassword" placeholder="Confirm Password" required>
                <input type="date" name="Date-of-Birth" placeholder="Date of Birth (yyyy-mm-dd)" required>
                <input type="Phone_number" name="Phone_number" placeholder="Phone Number (Please enter exactly 10 digits)" required maxlength="10" pattern="\d{10}">
                <input type="email" name="email" placeholder="Email ID" required>
                <input type="Address" name="Address" placeholder="Address" required>
                <input type="State" name="State" placeholder="State" required>
                <input type="Country" name="Country" placeholder="Country" required>
                <select name="role" required>
                    <option value="">--Select Role--</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit" name="register">Register</button>
                <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p> 

            </form>
        </div>

    <script src ="script.js"></script>

</body>
</html>  