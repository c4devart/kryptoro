<?php namespace Payer\Test\Integration;
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

use Payer\Sdk\Transport\Http\Response;

class InvoiceTest extends PayerTestCase {

    /**
     * Initializes the Payer Test Environment
     *
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test invoice activation
     *
     * @return void
     *
     */
    public function testActivateInvoice()
    {
        print "testActivateInvoice()\n";

        $createInvoiceResponse = $this->stub->createDummyInvoice();

        $activateInvoiceResponse = $this->stub->invoice->activate($createInvoiceResponse);

        var_dump($activateInvoiceResponse);

        $this->assertTrue($activateInvoiceResponse['invoice_number'] > 0);
    }

    /**
     * Test fetch invoice status
     *
     * @return void
     *
     */
    public function testGetInvoiceStatusNew()
    {
        print "testGetInvoiceStatus()\n";

        $createInvoiceResponse = $this->stub->createDummyInvoice();

        $getInvoiceStatusResponse = $this->stub->invoice->getStatus($createInvoiceResponse);

        var_dump($getInvoiceStatusResponse);

        $this->assertTrue(!empty($getInvoiceStatusResponse));
    }

    /**
     * Test fetch available template entries and bind it to an non-activated invoice
     *
     * @return void
     *
     */
    public function testInvoiceTemplateEntryBinding()
    {
        print "testInvoiceTemplateEntryBinding()\n";

        $activeTemplateEntriesResponse = $this->stub->invoice->getActiveTemplateEntries();

        var_dump($activeTemplateEntriesResponse);

        $this->assertNotEmpty(
            $activeTemplateEntriesResponse,
            "There was no active template entries to fetch. Please create an entry in your Payer Web"
        );

        $createInvoiceResponse = $this->stub->createDummyInvoice();

        $bindingData = array(
            'invoice_number'    => $createInvoiceResponse['invoice_number'],
            'template_entry_id' => $activeTemplateEntriesResponse['template_entries'][0]['template_entry_id']
        );
        $bindingResponse = $this->stub->invoice->bindToTemplateEntry($bindingData);

        var_dump($bindingResponse);

        $this->assertTrue(
            !empty($bindingResponse['template_entry_binding_id']) &&
            !empty($bindingResponse['create_date'])
        );
    }

    /**
     * Test refund an invoice
     *
     * @return void
     *
     */
    public function testRefundInvoice()
    {
        print "testRefundInvoice()\n";

        $createActiveInvoiceResponse = $this->stub->createActivatedDummyInvoice();

        $invoiceStatusData = array(
            'invoice_number' => $createActiveInvoiceResponse['invoice_number']
        );
        $invoiceStatusResponse = $this->stub->invoice->getStatus($invoiceStatusData);

        var_dump($invoiceStatusResponse);

        $refundData = array(
            'transaction_id'    => $invoiceStatusResponse['transaction_id'],
            'reason'            => 'Refund from Payer Sdk TestCase',
            'amount'            => $invoiceStatusResponse['total_amount'],
            'vat_percentage'    => 25
        );
        $refundInvoiceResponse = $this->stub->purchase->refund($refundData);

        var_dump($refundInvoiceResponse);

        $this->assertTrue($refundInvoiceResponse['transaction_id'] > 0);
    }

    /**
     * Test send an invoice copy via email
     *
     * @return void
     *
     */
    public function testSendEmailInvoice()
    {
        print "testSendInvoice()\n";

        $createActiveInvoiceResponse = $this->stub->createActivatedDummyInvoice();

        $sendInvoiceData = array(
            'invoice_number' => $createActiveInvoiceResponse['invoice_number'],
            'options' => array(
                'delivery_type' => 'email'
            )
        );

        $sendInvoiceResponse = $this->stub->invoice->send($sendInvoiceData);

        var_dump($sendInvoiceResponse);

        $this->assertTrue(!empty($sendInvoiceResponse));
    }

    /**
     * Test send an invoice copy via print
     *
     * @return void
     *
     */
    public function testSendPrintInvoice()
    {
        print "testSendInvoice()\n";

        $createActiveInvoiceResponse = $this->stub->createActivatedDummyInvoice();

        $sendInvoiceData = array(
            'invoice_number' => $createActiveInvoiceResponse['invoice_number'],
            'options' => array(
                'delivery_type' => 'print'
            )
        );

        $sendInvoiceResponse = $this->stub->invoice->send($sendInvoiceData);

        var_dump($sendInvoiceResponse);

        $this->assertTrue(!empty($sendInvoiceResponse));
    }

}