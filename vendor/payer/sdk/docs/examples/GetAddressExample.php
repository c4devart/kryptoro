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
use Payer\Sdk\Resource\Challenge;
use Payer\Sdk\Resource\GetAddress;
use Payer\Sdk\Transport\Http\Response;

$data = array(
    'identity_number' => '9008221716',
    'zip_code'        => '13234'
);

try {

    $gateway = Client::create($credentials);

    $challenge = new Challenge($gateway);

    // Fetch the session token
    $challengeResponse = $challenge->create();

    var_dump($challengeResponse);

    $data['challenge_token'] = $challengeResponse['challenge_token'];

    $getAddress = new GetAddress($gateway);

    // Fetch the address
    $getAddressResponse = $getAddress->create($data);

    var_dump($getAddressResponse);

    $identityNumber     = $getAddressResponse['identity_number'];
    $organisationName   = $getAddressResponse['organisation'];
    $firstName          = $getAddressResponse['first_name'];
    $lastName           = $getAddressResponse['last_name'];
    $address1           = $getAddressResponse['address_1'];
    $address2           = $getAddressResponse['address_2'];
    $zipCode            = $getAddressResponse['zip_code'];
    $city               = $getAddressResponse['city'];
    $country            = $getAddressResponse['country'];

} catch (PayerException $e) {
    var_dump($e);
}
