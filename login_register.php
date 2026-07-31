<?php 
session_start();
require_once 'config.php';

// Check if user is already logged in
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $dob = $_POST['Date-of-Birth'];
    $phone = $_POST['Phone_number'];
    $email = $_POST['email'];   
    $address = $_POST['Address'];
    $state = $_POST['State'];
    $country = $_POST['Country'];
    $role = $_POST['role'];

    // check if name and email already exist
    $checkName = $conn->prepare("SELECT name FROM users WHERE name = ?");
    $checkName->bind_param("s", $name);
    $checkName->execute();
    $checkName->store_result(); // 必须存储结果，否则下一次查询会报错
    if ($checkName->num_rows > 0) {
        $_SESSION['register_error'] = "Username already exists! Try adding a number at the end.";
        $_SESSION['active_form'] = 'register';
        header("Location: Project.php");
        exit();
    }
    $checkName->close();

    // check email
    $checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();
    if ($checkEmail->num_rows > 0) {
        $_SESSION['register_error'] = "Email already exists! Please try another one.";
        $_SESSION['active_form'] = 'register';
        header("Location: Project.php");
        exit();
    }
    $checkEmail->close();

    // insert user into database
    $insertUser = $conn->prepare("INSERT INTO users (name, password, date_birth, email, phone_number, address, state, country, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insertUser->bind_param("sssssssss", $name, $password, $dob, $email, $phone, $address, $state, $country, $role);
    $insertUser->execute();
    $insertUser->close();

    $_SESSION['register_success'] = "Registration successful, please log in.";
    header("Location: Project.php");
    exit();
}

// login logic
if (isset($_POST['login'])) {
    $name = $_POST['name'];
    $password = $_POST['password'];

    $checkName = $conn->prepare("SELECT * FROM users WHERE name = ?");
    $checkName->bind_param("s", $name);
    $checkName->execute();
    $result = $checkName->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['user_id'];

            if ($user['role'] == 'admin') {
                header("Location: admin.php");
                exit();
            }

            // check if user has credit cards
            $checkCard = $conn->prepare("SELECT card_id FROM credit_cards WHERE user_id = ?");
            $checkCard->bind_param("i", $user['user_id']);
            $checkCard->execute();
            $checkCard->store_result();
            
            if ($checkCard->num_rows > 0) {
                header("Location: user.php");
            } else {
                header("Location: creditcard.php");
            }
            $checkCard->close();
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid password!";
        }
    } else {
        $_SESSION['login_error'] = "Invalid username!";
    }

    $_SESSION['active_form'] = "login";
    header("Location: Project.php");
    exit();
}
?>
