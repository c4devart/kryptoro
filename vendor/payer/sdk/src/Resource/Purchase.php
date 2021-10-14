<?php namespace Payer\Sdk\Resource;
/**
 * Copyright 2016 Payer Financial Services AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * PHP version 5.3
 *
 * @package   Payer_Sdk
 * @author    Payer <teknik@payer.se>
 * @copyright 2016 Payer Financial Services AB
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache license v2.0
 */

use Payer\Sdk\PayerGatewayInterface;
use Payer\Sdk\Format\Purchase as PurchaseFormatter;
use Payer\Sdk\Exception\InvalidRequestException;
use Payer\Sdk\Exception\WebserviceException;
use Payer\Sdk\Transport\Http\Response;
use Payer\Sdk\Webservice\PayerPostInterface;

class Purchase extends PayerResource
{

    /**
     * Purchase object formatter
     *
     * @var DataFormatter
     *
     */
    private $_formatter;

    public function __construct(PayerGatewayInterface $gateway)
    {
        $this->gateway = $gateway;

        $this->_formatter = new PurchaseFormatter;
    }

    /**
     * Used to confirm a the callback request
     *
     * @return void
     *
     */
    public function acceptCallbackRequest()
    {
        die("TRUE");
    }

    /**
     * Initiate a new payment session through the payment dialogue
     *
     * @param array $input The create purchase object
     * @return bool Returns true if the payment was successfully initiated
     * @throws InvalidRequestException
     *
     */
    public function create(array $input)
    {
        $input = $this->_formatter->filterCreatePurchase($input);

        $payment = $input['payment'];
        $purchase = $input['purchase'];

        $post = $this->gateway->getPostService();

        $this->_setOrderDetails($post, $purchase);
        $this->_setOrderItems($post, $purchase['items']);
        $this->_setCustomerDetails($post, $purchase['customer']);
        $this->_setPaymentOptions($post, $payment);

        ob_start();

        ?>
        <!DOCTYPE html>
        <head>
            <script type="text/javascript">
                function sendform(){
                    var frm = document.getElementById("order_form");
                    frm.submit();
                }
                window.onload = function() {
                    sendform();
                }
            </script>
        </head>
        <body>
        <form id="order_form" 	 name="order_form"         	action="<?= $post->get_server_url() ?>" method="post">
            <input type="hidden" name="payer_agentid" 		value="<?= $post->get_agentid() ?>" />
            <input type="hidden" name="payer_xml_writer" 	value="<?= $post->get_api_version() ?>" />
            <input type="hidden" name="payer_data" 			value="<?= $post->get_xml_data() ?>" />
            <input type="hidden" name="payer_checksum" 		value="<?= $post->get_checksum() ?>" />
            <input type="hidden" name="payer_charset" 		value="<?= $post->get_charset() ?>" />
        </form>
        </body>
        </html>
        <?php

        ob_end_flush();

        return true;
    }

    /**
     * Creates a authorize callback response resource
     *
     * @param array $input Voluntary options
     * @return void
     *
     */
    public function createAuthorizeResource(array $input = array())
    {
        $this->validateCallbackRequest($input);
        $this->acceptCallbackRequest();
    }

    /**
     * Creates a settlement callback response resource
     *
     * @param array $input Voluntary options
     * @return void
     *
     */
    public function createSettlementResource(array $input = array())
    {
        $this->validateCallbackRequest($input);
        $this->acceptCallbackRequest();
    }

    /**
     * Carries an one-time recurring charge
     *
     * @param array $input The debit object
     * @return string The transaction id as json
     * @throws InvalidRequestException
     * @throws WebserviceException
     *
     */
    public function debit(array $input)
    {
        $input = $this->_formatter->filterDebit($input);

        $recurringToken = $input['recurring_token'];
        if (empty($recurringToken)) {
            throw new InvalidRequestException("Missing argument: 'recurring_token'");
        }

        $amount = $input['amount'];
        if (empty($amount)) {
            throw new InvalidRequestException("Missing argument: 'amount'");
        }

        $currency = $input['currency'];
        if (empty($currency)) {
            throw new InvalidRequestException("Missing argument: 'currency'");
        }

        $description = $input['description'];
        if (empty($description)) {
            throw new InvalidRequestException("Missing argument: 'description'");
        }

        $referenceId = $input['reference_id'];
        $vatPercentage = $input['vat_percentage'];

        $soap = $this->gateway->getSoapService();

        $soap->start();
        $transactionId = $soap->debitByReference(
            $recurringToken,
            $referenceId,
            (double) $amount,
            $currency,
            $description,
            doubleval($vatPercentage)
        );
        $soap->close();

        return array(
            'transaction_id' => $transactionId
        );
    }

