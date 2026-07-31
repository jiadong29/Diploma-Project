<?php
$conn = new mysqli("localhost", "root", "", "online_bill_payment_system");
if ($conn->connect_error) die("DB Error");

if (isset($_GET['user_id'])) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id=?");
    $stmt->bind_param("i", $_GET['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (!$user) die("User not found.");
}

if (isset($_POST['update'])) {
    $stmt = $conn->prepare("UPDATE users SET name=?, date_birth=?, email=?, phone_number=?, address=?, state=?, country=?, role=? WHERE user_id=?");
    $stmt->bind_param("ssssssssi", $_POST['name'], $_POST['date_birth'], $_POST['email'], $_POST['phone_number'], $_POST['address'], $_POST['state'], $_POST['country'], $_POST['role'], $_POST['user_id']);
    $stmt->execute();
    header("Location: admin.php"); exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f4faff;
            padding: 40px;
        }
        .form-container {
            background-color: white;
            padding: 25px;
            width: 400px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        h2 {
            color: #1e90ff;
            text-align: center;
        }
        label {
            display: block;
            margin-top: 15px;
        }
        input, select {
            width: 100%;
            padding: 7px;
            margin-top: 5px;
        }
        .btn-save {
            background-color: #1e90ff;
            color: white;
            padding: 10px;
            border: none;
            margin-top: 20px;
            width: 100%;
            cursor: pointer;
        }
        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #888;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="form-container">
<h2>Edit User</h2>
<form method="POST">
<input type="hidden" name="user_id" value="<?=$user['user_id']?>">
<label>Name:</label>
<input type="text" name="name" value="<?=htmlspecialchars($user['name'])?>" required>
<label>Date of Birth:</label>
<input type="date" name="date_birth" value="<?=htmlspecialchars($user['date_birth'])?>" required>
<label>Email:</label>
<input type="email" name="email" value="<?=htmlspecialchars($user['email'])?>" required>
<label>Phone:</label>
<input type="text" name="phone_number" value="<?=htmlspecialchars($user['phone_number'])?>" required>
<label>Address:</label>
<input type="text" name="address" value="<?=htmlspecialchars($user['address'])?>" required>
<label>State:</label>
<input type="text" name="state" value="<?=htmlspecialchars($user['state'])?>" required>
<label>Country:</label>
<input type="text" name="country" value="<?=htmlspecialchars($user['country'])?>" required>
<label>Role:</label>
<select name="role" required>
    <option value="user" <?=$user['role']==='user'?'selected':''?>>User</option>
    <option value="admin" <?=$user['role']==='admin'?'selected':''?>>Admin</option>
</select>
<button type="submit" name="update" class="btn-save">Save Changes</button>
</form>
<a href="admin.php" class="btn-cancel">Cancel</a>
</div>
</body></html>
              
