<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Smark\Payment;

class PaymongoPaymentController extends Controller
{
    public function testPaymentPage()
    {
        return view('paymongo-payment.test-payment');
    }

    public function createPayment()
    {
        $secretKey = env('PAYMONGO_SECRET_KEY_TEST');
        $amount = $_POST['amount'] ?? 100; // example
        $description = "Premium Subscription";
        $remarks = "User #1234";

        $checkoutUrl = Payment::createPaymentLink($secretKey, $amount, $description, $remarks);

        if ($checkoutUrl) {
            header("Location: " . $checkoutUrl);
            exit();
        } else {
            echo "Error creating payment link. Check log for details.";
        }
    }

    public function callbackPayment()
    {
        $raw = file_get_contents("php://input");

        // Decode JSON
        $data = json_decode($raw, true);

        // Log raw data for debugging
        file_put_contents("webhook_raw.log", $raw . "\n", FILE_APPEND);

        if (!$data || !isset($data['data']['attributes']['type'])) {
            echo "❌ Invalid webhook payload";
            exit;
        }

        // Extract event type (like payment.paid)
        $eventType = $data['data']['attributes']['type'];

        if ($eventType === "payment.paid") {
            // ✅ Payment successful
            $payment = $data['data']['attributes']['data']['attributes'] ?? [];
            $amount = ($payment['amount'] ?? 0) / 100;
            $desc   = $payment['description'] ?? 'No description';

            // Example: Save to DB or log
            file_put_contents("payments.log", "✅ Payment success: ₱{$amount} - {$desc}\n", FILE_APPEND);
        } else {
            // Log other events
            file_put_contents("payments.log", "ℹ️ Event received: {$eventType}\n", FILE_APPEND);
        }

        http_response_code(200);
        echo "✅ Webhook processed";
    }
}