    /**
     * Returns the raw data used in the post request to initiate
     * a payment session through the payment dialogue
     *
     * @param array $input The create purchase object
     * @return array The raw data of the post request
     * @throws InvalidRequestException
     *
     */
    public function getPostData(array $input)
    {
	    	$input = $this->_formatter->filterCreatePurchase($input);

	    	$payment = $input['payment'];
	    	$purchase = $input['purchase'];

	    	$post = $this->gateway->getPostService();

	    	$this->_setOrderDetails($post, $purchase);
	    	$this->_setOrderItems($post, $purchase['items']);
	    	$this->_setCustomerDetails($post, $purchase['customer']);
	    	$this->_setPaymentOptions($post, $payment);

   		return array(
   			'server_url' => $post->get_server_url(),
   			'agent_id' => $post->get_agentid(),
   			'api_version' => $post->get_api_version(),
   			'xml_data' => $post->get_xml_data(),
   			'checksum' => $post->get_checksum(),
   			'charset' => $post->get_charset()
   		);
     }

    /**
     * Refunds an invoice with the defined amount
     *
     * @param array $input The refund request object
     * @return string The transaction id of the refund
     * @throws InvalidRequestException
     * @throws WebserviceException
     *
     */
    public function refund(array $input)
    {
        $input = $this->_formatter->filterRefund($input);

        $transactionId = $input['transaction_id'];
        if (empty($transactionId)) {
            throw new InvalidRequestException("Missing argument: 'transaction_id'");
        }

        $reason = $input['reason'];
        if (empty($reason)) {
            throw new InvalidRequestException("Missing argument: 'reason'");
        }

        $amount = $input['amount'];
        if (empty($amount)) {
            throw new InvalidRequestException("Missing argument: 'amount'");
        }

        $vat_percentage = $input['vat_percentage'];
        if (empty($vat_percentage)) {
            throw new InvalidRequestException("Missing argument: 'vat_percentage'");
        }

        $soap = $this->gateway->getSoapService();

        $soap->start();
        $refundId = $soap->simpleRefund(
            $transactionId,
            $reason,
            doubleval($amount),
            intval($vat_percentage)
        );
        $soap->close();

        return array(
            'transaction_id' => $refundId
        );
    }

    /**
     * Performs a settlement on a pending payment
     *
     * @param array $input The settlement request object
     * @return string The transaction id of the refund
     * @throws InvalidRequestException
     * @throws WebserviceException
     *
     */
    public function settlement(array $input)
    {
        $input = $this->_formatter->filterSettlement($input);

        $settlementId = $input['settlement_id'];
        if (empty($settlementId)) {
            throw new InvalidRequestException("Missing argument: 'settlement_id'");
        }

        $amount = $input['amount'];
        if (empty($amount)) {
            throw new InvalidRequestException("Missing argument: 'amount'");
        }

        $soap = $this->gateway->getSoapService();

        $soap->start();
        $transactionId = $soap->settlement($settlementId, $amount);
        $soap->close();

        return array(
            'transaction_id' => $transactionId
        );
    }

    /**
     * Carries the validation of the callback request source
     *
     * @param array $input Voluntary  options
     *
     */
    public function validateCallbackRequest(array $input = array())
    {
        $input = $this->_formatter->filterCallbackRequest($input);

        $post = $this->gateway->getPostService();

        // Extended layer between the shop and Payer e.g. a proxy has to
        // be added as a valid ip address in the api firewall
        $proxy = $input['proxy'];
        if (!empty($proxy)) {
            foreach ($proxy as $address) {
                $post->add_valid_ip($address);
            }
        }

        if(!$post->is_valid_ip()) {
            die("FALSE - INVALID IP " . $_SERVER['REMOTE_ADDR'] . "\n");
        }
        if(!$post->is_valid_callback()) {
            die("FALSE - INVALID CALLBACK REQUEST");
        }
    }

