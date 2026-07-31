<?php
// init_db.php
$servername = "localhost";
$username = "root";
$password = "";

// 创建连接（无 db）
$conn = new mysqli($servername, $username, $password);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 创建数据库
$sql = "CREATE DATABASE IF NOT EXISTS Online_Bill_Payment_System";
if ($conn->query($sql) === TRUE) {
    echo "数据库创建成功<br>";
} else {
    echo "创建数据库出错: " . $conn->error;
}

// 选择数据库
$conn->select_db("Online_Bill_Payment_System");

// 创建 users 表
$sql = "
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    date_birth DATE,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone_number VARCHAR(20),
    address TEXT,
    state VARCHAR(100),
    country VARCHAR(100),
    role ENUM('user', 'admin') DEFAULT 'user'
)";
if ($conn->query($sql) === TRUE) {
    echo "用户表创建成功";
} else {
    echo "创建表出错: " . $conn->error;
}


// 创建 creditcards_db 表
$sql = "
CREATE TABLE IF NOT EXISTS credit_cards (
    card_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    card_number VARCHAR(20) NOT NULL,
    expiry_date DATE NOT NULL,
    cvv VARCHAR(4) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "信用卡表创建成功<br>";
} else {
    echo "创建信用卡表出错: " . $conn->error . "<br>";
}

// 创建 merchants 表
$sql = "
CREATE TABLE IF NOT EXISTS merchants (
    merchant_id INT AUTO_INCREMENT PRIMARY KEY,
    merchant_name VARCHAR(100) NOT NULL UNIQUE,
    category VARCHAR(50)
)";
if ($conn->query($sql) === TRUE) {
    echo "商家表创建成功<br>";
} else {
    echo "创建商家表出错: " . $conn->error . "<br>";
}

$sql = "
CREATE TABLE IF NOT EXISTS bills (
    bill_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    merchant_id INT NOT NULL,
    month DATE NOT NULL,
    amount DOUBLE NOT NULL,
    status ENUM('unpaid','paid') DEFAULT 'unpaid',
    due_date DATE NOT NULL,
    paid_date TIMESTAMP(3) NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (merchant_id) REFERENCES merchants(merchant_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "账单表创建成功<br>";
} else {
    echo "创建账单表出错: " . $conn->error . "<br>";
}

$conn->close();
?>