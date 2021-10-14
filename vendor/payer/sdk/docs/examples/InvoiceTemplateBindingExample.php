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
use Payer\Sdk\Resource\Invoice;

use Payer\Sdk\Transport\Http\Response;

$data = array(
    'invoice_number'        => '',  // The invoice number to fetch
    'template_entry_id'     => '',  // The id of the Template Entry to use
);

try {
    $gateway = Client::create($credentials);

    $invoice = new Invoice($gateway);

    // Fetches an array of active template entries
    //
    // $templateEntryResponse = $invoice->getActiveTemplateEntries();
    // var_dump($templateEntryResponse);

    // Bind the invoice to a specific template
    $bindInvoiceResponse = $invoice->bindToTemplateEntry($data);

    var_dump($bindInvoiceResponse);

    $bindingId =        $bindInvoiceResponse['template_entry_binding_id'];
    $entryId =          $bindInvoiceResponse['template_entry_id'];
    $invoiceNumber =    $bindInvoiceResponse['invoice_number'];
    $createDate =       $bindInvoiceResponse['create_date'];

} catch (PayerException $e) {
    var_dump($e);
}