    /**
     * Adds the customer details to the purchase session
     *
     * @param PayerPostInterface $post Post webservice
     * @param array $customer The customer object
     *
     */
    private function _setCustomerDetails(
        PayerPostInterface $post,
        array $customer
    ) {
        $post->add_buyer_info (
            $customer['first_name'],
            $customer['last_name'],
            ( !empty( $customer['address']['co'] ) ? 'c/o ' . $customer['address']['co'] : $customer['address']['address_1'] ),
            ( !empty( $customer['address']['co'] ) ? $customer['address']['address_1'] : $customer['address']['address_2'] ),
            $customer['zip_code'],
            $customer['city'],
            $customer['country_code'],
            $customer['phone']['home'],
            $customer['phone']['work'],
            $customer['phone']['mobile'],
            $customer['email'],
            $customer['organisation'],
            $customer['identity_number'],
            $customer['id'],
            $customer['your_reference']
        );
    }

    /**
     * Adds the order items to the purchase session
     *
     * @param PayerPostInterface $post Post webservice
     * @param array $items The order items object
     *
     */
    private function _setOrderItems(
        PayerPostInterface $post,
        array $items
    ) {
        foreach ($items as $item) {
            if ($item['type'] == 'FEE') {
                $post->set_fee(
                    $item['description'],
                    $item['unit_price'],
                    $item['article_number'],
                    $item['unit_vat_percentage'],
                    $item['quantity']
                );
            }

            if ($item['type'] == 'INFOLINE') {
                $post->add_info_line(
                    $item['line_number'],
                    $item['description']
                );
            }

            if ($item['type'] == 'FREEFORM') {
                $post->add_freeform_purchase_ex(
                    $item['line_number'],
                    $item['description'],
                    $item['article_number'],
                    $item['unit_price'],
                    $item['unit_vat_percentage'],
                    $item['quantity'],
                    $item['unit'],
                    $item['account'],
                    $item['dist_agent_id']
                );
            }
        }
    }

    /**
     * Adds the order details to the purchase session
     *
     * @param PayerPostInterface $post Post webservice
     * @param array $order The order details object
     *
     */
    private function _setOrderDetails(
        PayerPostInterface $post,
        array $order
    ) {
        if (!empty($order['charset'])) {
            $post->setCharSet($order['charset']);
        }

        if (!empty($order['currency'])) {
            $post->set_currency($order['currency']);
        }

        if (!empty($order['description'])) {
            $post->set_description($order['description']);
        }

        if (!empty($order['reference_id'])) {
            $post->set_reference_id($order['reference_id']);
        }

        $post->set_debug_mode('silent');
        $post->set_test_mode($order['test_mode']);

        if ($order['test_mode'] == true) {
            $post->set_debug_mode('verbose');
        }
    }

    /**
     * Adds the payment option values to purchase session
     *
     * @param PayerPostInterface $post Post webservice
     * @param array $payment The payment object
     * @throws InvalidRequestException
     *
     */
    private function _setPaymentOptions(
        PayerPostInterface $post,
        array $payment
    ) {
        if (empty($payment['method'])) {
            throw new InvalidRequestException("Missing argument: 'method' in 'payment'");
        }

        $post->add_payment_method($payment['method']);

        if (!empty($payment['language'])) {
            $post->set_language($payment['language']);
        }

        if (!empty($order['message'])) {
            $post->set_message($order['message']);
        }

        if (empty($payment['url'])) {
            throw new InvalidRequestException("Missing argument: 'url' in 'payment'");
        }

        $url = $payment['url'];

        # Payment Reservation
        if (empty($url['authorize'])) {
            throw new InvalidRequestException("Missing argument: 'authorize' in 'url'");
        }
        $post->set_authorize_notification_url($url['authorize']);

        # Payment Completed
        if (empty($url['settle'])) {
            throw new InvalidRequestException("Missing argument: 'settle' in 'url'");
        }
        $post->set_settle_notification_url($url['settle']);

        # Cancellation
        if (empty($url['redirect'])) {
            throw new InvalidRequestException("Missing argument: 'redirect' in 'url'");
        }
        $post->set_redirect_back_to_shop_url($url['redirect']);

        # Purchase Success
        if (empty($url['success'])) {
            throw new InvalidRequestException("Missing argument: 'success' in 'url'");
        }
        $post->set_success_redirect_url($url['success']);

        if (!empty($payment['options']['interaction'])) {
            $post->add_attribute('interaction', $payment['options']['interaction']);
        }

        if (!empty($payment['options']['installment_months'])) {
            $post->add_attribute('installment_month', $payment['options']['installment_months']);
        }

        if ($payment['options']['store'] == true) {
           $post->add_option("store", true);
        }
    }

}
