<?php namespace Payer\Sdk\Format;
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

use Payer\Sdk\Format\Order;

class Purchase extends DataFormatter
{

    /**
     * Handles the default 'Callback Request' object format
     *
     * @param array $callbackRequest The callback request object to be filtered
     * @return array The filtered callback object
     *
     */
    public function filterCallbackRequest(array $callbackRequest) {
        if (!array_key_exists("proxy", $callbackRequest)) {
            $callbackRequest['proxy'] = array();
        }

        return $callbackRequest;
    }

    /**
     * Handles the default 'Debit' object format
     *
     * @param array $debit The debit request object to be filtered
     * @return array The filtered debit object
     *
     */
    public function filterDebit(array $debit)
    {
        if (!array_key_exists("recurring_token", $debit)) {
            $debit['recurring_token'] = '';
        }

        if (!array_key_exists("reference_id", $debit)) {
            $debit['reference_id'] = '';
        }

        if (!array_key_exists("amount", $debit)) {
            $debit['amount'] = '';
        }

        if (!array_key_exists("currency", $debit)) {
            $debit['currency'] = '';
        }

        if (!array_key_exists("description", $debit)) {
            $debit['description'] = '';
        }

        if (!array_key_exists("vat_percentage", $debit)) {
            $debit['vat_percentage'] = '';
        }

        return $debit;
    }

    /**
     * Handles the filtering of the Purchase Creation model
     *
     * @param array $create The create request object to be filtered
     * @return array The filtered crete object
     *
     */
    public function filterCreatePurchase(array $create)
    {
        $create['purchase'] = $this->filterPurchase($create['purchase']);
        $create['payment'] = $this->filterPayment($create['payment']);

        return $create;
    }

    /**
     * Handles the default 'Purchase' object format
     *
     * @param array $purchase The purchase request object to be filtered
     * @return array The filtered purchase object
     *
     */
    public function filterPurchase(array $purchase)
    {
        $formatter = new Order;

        if (!array_key_exists("charset", $purchase)) {
            $purchase['charset'] = 'ISO-8859-1';
        }

        if (!array_key_exists("client_ip", $purchase)) {
            $purchase['client_ip'] = '';
        }

        if (!array_key_exists("currency", $purchase)) {
            $purchase['currency'] = 'SEK';
        }

        if (!array_key_exists("description", $purchase)) {
            $purchase['description'] = date('Y-m-d H:i:s');
        }

        if (!array_key_exists("items", $purchase)) {
            $purchase['items'] = array();
        }
        $purchase['items'] = $formatter->filterItems($purchase['items']);

        if (!array_key_exists("options", $purchase)) {
            $purchase['options'] = array();
        }
        $purchase['options'] = $formatter->filterOptions($purchase['options']);

        if (!array_key_exists("order_number", $purchase)) {
            $purchase['order_number'] = '';
        }

        if (!array_key_exists("test_mode", $purchase)) {
            $purchase['test_mode'] = false;
        }

        if (!array_key_exists("customer", $purchase)) {
            $purchase['customer'] = array();
        }
        $purchase['customer'] = $formatter->filterCustomerDetails($purchase['customer']);

        return $purchase;
    }

    /**
     * Handles the default 'Payment' object format
     *
     * @param array $payment The payment request object to be filtered
     * @return array The filtered payment object
     *
     */
    public function filterPayment(array $payment) {

        if (!array_key_exists("language", $payment)) {
            $payment['language'] = '';
        }

        if (!array_key_exists("message", $payment)) {
            $payment['message'] = '';
        }

        if (!array_key_exists("method", $payment)) {
            $payment['method'] = '';
        }

        if (!array_key_exists("url", $payment)) {
            $payment['url'] = array();
        }

        if (!array_key_exists("authorize", $payment['url'])) {
            $payment['url']['authorize'] = '';
        }

        if (!array_key_exists("redirect", $payment['url'])) {
            $payment['url']['redirect'] = '';
        }

        if (!array_key_exists("settle", $payment['url'])) {
            $payment['url']['settle'] = '';
        }

        if (!array_key_exists("success", $payment['url'])) {
            $payment['url']['success'] = '';
        }

        if (!array_key_exists("options", $payment)) {
            $payment['options'] = array();
        }

        if (!array_key_exists("interaction", $payment['options'])) {
            $payment['options']['interaction'] = '';
        }

        if (!array_key_exists("installment_months", $payment['options'])) {
            $payment['options']['installment_months'] = '';
        }

        if (!array_key_exists("store", $payment['options'])) {
            $payment['options']['store'] = false;
        }

        return $payment;
    }

    /**
     * Handles the default 'Invoice Refund' object format
     *
     * @param array $refund The refund request object to be filtered
     * @return array The filtered refund object
     *
     */
    public function filterRefund(array $refund)
    {
        if (!array_key_exists("transaction_id", $refund)) {
            $refund['transaction_id'] = '';
        }

        if (!array_key_exists("reason", $refund)) {
            $refund['reason'] = '';
        }

        if (!array_key_exists("amount", $refund)) {
            $refund['amount'] = '';
        }

        if (!array_key_exists("vat_percentage", $refund)) {
            $refund['vat_percentage'] = '';
        }

        return $refund;
    }

    /**
     * Handles the default 'Settlement' object format
     *
     * @param array $settlement The settlement request object to be filtered
     * @return array The filtered settlement object
     *
     */
    public function filterSettlement(array $settlement)
    {
        if (!array_key_exists("settlement_id", $settlement)) {
            $settlement['settlement_id'] = '';
        }

        if (!array_key_exists("amount", $settlement)) {
            $settlement['amount'] = '';
        }

        return $settlement;
    }

}
