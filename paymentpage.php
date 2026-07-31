<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    header("Location: login_register.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize variables for payment processing
$success = null;
$error = null;
$original_amount = null;

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $merchant_name = $_POST['merchant'] ?? '';
    $category = $_POST['category'] ?? '';
    $account_number = $_POST['account_number'] ?? '';
    $bill_month = $_POST['bill_month'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? '';

    // Validate required fields
    if (empty($merchant_name) || empty($account_number) || empty($bill_month) || empty($amount) || empty($payment_method)) {
        $error = "All fields are required.";
    } else {
        try {
            // Check if merchant exists, if not create it
            $merchant_query = "SELECT merchant_id FROM merchants WHERE merchant_name = ?";
            $stmt = $conn->prepare($merchant_query);
            $stmt->bind_param("s", $merchant_name);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $merchant = $result->fetch_assoc();
                $merchant_id = $merchant['merchant_id'];
            } else {
                // Create new merchant
                $insert_merchant = "INSERT INTO merchants (merchant_name, category) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_merchant);
                $stmt->bind_param("ss", $merchant_name, $category);
                $stmt->execute();
                $merchant_id = $conn->insert_id;
            }

            // Pay existing unpaid bill instead of inserting a duplicate
            $conn->begin_transaction();

            // Find matching unpaid bill
            $find_bill_sql = "SELECT bill_id, amount FROM bills WHERE user_id = ? AND merchant_id = ? AND month = ? AND status = 'unpaid' LIMIT 1";
            $stmt = $conn->prepare($find_bill_sql);
            $stmt->bind_param("iis", $user_id, $merchant_id, $bill_month);
            $stmt->execute();
            $bill_result = $stmt->get_result();

            if ($bill_result->num_rows === 0) {
                $conn->rollback();
                $error = "No unpaid bill found for the selected month. It may have already been paid.";
            } else {
                $bill = $bill_result->fetch_assoc();
                $bill_id = $bill['bill_id'];
                $original_amount = $bill['amount'];

                // Validate that the payment amount matches the bill amount exactly
                if (abs($amount - $original_amount) > 0.01) {
                    $conn->rollback();
                    $error = "Payment amount (RM " . number_format($amount, 2) . ") does not match the bill amount (RM " . number_format($original_amount, 2) . "). Please pay the exact amount.";
                } else {
                    // Mark as paid (keep original amount, don't update it)
                    $update_sql = "UPDATE bills SET status = 'paid', paid_date = NOW() WHERE bill_id = ?";
                    $stmt = $conn->prepare($update_sql);
                    $stmt->bind_param("i", $bill_id);

                    if ($stmt->execute()) {
                        $conn->commit();
                        $success = "Payment processed successfully!";
                    } else {
                        $conn->rollback();
                        $error = "Error processing payment: " . $conn->error;
                    }
                }
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get merchant and category from URL parameters (for form display)
$merchant = $_GET['merchant'] ?? '';
$category = $_GET['category'] ?? '';

// Validate merchant
$valid_merchants = [
    'utilities' => ['Air Perak', 'Air Selangor', 'Air Johor', 'Indah Water', 'TNB'],
    'postpaid' => ['Maxis', 'Celcom', 'Digi', 'U Mobile', 'Yes']
];

$is_valid_merchant = false;
if (isset($valid_merchants[$category]) && in_array($merchant, $valid_merchants[$category])) {
    $is_valid_merchant = true;
}

// Get merchant icon mapping
$merchant_icons = [
    'Air Perak' => 'Air Perak_Logo.png',
    'Air Selangor' => 'Air Selangor_icon.png',
    'Air Johor' => 'Air_Johor_logo.png',
    'Indah Water' => 'Indah Water_icon.png',
    'TNB' => 'Tnb_icon.png',
    'Maxis' => 'maxis_icon.png',
    'Celcom' => 'Celcom_icon.png',
    'Digi' => 'Digi_icon.png',
    'U Mobile' => 'U Mobile_icon.png',
    'Yes' => 'Yes_icon.png'
];

$merchant_icon = $merchant_icons[$merchant] ?? '';

// Fetch unpaid bills data for the current user
$bills_data = [];
$bills_query = "SELECT b.month, b.amount, m.merchant_name 
                FROM bills b 
                JOIN merchants m ON b.merchant_id = m.merchant_id 
                WHERE b.user_id = ? AND m.merchant_name = ? AND b.status = 'unpaid'
                ORDER BY b.month DESC";
$bills_stmt = $conn->prepare($bills_query);
$bills_stmt->bind_param("is", $user_id, $merchant);
$bills_stmt->execute();
$bills_result = $bills_stmt->get_result();

while ($row = $bills_result->fetch_assoc()) {
    $bills_data[] = $row;
}

// Fetch credit cards for the current user
$credit_cards = [];
$cards_query = "SELECT card_id, card_number, expiry_date 
                FROM credit_cards 
                WHERE user_id = ?";
$cards_stmt = $conn->prepare($cards_query);
$cards_stmt->bind_param("i", $user_id);
$cards_stmt->execute();
$cards_result = $cards_stmt->get_result();

while ($row = $cards_result->fetch_assoc()) {
    $credit_cards[] = $row;
}

// Get the latest unpaid bill data for pre-filling
$latest_bill = !empty($bills_data) ? $bills_data[0] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($success) ? 'Payment Processing' : 'Payment - ' . htmlspecialchars($merchant); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5faff;
            margin: 0;
            padding: 0;
        }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #2391ffff;
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }
        .header-title {
            font-size: 24px;
            color: #fff;
            margin: 0;
        }
        .back-btn {
            background: #222;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
        }
        .payment-card, .result-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .result-card {
            text-align: center;
        }
        .merchant-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .merchant-icon {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .merchant-info h2 {
            margin: 0;
            color: #333;
        }
        .merchant-info p {
            margin: 5px 0 0 0;
            color: #666;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #2391ff;
        }
        .submit-btn {
            background: #2391ff;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }
        .submit-btn:hover {
            background: #1a7ae6;
        }
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .amount-display {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        .amount-display h3 {
            margin: 0;
            color: #2391ff;
            font-size: 24px;
        }
        .payment-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: left;
        }
        .payment-details h3 {
            margin-top: 0;
            color: #333;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .detail-value {
            color: #333;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-primary {
            background: #2391ff;
            color: white;
        }
        .btn-secondary {
            background: #666;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

<div class="header-bar">
    <h1 class="header-title"><?php echo isset($success) ? 'Payment Processing' : 'Payment Page'; ?></h1>
    <a href="user.php" class="back-btn">Back to Dashboard</a>
</div>

<div class="container">
    <?php if (isset($success)): ?>
        <!-- Payment Success Result -->
        <div class="result-card">
            <div class="success-message">
                <h2>✅ Payment Successful!</h2>
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
            
            <div class="payment-details">
                <h3>Payment Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Merchant:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($merchant_name); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Account Number:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($account_number); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Bill Month:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($bill_month); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount:</span>
                    <span class="detail-value">RM <?php echo number_format($original_amount ?? $amount, 2); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value"><?php echo ucfirst(str_replace('_', ' ', $payment_method)); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Date:</span>
                    <span class="detail-value"><?php echo date('d/m/Y H:i:s'); ?></span>
                </div>
            </div>
            
            <a href="user.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
        
    <?php elseif (isset($error)): ?>
        <!-- Payment Error Result -->
        <div class="result-card">
            <div class="error-message">
                <h2>❌ Payment Failed</h2>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
            
            <a href="paymentpage.php?merchant=<?php echo urlencode($merchant_name); ?>&category=<?php echo urlencode($category); ?>" class="btn btn-primary">Try Again</a>
            <a href="user.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        
    <?php elseif ($is_valid_merchant): ?>
        <!-- Payment Form -->
        <div class="payment-card">
            <div class="merchant-header">
                <?php if ($merchant_icon): ?>
                    <img src="<?php echo htmlspecialchars($merchant_icon); ?>" alt="<?php echo htmlspecialchars($merchant); ?>" class="merchant-icon">
                <?php endif; ?>
                <div class="merchant-info">
                    <h2><?php echo htmlspecialchars($merchant); ?></h2>
                    <p><?php echo ucfirst($category); ?> Bill Payment</p>
                </div>
            </div>

            <?php if (empty($bills_data)): ?>
                <div class="error-message">
                    <h3>No Unpaid Bills Available</h3>
                    <p>You have no unpaid bills for <?php echo htmlspecialchars($merchant); ?> at this time.</p>
                    <a href="user.php" class="back-btn">Back to Dashboard</a>
                </div>
            <?php else: ?>
                <div class="amount-display">
                    <h3>Amount: RM <span id="amount">0.00</span></h3>
                </div>

                <form id="paymentForm" method="POST" action="paymentpage.php?merchant=<?php echo urlencode($merchant); ?>&category=<?php echo urlencode($category); ?>">
                <input type="hidden" name="merchant" value="<?php echo htmlspecialchars($merchant); ?>">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                
                <div class="form-group">
                    <label for="account_number">Account Number</label>
                    <input type="text" id="account_number" name="account_number" required>
                </div>

                <div class="form-group">
                    <label for="bill_month">Bill Month</label>
                    <select id="bill_month" name="bill_month" required>
                        <option value="">Select Bill Month</option>
                        <?php foreach ($bills_data as $bill): ?>
                            <option value="<?php echo htmlspecialchars($bill['month']); ?>" 
                                    data-amount="<?php echo htmlspecialchars($bill['amount']); ?>">
                                <?php echo date('F Y', strtotime($bill['month'])); ?> - RM <?php echo number_format($bill['amount'], 2); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount_input">Amount (RM)</label>
                    <input type="number" id="amount_input" name="amount" step="0.01" min="0" required 
                           value="<?php echo $latest_bill ? htmlspecialchars($latest_bill['amount']) : ''; ?>" readonly>
                    <small style="color: #666; font-size: 12px;">Amount is fixed based on your bill and cannot be modified</small>
                </div>

                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">Select Payment Method</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="e_wallet">E-Wallet</option>
                    </select>
                </div>

                <div class="form-group" id="credit_card_section" style="display: none;">
                    <label for="credit_card">Select Credit Card</label>
                    <select id="credit_card" name="credit_card_id">
                        <option value="">Select Credit Card</option>
                        <?php foreach ($credit_cards as $card): ?>
                            <option value="<?php echo htmlspecialchars($card['card_id']); ?>">
                                **** **** **** <?php echo substr($card['card_number'], -4); ?> 
                                (Expires: <?php echo date('d/m/Y', strtotime($card['expiry_date'])); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Proceed to Payment</button>
                </form>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Invalid Merchant -->
        <div class="payment-card">
            <div class="error-message">
                <h3>Invalid Merchant</h3>
                <p>The selected merchant is not valid. Please go back and select a valid biller.</p>
                <a href="user.php" class="back-btn">Back to Dashboard</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Handle bill month selection to auto-fill amount
document.getElementById('bill_month')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const amount = selectedOption.getAttribute('data-amount');
    if (amount) {
        document.getElementById('amount_input').value = amount;
        document.getElementById('amount').textContent = parseFloat(amount).toFixed(2);
    }
});

// Handle payment method selection to show/hide credit card section
document.getElementById('payment_method')?.addEventListener('change', function() {
    const creditCardSection = document.getElementById('credit_card_section');
    if (this.value === 'credit_card') {
        creditCardSection.style.display = 'block';
        document.getElementById('credit_card').required = true;
    } else {
        creditCardSection.style.display = 'none';
        document.getElementById('credit_card').required = false;
    }
});

// Initialize amount display with default value
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount_input');
    if (amountInput) {
        const amount = amountInput.value || '0.00';
        document.getElementById('amount').textContent = parseFloat(amount).toFixed(2);
    }
});
</script>

</body>
</html> 