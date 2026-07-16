<?php
/**
 * Email Configuration
 * NexMart E-Commerce
 */

// Email settings for sending order receipts
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'saikyawthihacs@gmail.com');
define('MAIL_PASSWORD', 'ivds akfx qish lntz'); // Add your Gmail App Password here
define('MAIL_FROM_EMAIL', 'saikyawthihacs@gmail.com');
define('MAIL_FROM_NAME', 'NexMart Store');
define('MAIL_ENCRYPTION', 'tls');

// Email sending function using PHPMailer
function sendOrderEmail($to, $orderData) {
    // Try to load Composer autoloader if present
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }

    // If PHPMailer class is not available after attempting to autoload, fallback to PHP mail()
    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        return sendOrderEmailFallback($to, $orderData);
    }

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;
        
        // Recipients
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($to, $orderData['customer_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Order Confirmation #' . $orderData['order_number'];
        $mail->Body = getOrderEmailTemplate($orderData);
        $mail->AltBody = getOrderEmailPlainText($orderData);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Fallback email function using PHP mail()
function sendOrderEmailFallback($to, $orderData) {
    $subject = 'Order Confirmation #' . $orderData['order_number'];
    $message = getOrderEmailTemplate($orderData);
    
    $headers = "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . MAIL_FROM_EMAIL . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Order email HTML template
function getOrderEmailTemplate($orderData) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Order Confirmation</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 0;
                background-color: #f5f5f5;
            }
            .email-container {
                max-width: 600px;
                margin: 20px auto;
                background: #ffffff;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            .email-header {
                background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
                color: #ffffff;
                padding: 30px 20px;
                text-align: center;
            }
            .email-header h1 {
                margin: 0;
                font-size: 28px;
                font-weight: 600;
            }
            .email-body {
                padding: 30px 20px;
            }
            .order-info {
                background: #f8fafc;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
            }
            .order-info h2 {
                margin: 0 0 15px 0;
                color: #1e293b;
                font-size: 18px;
            }
            .info-row {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                border-bottom: 1px solid #e2e8f0;
            }
            .info-row:last-child {
                border-bottom: none;
            }
            .info-label {
                font-weight: 600;
                color: #64748b;
            }
            .info-value {
                color: #1e293b;
            }
            .order-items {
                margin: 20px 0;
            }
            .order-items h3 {
                margin: 0 0 15px 0;
                color: #1e293b;
                font-size: 18px;
            }
            .item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 15px;
                border-bottom: 1px solid #e2e8f0;
                background: #ffffff;
            }
            .item:last-child {
                border-bottom: none;
            }
            .item-details {
                flex: 1;
            }
            .item-name {
                font-weight: 600;
                color: #1e293b;
                margin-bottom: 5px;
            }
            .item-qty {
                color: #64748b;
                font-size: 14px;
            }
            .item-price {
                font-weight: 600;
                color: #2563eb;
                font-size: 16px;
            }
            .order-total {
                background: #f1f5f9;
                padding: 20px;
                border-radius: 8px;
                margin-top: 20px;
            }
            .total-row {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                font-size: 16px;
            }
            .total-row.grand-total {
                border-top: 2px solid #cbd5e1;
                margin-top: 10px;
                padding-top: 15px;
                font-size: 20px;
                font-weight: 700;
                color: #1e293b;
            }
            .shipping-address {
                background: #f8fafc;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
            }
            .shipping-address h3 {
                margin: 0 0 15px 0;
                color: #1e293b;
                font-size: 18px;
            }
            .button {
                display: inline-block;
                background: #2563eb;
                color: #ffffff;
                padding: 12px 30px;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
                margin: 20px 0;
            }
            .email-footer {
                background: #f8fafc;
                padding: 20px;
                text-align: center;
                color: #64748b;
                font-size: 14px;
            }
            .email-footer p {
                margin: 5px 0;
            }
            @media only screen and (max-width: 600px) {
                .email-container {
                    margin: 0;
                    border-radius: 0;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h1>✓ Order Confirmed!</h1>
                <p style="margin: 10px 0 0 0; opacity: 0.9;">Thank you for your order</p>
            </div>
            
            <div class="email-body">
                <p>Hi <strong>' . htmlspecialchars($orderData['customer_name']) . '</strong>,</p>
                <p>Thank you for shopping with NexMart! Your order has been received and is being processed.</p>
                
                <div class="order-info">
                    <h2>Order Information</h2>
                    <div class="info-row">
                        <span class="info-label">Order Number:</span>
                        <span class="info-value"><strong>#' . htmlspecialchars($orderData['order_number']) . '</strong></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Order Date:</span>
                        <span class="info-value">' . date('M d, Y H:i A') . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value">' . ucfirst(str_replace('_', ' ', htmlspecialchars($orderData['payment_method']))) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value"><strong style="color: #f59e0b;">' . ucfirst(htmlspecialchars($orderData['status'])) . '</strong></span>
                    </div>
                </div>
                
                <div class="order-items">
                    <h3>Order Items</h3>
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
    ';
    
    // Add order items
    foreach ($orderData['items'] as $item) {
        $html .= '
                        <div class="item">
                            <div class="item-details">
                                <div class="item-name">' . htmlspecialchars($item['product_name']) . '</div>
                                <div class="item-qty">Quantity: ' . $item['quantity'] . ' × $' . number_format($item['price'], 2) . '</div>
                            </div>
                            <div class="item-price">$' . number_format($item['subtotal'], 2) . '</div>
                        </div>
        ';
    }
    
    $html .= '
                    </div>
                </div>
                
                <div class="order-total">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>$' . number_format($orderData['subtotal'], 2) . '</span>
                    </div>
                    <div class="total-row">
                        <span>Shipping:</span>
                        <span>$' . number_format($orderData['shipping_cost'], 2) . '</span>
                    </div>
                    <div class="total-row">
                        <span>Tax:</span>
                        <span>$' . number_format($orderData['tax'], 2) . '</span>
                    </div>
                    <div class="total-row grand-total">
                        <span>Total:</span>
                        <span>$' . number_format($orderData['total'], 2) . '</span>
                    </div>
                </div>
                
                <div class="shipping-address">
                    <h3>Shipping Address</h3>
                    <p style="margin: 0; line-height: 1.8;">
                        <strong>' . htmlspecialchars($orderData['shipping_name']) . '</strong><br>
                        ' . htmlspecialchars($orderData['shipping_address']) . '<br>
                        ' . htmlspecialchars($orderData['shipping_city']) . ', ' . htmlspecialchars($orderData['shipping_state']) . ' ' . htmlspecialchars($orderData['shipping_zip']) . '<br>
                        ' . htmlspecialchars($orderData['shipping_country']) . '<br>
                        Phone: ' . htmlspecialchars($orderData['shipping_phone']) . '
                    </p>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="' . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/orders.php" class="button">View Order Details</a>
                </div>
                
                <p style="margin-top: 30px; color: #64748b; font-size: 14px;">
                    We will send you a shipping confirmation email as soon as your order ships. 
                    If you have any questions about your order, please contact us at 
                    <a href="mailto:' . MAIL_FROM_EMAIL . '" style="color: #2563eb;">' . MAIL_FROM_EMAIL . '</a>
                </p>
            </div>
            
            <div class="email-footer">
                <p><strong>NexMart - Your Electronics Store</strong></p>
                <p>© ' . date('Y') . ' NexMart. All rights reserved.</p>
                <p style="margin-top: 15px; font-size: 12px;">
                    This is an automated email. Please do not reply to this message.
                </p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return $html;
}

// Plain text version of order email
function getOrderEmailPlainText($orderData) {
    $text = "ORDER CONFIRMATION\n\n";
    $text .= "Hi " . $orderData['customer_name'] . ",\n\n";
    $text .= "Thank you for shopping with NexMart! Your order has been received and is being processed.\n\n";
    $text .= "ORDER INFORMATION\n";
    $text .= "Order Number: #" . $orderData['order_number'] . "\n";
    $text .= "Order Date: " . date('M d, Y H:i A') . "\n";
    $text .= "Payment Method: " . ucfirst(str_replace('_', ' ', $orderData['payment_method'])) . "\n";
    $text .= "Status: " . ucfirst($orderData['status']) . "\n\n";
    
    $text .= "ORDER ITEMS\n";
    foreach ($orderData['items'] as $item) {
        $text .= "- " . $item['product_name'] . " (x" . $item['quantity'] . ") - $" . number_format($item['subtotal'], 2) . "\n";
    }
    
    $text .= "\nORDER TOTAL\n";
    $text .= "Subtotal: $" . number_format($orderData['subtotal'], 2) . "\n";
    $text .= "Shipping: $" . number_format($orderData['shipping_cost'], 2) . "\n";
    $text .= "Tax: $" . number_format($orderData['tax'], 2) . "\n";
    $text .= "Total: $" . number_format($orderData['total'], 2) . "\n\n";
    
    $text .= "SHIPPING ADDRESS\n";
    $text .= $orderData['shipping_name'] . "\n";
    $text .= $orderData['shipping_address'] . "\n";
    $text .= $orderData['shipping_city'] . ", " . $orderData['shipping_state'] . " " . $orderData['shipping_zip'] . "\n";
    $text .= $orderData['shipping_country'] . "\n";
    $text .= "Phone: " . $orderData['shipping_phone'] . "\n\n";
    
    $text .= "If you have any questions, please contact us at " . MAIL_FROM_EMAIL . "\n\n";
    $text .= "Thank you for shopping with NexMart!\n";
    
    return $text;
}

// Generic email sending function for password reset
function sendPasswordResetEmail($to, $name, $resetToken) {
    // Try to load Composer autoloader if present
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }

    // If PHPMailer class is not available, fallback to PHP mail()
    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        return sendPasswordResetEmailFallback($to, $name, $resetToken);
    }

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;
        
        // Recipients
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($to, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - NexMart';
        $mail->Body = getPasswordResetEmailTemplate($name, $resetToken);
        $mail->AltBody = getPasswordResetEmailPlainText($name, $resetToken);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Password reset email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Fallback for password reset email
function sendPasswordResetEmailFallback($to, $name, $resetToken) {
    $subject = 'Password Reset Request - NexMart';
    $message = getPasswordResetEmailTemplate($name, $resetToken);
    
    $headers = "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . MAIL_FROM_EMAIL . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Password reset email HTML template
function getPasswordResetEmailTemplate($name, $resetToken) {
    $resetLink = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/reset-password.php?token=' . $resetToken;
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: #fff; padding: 30px 20px; text-align: center; }
            .header h1 { margin: 0; font-size: 24px; }
            .body { padding: 30px 20px; }
            .button { display: inline-block; background: #2563eb; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: 600; margin: 20px 0; }
            .footer { background: #f8fafc; padding: 20px; text-align: center; color: #64748b; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🔐 Password Reset Request</h1>
            </div>
            <div class="body">
                <p>Hi <strong>' . htmlspecialchars($name) . '</strong>,</p>
                <p>We received a request to reset your password for your NexMart account.</p>
                <p>Click the button below to reset your password:</p>
                <div style="text-align: center;">
                    <a href="' . $resetLink . '" class="button">Reset Password</a>
                </div>
                <p style="color: #64748b; font-size: 14px; margin-top: 20px;">Or copy and paste this link into your browser:<br>
                <a href="' . $resetLink . '" style="color: #2563eb; word-break: break-all;">' . $resetLink . '</a></p>
                <p style="color: #ef4444; margin-top: 20px;"><strong>This link will expire in 1 hour.</strong></p>
                <p style="color: #64748b; font-size: 14px;">If you didn\'t request a password reset, please ignore this email or contact us if you have concerns.</p>
            </div>
            <div class="footer">
                <p><strong>NexMart - Your Electronics Store</strong></p>
                <p>© ' . date('Y') . ' NexMart. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return $html;
}

// Plain text version
function getPasswordResetEmailPlainText($name, $resetToken) {
    $resetLink = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/reset-password.php?token=' . $resetToken;
    
    $text = "PASSWORD RESET REQUEST\n\n";
    $text .= "Hi " . $name . ",\n\n";
    $text .= "We received a request to reset your password for your NexMart account.\n\n";
    $text .= "Click this link to reset your password:\n";
    $text .= $resetLink . "\n\n";
    $text .= "This link will expire in 1 hour.\n\n";
    $text .= "If you didn't request a password reset, please ignore this email.\n\n";
    $text .= "Thank you,\nNexMart Team\n";
    
    return $text;
}
?>
