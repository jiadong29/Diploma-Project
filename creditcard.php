<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: Project.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_number = preg_replace('/\D/', '', $_POST['card_number']);
    $expiry_date = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];

        $stmt = $conn->prepare("INSERT INTO credit_cards (user_id, card_number, expiry_date, cvv) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $card_number, $expiry_date, $cvv);
        
        if ($stmt->execute()) {
        header("Location: user.php?success=1");
        exit();
        } else {
        $_SESSION['error'] = "Error saving credit card details: " . $conn->error;
        header("Location: creditcard.php?error=" . urlencode($conn->error));
        exit();
    }
        $stmt->close();
    }
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Credit Card Details</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #e6f2ff;
    }
    .card-container {
      width: 500px;
      background-color: #ffffff;
      margin: 200px auto;
      border: 1px solid #b3d1ff;
      border-radius: 5px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .card-header {
      background-color: #3399ff;
      color: white;
      padding: 10px;
      text-align: center;
      font-size: 18px;
      font-weight: bold;
      border-top-left-radius: 5px;
      border-top-right-radius: 5px;
    }
    .card-body {
      padding: 30px;
    }
    .card-body label {
      display: block;
      margin-bottom: 15px;
      font-weight: bold;
      color: #333;
    }
    .card-body input {
      width: 96%;
      padding: 8px;
      margin-bottom: 25px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .card-icons {
      float: right;
      margin-top: -25px;
    }
    .card-icons img {
      height: 25px;
      margin-left: 5px;
    }

    .submit-btn {
      background-color: orange;
      color: white;
      border: none;
      padding: 10px 20px;
      width: 100%;
      font-size: 16px;
      border-radius: 4px;
      cursor: pointer;
    }
    .submit-btn:hover {
      background-color: darkorange;
    }
    .message {
      text-align: center;
      margin: 15px 0;
      color: green;
    }
  </style>
</head>
<body>

<div class="card-container">
  <div class="card-header">
    Credit Card Details
  </div>
  <div class="card-body">
    <form method="POST">
      <label>Credit/Debit card Number:</label>
      <input type="text" name="card_number" maxlength="16" placeholder="1234 5678 9012 3456" required>
      <div class="card-icons">
        <img src="https://img.icons8.com/color/48/000000/mastercard-logo.png"/>
        <img src="https://img.icons8.com/color/48/000000/visa.png"/>
      </div>

      <label>Expiry Date:</label>
      <input type="date" name="expiry_date" maxlength="10" placeholder="dd-mm-yyyy" required>

      <label>CVV:</label>
      <input type="password" name="cvv" maxlength="3" placeholder="•••" required>
    
      <button type="submit" class="submit-btn">Save</button>
    </form>
  </div>
</div>

</body>
</html>
