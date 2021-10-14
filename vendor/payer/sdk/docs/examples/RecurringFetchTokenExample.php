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

try {

    /*
     *  NOTICE: A purchase with the option flag 'store' set to 'true' must have
     *          been completed before continuing with this step.
     *
     *  Callback url format example:    http://example.com/RecurringFetchTokenExample.php?payer_unique_id=TEST_6973b3c1-5fdb-49f6-8027-480a0d1a9bae
     *
     *  2. Fetch the token from the Authorize callback url
     *
     */

    // $recurringToken = $_GET['payer_unique_id'];
    //
    // Store the Recurring Token securely
    // ...

    $gateway = Client::create($credentials);

    $purchase = new Purchase($gateway);
    $purchase->createAuthorizeResource();

    // Next step in 'RecurringDebitExample.php'

} catch (PayerException $e) {
    var_dump($e);
}

