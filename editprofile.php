<?php
session_start();
require_once 'config.php';

// Make sure the user is logged in
if (!isset($_SESSION['name'])) {
    header("Location: Project.php");
    exit();
}

if (isset($_SESSION['success_message'])) {
    echo "<script>alert('{$_SESSION['success_message']}');</script>";
    unset($_SESSION['success_message']);
}

// Get user information
$name = $_SESSION['name'];
$query = "SELECT u.*, c.card_number, c.expiry_date, c.cvv 
          FROM users u 
          LEFT JOIN credit_cards c ON u.user_id = c.user_id 
          WHERE u.name = ?";
$getuserid = $conn->prepare($query);
$getuserid->bind_param("s", $name);
$getuserid->execute();
$result = $getuserid->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update user information
    $updateUser = "UPDATE users SET name=?, email=?, phone_number=?, date_birth=?, address=?, state=?, country=? WHERE user_id=?";
    $stmtUser = $conn->prepare($updateUser);
    $stmtUser->bind_param("sssssssi", $_POST['name'], $_POST['email'], $_POST['phone_number'], $_POST['date_birth'], $_POST['address'], $_POST['state'], $_POST['country'], $user['user_id']);
    $stmtUser->execute();

    // Update or insert credit card information
    $checkCard = $conn->prepare("SELECT * FROM credit_cards WHERE user_id=?");
    $checkCard->bind_param("i", $user['user_id']);
    $checkCard->execute();
    $cardResult = $checkCard->get_result();

    if ($cardResult->num_rows > 0) {
        $updateCard = "UPDATE credit_cards SET card_number=?, expiry_date=?, cvv=? WHERE user_id=?";
        $stmtUser = $conn->prepare($updateCard);
        $stmtUser->bind_param("sssi", $_POST['card_number'], $_POST['expiry_date'], $_POST['cvv'], $user['user_id']);
        $stmtUser->execute();
        $_SESSION['success_message'] = "Profile updated successfully.";
    } else {
        $insertCard = "INSERT INTO credit_cards (user_id, card_number, expiry_date, cvv) VALUES (?, ?, ?, ?)";
        $stmtUser = $conn->prepare($insertCard);
        $stmtUser->bind_param("isss", $user['user_id'], $_POST['card_number'], $_POST['expiry_date'], $_POST['cvv']);
        $stmtUser->execute();
        $_SESSION['success_message'] = "Profile and credit card information saved successfully.";
    } 
    $stmtUser->close();
    header("Location: user.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Profile</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: linear-gradient(to right, #6a11cb, #00d4ff);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .form-container {
      background: white;
      border-radius: 30px;
      width: 60%;
      max-width: 800px;
      padding: 40px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .form-header {
      text-align: center;
      font-size: 24px;
      font-weight: bold;
      background-color: #ddd;
      padding: 15px;
      border-top-left-radius: 30px;
      border-top-right-radius: 30px;
      margin: -40px -40px 30px -40px;
    }

    form {
      display: flex;
      flex-wrap: wrap;
      gap: 40px;
    }

    .form-group {
      flex: 1 1 45%;
      display: flex;
      flex-direction: column;
    }

    .form-group.small {
      flex: 1 1 20%;
    }

    label {
      margin-bottom: 6px;
      font-weight: bold;
      color: #333;
    }

    input {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .submit-btn {
      margin-top:0px;
      padding: 12px 25px;
      font-size: 16px;
      background-color: #33bfff;
      color: white;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      align-self: flex-end;
    }

    .submit-btn:hover {
      background-color: #29a1db;
    }

    @media (max-width: 768px) {
      .form-group {
        flex: 1 1 100%;
      }
      .submit-btn {
        align-self: center;
      }
    }
  </style>
</head>
<body>

<div class="form-container">
  <div class="form-header">Edit Profile</div>
  <form action="" method="POST">
    <div class="form-group">
      <label for="name">NAME:</label>
      <input type="text" name="name" value="<?=htmlspecialchars($user['name'])?>" required>
    </div>
    <div class="form-group">
      <label for="email">EMAIL:</label>
      <input type="email" name="email" value="<?=htmlspecialchars($user['email'])?>" required>
    </div>
    <div class="form-group">
      <label for="phone">Phone number:</label>
      <input type="text" name="phone_number" value="<?=htmlspecialchars($user['phone_number'])?>" required>
    </div>
    <div class="form-group">
      <label for="dob">Date of Birth:</label>
      <input type="date" name="date_birth" value="<?=htmlspecialchars($user['date_birth'])?>" required>
    </div>
    <div class="form-group">
      <label for="address">Address:</label>
      <input type="text" name="address" value="<?=htmlspecialchars($user['address'])?>" required>
    </div>
    <div class="form-group">
      <label for="card_number">Credit card number:</label>
      <input type="text" name="card_number" value="<?=htmlspecialchars($user['card_number'])?>" required>
    </div>
    <div class="form-group">
      <label for="state">State:</label>
      <input type="text" name="state" value="<?=htmlspecialchars($user['state'])?>" required>
    </div>
    <div class="form-group">
      <label for="expiry_date">Expiry Date:</label>
      <input type="date" name="expiry_date" value="<?=htmlspecialchars($user['expiry_date'])?>" required>
    </div>
    <div class="form-group">
    <label for="country">Country:</label>
    <input type="text" name="country" value="<?=htmlspecialchars($user['country'])?>" required>
    </div>
    <div class="form-group small">
      <label for="cvv">CVV:</label>
      <input type="text" name="cvv" value="<?=htmlspecialchars($user['cvv'])?>" required>
    </div>
    <div class="form-group" style="flex: 1 1 100%; display: flex; justify-content: flex-end;">
      <button type="submit" class="submit-btn">SAVE</button>
    </div>
  </form>
</div>

</body>
</html>
