<?php
session_start();
require_once 'config.php';

// Check if user is logged in and user_id > 0
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    header("Location: project.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user information
$user_query = "SELECT name FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userName = $user['name'] ?? "User";

// Get recent/saved bills (paid bills history)
$bills_query = "SELECT b.bill_id, COALESCE(m.merchant_name, 'Unknown Merchant') as merchant_name, b.month, b.amount, b.paid_date 
                FROM bills b 
                LEFT JOIN merchants m ON b.merchant_id = m.merchant_id 
                WHERE b.user_id = ? AND b.status = 'paid' 
                ORDER BY b.paid_date DESC 
                LIMIT 10";
$stmt = $conn->prepare($bills_query);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $bills_result = $stmt->get_result();
} else {
    // If query fails, create empty result
    $bills_result = null;
}

// Popular Billers categories
$utilities_billers = [
    ['name' => 'Air Perak', 'icon' => 'Air Perak_Logo.png'],
    ['name' => 'Air Selangor', 'icon' => 'Air Selangor_icon.png'],
    ['name' => 'Air Johor', 'icon' => 'Air_Johor_logo.png'],
    ['name' => 'Indah Water', 'icon' => 'Indah Water_icon.png'],
    ['name' => 'TNB', 'icon' => 'Tnb_icon.png']
];

$postpaid_billers = [
    ['name' => 'Maxis', 'icon' => 'maxis_icon.png'],
    ['name' => 'Celcom', 'icon' => 'Celcom_icon.png'],
    ['name' => 'Digi', 'icon' => 'Digi_icon.png'],
    ['name' => 'U Mobile', 'icon' => 'U Mobile_icon.png'],
    ['name' => 'Yes', 'icon' => 'Yes_icon.png']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles/user.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f5faff;
        margin: 0px;
        padding: 0px;
    }
    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #2391ffff;
        width: 100px;
        min-width: 98%;
        padding: 10px 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }
    .header-title {
        font-size: 35px;
        color: #fff;
        margin: 0;
    }
    .header-actions {
        display: flex;
        align-items: center;
        gap: 18px;
    }
    .header-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
    }
    .header-btn {
        background: #222;
        color: #fff;
        font-size: 20px;
        padding: 8px 22px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.2s;
        display: inline-block;
    }
    .header-btn:hover {
        background: #444;
    }

    .container {
        padding: 30px;
    }

    .content-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 40px;
        flex-wrap: wrap;
    }

    .left-panel {
        flex: 1;
        min-width: 350px;
    }

    .right-panel {
        width: 800px;
        height: 800px;
    }

    .right-image {
        width:800px;
        height: 600px;
        object-fit: cover;
        border-radius: 12px;
        
    }

    section {
        margin-bottom: 40px;
    }

    .saved-bills .bill-boxes {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .bill-box {
        background-color: #ffffff;
        border: 1px solid #dbe8ff;
        border-radius: 10px;
        padding: 15px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        position: relative;
        width: 200px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .bill-box h4 {
        margin: 0;
        color: #333;
        font-size: 14px;
    }
    .bill-box p {
        margin: 0;
        color: #666;
        font-size: 12px;
    }
    .bill-amount {
        font-weight: bold;
        color: #2391ff;
        font-size: 16px;
    }
    .bill-date {
        color: #999;
        font-size: 11px;
    }

    .category-section {
        margin-bottom: 25px;
    }
    .category-title {
        font-size: 18px;
        color: #333;
        margin-bottom: 15px;
        padding-bottom: 5px;
        border-bottom: 2px solid #2391ff;
    }

    .popular-billers .biller-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .biller-btn {
        background-color: #fff;
        border: 1px solid #dbe8ff;
        border-radius: 10px;
        padding: 15px;
        cursor: pointer;
        width: 80px;
        height: 80px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: #333;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .biller-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        border-color: #2391ff;
    }

    .biller-btn img {
        width: 80px;
        height: 80px;
        margin-bottom: 8px;
    }

    .biller-btn span {
        font-size: 11px;
        text-align: center;
        line-height: 1.2;
    }

    .view-more-btn {
        margin-top: 15px;
        background-color: #1e90ff;
        color: white;
        padding: 10px 18px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .no-bills {
        color: #666;
        font-style: italic;
        text-align: center;
        padding: 20px;
    }

    @media (max-width: 1000px) {
        .right-panel {
            width: 100%;
            height: auto;
        }
        .right-image {
            height: auto;
        }
    }
    </style>
</head>
<body>

<div class="header-bar">
    <h1 class="header-title">Online Bill Payment System</h1>
    <div class="header-actions">
        <img src="person.png" alt="Person" class="header-avatar">
        <a href="editprofile.php" class="header-btn">Edit Profile</a>
        <a href="Project.php?logout=1" class="header-btn">Logout</a>
    </div>
</div>

<div class="container">
    <div class="content-row">
        <div class="left-panel">
            <section class="saved-bills">
                <h2>Recent / Saved Bills</h2>
                <div class="bill-boxes">
                    <?php if ($bills_result && $bills_result->num_rows > 0): ?>
                        <?php while ($bill = $bills_result->fetch_assoc()): ?>
                            <div class="bill-box">
                                <h4><?php echo htmlspecialchars($bill['merchant_name']); ?></h4>
                                <p>Month: <?php echo date('M Y', strtotime($bill['month'])); ?></p>
                                <p class="bill-amount">RM <?php echo number_format($bill['amount'], 2); ?></p>
                                <p class="bill-date">Paid: <?php echo date('d/m/Y', strtotime($bill['paid_date'])); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-bills">No recent bills found</div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Popular Billers -->
            <section class="popular-billers">
                <h2>Popular Billers</h2>
                
                <!-- Utilities Category -->
                <div class="category-section">
                    <h3 class="category-title">Utilities</h3>
                    <div class="biller-grid">
                        <?php foreach ($utilities_billers as $biller): ?>
                            <a href="paymentpage.php?merchant=<?php echo urlencode($biller['name']); ?>&category=utilities" class="biller-btn">
                                <img src="<?php echo htmlspecialchars($biller['icon']); ?>" alt="<?php echo htmlspecialchars($biller['name']); ?>">
                                <span><?php echo htmlspecialchars($biller['name']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Postpaid Category -->
                <div class="category-section">
                    <h3 class="category-title">Postpaid</h3>
                    <div class="biller-grid">
                        <?php foreach ($postpaid_billers as $biller): ?>
                            <a href="paymentpage.php?merchant=<?php echo urlencode($biller['name']); ?>&category=postpaid" class="biller-btn">
                                <img src="<?php echo htmlspecialchars($biller['icon']); ?>" alt="<?php echo htmlspecialchars($biller['name']); ?>">
                                <span><?php echo htmlspecialchars($biller['name']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
            <img src="payment.png" alt="Dashboard Image" class="right-image">
        </div>
    </div>
</div>
</body>
</html>