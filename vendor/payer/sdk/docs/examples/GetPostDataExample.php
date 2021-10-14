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
        'language'  => 'sv',
        'method'    => 'card',  // Examples: auto, card, invoice, bank, installment, swish
        'url' => array(
            'authorize' => 'http://example.com/CallbackEndpointAuthorizeExample.php', // The url to the Authorize Callback Resource
            'settle'    => 'http://example.com/CallbackEndpointSettlementExample.php', // The url to the Settlement Callback Resource
            'redirect'  => 'http://example.com',
            'success'   => 'http://example.com'
        ),
         'options' => array(
            // 'installment_months' => '',
            // 'interaction' => 'minimal',
            // 'store' => true
         )
    ),
    'purchase' => array(
        'charset'       => 'UTF-8',
        'currency'  => 'SEK',
        'description'   => 'This is an order description',
        'reference_id'  => base64_encode(rand()),
        'test_mode'     => true,

        'customer' => array(
            'identity_number'   => '1602079954',
            // 'organisation'      => 'Test Company',
            // 'your_reference'    => 'Test Reference',
            'first_name'        => '',
            'last_name'         => '',
            'address' => array(
                'address_1'     => '',
                'address_2'     => '',
                'co'            => '',
            ),
            'zip_code'      => 12345,
            'city'          => 'Stockholm',
            'country_code'  => 'SE',
            'phone' => array(
                'home'      => '1234567890',
                'work'      => '0987654321',
                'mobile'    => '111222333444'
            ),
            'email' => 'demo@payer.se'
        ),
        'items' => array(
            array(
                'type'                  => 'freeform',
                'line_number'           => 1,
                'article_number'        => 'ABC123',
                'description'           => "This is an freeform description",
                'unit_price'            => 40,
                'unit_vat_percentage'   => 20,
                'quantity'              => 5,
                'unit'                  => null
            ),
            // array(
            //     'type'                  => 'infoline',
            //     'line_number'           => 2,
            //     'article_number'        => 'ABC123',
            //     'description'           => "This is an infoline description",
            //     'unit'                  => null
            // ),
            // array(
            //     'type'                  => 'fee',
            //     'article_number'        => 'ABC123',
            //     'description'           => "This is an fee description",
            //     'unit_price'            => 40,
            //     'unit_vat_percentage'   => 25,
            //     'quantity'              => 1,
            // )
        )
    )
);

try {

    $gateway = Client::create($credentials);

    $purchase = new Purchase($gateway);
    $postData = $purchase->getPostData($data);
    
    var_dump($postData);

} catch (PayerException $e) {
    var_dump($e);
}
