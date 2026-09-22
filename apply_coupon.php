<?php
include 'config/db.php';

header('Content-Type: application/json');

$code = strtoupper(trim($_GET['code'] ?? ''));
$total_price = floatval($_GET['total_price'] ?? 0);

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a promo code.']);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active'");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($coupon = $result->fetch_assoc()) {
    if ($total_price < $coupon['min_amount']) {
        echo json_encode([
            'success' => false, 
            'message' => 'Minimum booking amount of ₹' . number_format($coupon['min_amount'], 2) . ' required for this code.'
        ]);
        exit();
    }

    $discount = 0;
    if ($coupon['discount_type'] === 'percentage') {
        $discount = ($total_price * $coupon['discount_value']) / 100;
    } else {
        $discount = $coupon['discount_value'];
    }

    $final_price = max(0, $total_price - $discount);

    echo json_encode([
        'success' => true,
        'message' => 'Promo code applied successfully!',
        'discount' => $discount,
        'final_price' => $final_price
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired promo code.']);
}