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

class PurchaseTest extends PayerTestCase {

    /**
     * Initializes the Payer test environment
     *
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Initiates a purchase through the payment dialogue
     *
     * @return void
     */
    public function testCreatePurchase()
    {
        print "testPurchase()\n";

        // TODO: Impl

        $this->fail();
    }

    /**
     * Get the raw data of the post request
     *
     * @return void
     */
    public function testGetPostdata()
    {
	    	print "testGetPostdata()\n";

	    	// TODO: Impl

	    	$this->fail();
    }

    /**
     * Test refund a payment
     *
     * @return void
     */
    public function testRefund()
    {
        print "testRefund()\n";

        // TODO: Impl

        $this->fail();
    }

    /**
     * Test settlement a pending payment
     *
     * @return void
     */
    public function testSettlement()
    {
        print "testRefund()\n";

        // TODO: Impl

        $this->fail();
    }

}
