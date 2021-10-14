<?php
/*
 * Example 15 - How to create an on-demand recurring payment.
 */

try {
    /*
     * Initialize the Mollie API library with your API key or OAuth access token.
     */
    require "initialize.php";

    /*
     * Retrieve the last created customer for this example.
     * If no customers are created yet, run example 11.
     */
    $customer = $mollie->customers->page(null, 1)[0];
    
    /*
     * Generate a unique order id for this example.
     */
    $orderId = time();

    /*
     * Determine the url parts to these example files.
     */
    $protocol = isset($_SERVER['HTTPS']) && strcasecmp('off', $_SERVER['HTTPS']) !== 0 ? "https" : "http";
    $hostname = $_SERVER['HTTP_HOST'];
    $path = dirname(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF']);
    
    /*
     * Customer Payment creation parameters.
     *
     * See: https://docs.mollie.com/reference/v2/customers-api/create-customer-payment
     */
    $payment = $customer->createPayment([
        "amount" => [
            "value" => "10.00", // You must send the correct number of decimals, thus we enforce the use of strings
            "currency" => "EUR"
        ],
        "description" => "On-demand payment - Order #{$orderId}",
        "webhookUrl" => "{$protocol}://{$hostname}{$path}/02-webhook-verification.php",
        "metadata" => [
            "order_id" => $orderId,
        ],

        // Flag this payment as a recurring payment.
        "sequenceType" => \Mollie\Api\Types\SequenceType::SEQUENCETYPE_RECURRING,
    ]);
    
    /*
     * In this example we store the order with its payment status in a database.
     */
    database_write($orderId, $payment->status);

    /*
     * The payment will be either pending or paid immediately. The customer
     * does not have to perform any payment steps.
     */
    echo "<p>Selected mandate is '" . htmlspecialchars($payment->mandateId) . "' (" . htmlspecialchars($payment->method) . ").</p>\n";
    echo "<p>The payment status is '" . htmlspecialchars($payment->status) . "'.</p>\n";
} catch (\Mollie\Api\Exceptions\ApiException $e) {
    echo "API call failed: " . htmlspecialchars($e->getMessage());
}

/*
 * NOTE: This example uses a text file as a database. Please use a real database like MySQL in production code.
 */
function database_write($orderId, $status)
{
    $orderId = intval($orderId);
    $database = dirname(__FILE__) . "/orders/order-{$orderId}.txt";
    file_put_contents($database, $status);
}
