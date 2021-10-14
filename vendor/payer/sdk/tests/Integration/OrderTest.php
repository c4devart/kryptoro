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

class OrderTest extends PayerTestCase {

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test create an order
     *
     * @return void
     *
     */
    public function testCreateOrder()
    {
        print "testCreateOrder()\n";

        $orderData = $this->stub->orderData;
        $createOrderResponse = $this->stub->order->create($orderData);
        var_dump($createOrderResponse);

        $this->assertTrue(
            is_integer($createOrderResponse['order_id']) &&
            $createOrderResponse['order_id'] > 0
        );
    }

    /**
     * Test fetch order status
     *
     * @return void
     *
     */
    public function testCreateOrderAndGetStatus()
    {
        print "testCreateOrderAndGetStatus()\n";

        $createOrderResponse = $this->stub->createDummyOrder();
        $getStatusData =  array(
            'order_id' => $createOrderResponse['order_id']
        );
        $getStatusResponse = $this->stub->order->getStatus($getStatusData);
        var_dump($getStatusResponse);

        $this->assertTrue(!empty($getStatusResponse));
    }

    /**
     * Test create and commit an order by order id
     *
     * @return void
     *
     */
    public function testCreateAndCommitOrderByOrderId()
    {
        print "testCreateAndCommitOrderByOrderId()\n";

        $createOrderResponse = $this->stub->createDummyOrder();
        $commitData =  array(
            'order_id' => $createOrderResponse['order_id']
        );
        $commitOrderResponse = $this->stub->order->commit($commitData);
        var_dump($commitOrderResponse);

        $this->assertTrue($commitOrderResponse['invoice_number'] > 0);
    }

    /**
     * Test create and commit an order by reference id
     *
     * @return void
     *
     */
    public function testCreateAndCommitOrderByReferenceId()
    {
        print "testCreateAndCommitOrderByReferenceId()\n";

        $orderData = $this->stub->orderData;

        // Add the merchants reference id
        $referenceId = base64_encode(rand());
        $orderData['reference_id'] = $referenceId;

        $createOrderResponse = $this->stub->createDummyOrder($orderData);
        $commitData =  array(
            'reference_id' => $referenceId
        );
        $commitOrderResponse = $this->stub->order->commit($commitData);
        var_dump($commitOrderResponse);

        $this->assertTrue($commitOrderResponse['invoice_number'] > 0);
    }

}