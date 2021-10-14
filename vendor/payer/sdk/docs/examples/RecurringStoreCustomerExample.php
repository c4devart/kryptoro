<?php
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

require_once "PayerCredentials.php";
require_once "../../vendor/autoload.php";

use Payer\Sdk\Client;
use Payer\Sdk\Resource\Purchase;

$data = array(
    'payment' => array(
        'currency'  => 'SEK',
        'language'  => 'sv',
        'method'    => '',  // E.g. auto, card, invoice, bank, installment...
        'url' => array(
            'authorize' => 'http://example.com/PurchaseAuthorizationExample.php',   // Authorization Resource
            'settle'    => 'http://example.com/PurchaseSettlementExample.php',      // Settlement Resource
            'redirect'  => 'http://example.com',
            'success'   => 'http://example.com'
        ),
        'options' =>    array(
            'store' => true     // NOTICE: This enables Recurring Payment
        )
    ),
    'order' => array(
        'charset'       => 'UTF-8',
        'description'   => 'This is an order description',
        'reference_id'  => base64_encode(rand()),
        'test_mode'     => true,
        'customer' => array(
            'identity_number' => '',
            'first_name'    => '',
            'last_name'     => '',
            'address' => array(
                'co'            => '',
                'address_1'     => '',
                'address_2'     => '',
            ),
            'zip_code'      => 12345,
            'city'          => 'Stockholm',
            'country_code'  => 'SE',
            'phone' => array(
                'home'      => '1234567890',
                'work'      => '0987654321',
                'mobile'    => '111222333444'
            ),
            'email' => 'demo@payer.se',
            'organisation'  => array(
                'name'      => 'Test Company',
                'number'    => 1234567890,
                'reference' => 'Test person'
            )
        ),
        'items' => array(
            array(
                'line_number'           => 1,
                'article_number'        => 'ABC123',
                'description'           => "This is an item description",
                'unit_price'            => 40,
                'unit_vat_percentage'   => 20,
                'quantity'              => 5,
                'unit'                  => null,
                'account'               => null,
                'dist_agent_id'         => null
            )
        )
    )
);

try {

    /*
     *  NOTICE: This step will take care of initializing a payment at
     *          Payer with the purpose to store the customers card details
     *          for the upcoming recurring payments.
     *
     *  1. This is the first step in the recurring payment chain
     *
     */

    $gateway = Client::create($credentials);

    $purchase = new Purchase($gateway);
    $purchase->create($data);

    // Next step in 'RecurringFetchTokenExample.php'

} catch (PayerException $e) {
    var_dump($e);
}

