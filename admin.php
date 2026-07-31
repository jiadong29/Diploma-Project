<?php
$conn = new mysqli("localhost", "root", "", "online_bill_payment_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

$roleFilter = $_GET['role'] ?? 'all';
// Prepare SQL query with role filter
$sql = "SELECT * FROM users";
$conditions = [];
if ($roleFilter !== 'all') $conditions[] = "role='$roleFilter'";

if ($conditions) $sql .= " WHERE " . implode(" AND ", $conditions);

$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
        }
        .sidebar {
            width: 270px;
            background-color: #f0f8ff;
            padding: 15px;
            height: 100vh;
        }
        .sidebar h2 {
            color: #1e90ff;
        }
        .content {
            flex: 1;
            padding: 10px;
            background-color: #ffffff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #dcdcdc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #e6f2ff;
        }
        .btn-edit {
            background-color: #1e90ff;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }
        .btn-delete {
            background-color: #ff4d4d;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }
        select {
            width: 100%;
            margin-bottom: 20px;
            padding: 10px;
        }

        .button1, .logout_button {
        border: none;
        color: white;
        padding: 6px 40px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 20px;
        margin: 20px 50px;
        transition-duration: 0.4s;
        cursor: pointer;
        }

        .button1 {
        background-color: #04AA6D; 
        color: white; 
        border: 2px solid #04AA6D;
        }

        .button1:hover {
        background-color:rgb(1, 149, 97);
        color: white;
        }

    </style>
</head>
<body>

<div class="sidebar">
<h2>Welcome Admin</h2>
<form method="GET">
<label for>Role:</label>
<select name="role" user_id="role">
    <option value="all">All</option>
    <option value="user" <?= $roleFilter === 'user' ? 'selected' : '' ?>>User</option>
    <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
</select>

<button type="submit">Filter</button>
</form>
<a href="Project.php?logout=1" class="logout_button button1">Logout</a>
</div>

<div class="content">
<h1>User Management</h1>
<table>
<tr>
    <th>Name</th>
    <th>Date of Birth</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Address</th>
    <th>State</th>
    <th>Country</th>
    <th>Role</th>
    <th>Actions</th>
</tr>
<?php while($row=$result->fetch_assoc()): ?>
<tr>
<td><?=htmlspecialchars($row['name'])?></td>
<td><?=htmlspecialchars($row['date_birth'])?></td>
<td><?=htmlspecialchars($row['email'])?></td>
<td><?=htmlspecialchars($row['phone_number'])?></td>
<td><?=htmlspecialchars($row['address'])?></td>
<td><?=htmlspecialchars($row['state'])?></td>
<td><?=htmlspecialchars($row['country'])?></td>
<td><?=htmlspecialchars($row['role'])?></td>
<td>
<a href="edit_user.php?user_id=<?=$row['user_id']?>"><button class="btn-edit">Edit</button></a>
<a href="?delete=<?=$row['user_id']?>" onclick="return confirm('Delete this user?')"><button class="btn-delete">Delete</button></a>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>

</body>
</html>  
