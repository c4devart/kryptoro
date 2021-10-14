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

use Payer\Sdk\Client;

class ConnectionTest extends PayerTestCase {

    /**
     * Initializes the Payer test environment
     *
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test all webservice credentials
     *
     * @return void
     */
    public function testGatewayConnection()
    {
        print "testGatewayConnection()\n";

        $gateway = Client::create($this->credentials);

        $this->assertTrue(isset($gateway));
    }

    /**
     * Test Payer Post credentials
     *
     * @return void
     */
    public function testPostServiceCredentialSuccess()
    {
        print "testPostServiceCredentialSuccess()\n";

        $credentials = array(
            'agent_id' => $this->credentials['agent_id'],
            'post' => $this->credentials['post'] // Only post
        );

        $gateway = Client::create($credentials);

        $this->assertTrue(isset($gateway));
    }

    /**
     * Test Payer Soap credentials
     *
     * @return void
     */
    public function testSoapServiceCredentialSuccess()
    {
        print "testSoapServiceCredentialSuccess()\n";

        $credentials = array(
            'agent_id' => $this->credentials['agent_id'],
            'soap' => $this->credentials['soap'] // Only soap
        );

        $gateway = Client::create($credentials);

        $this->assertTrue(isset($gateway));
    }

    /**
     * Test that location is initialized as null
     *
     * @return void
     */
    public function testPostServiceWithEmptyCredentials()
    {
        print "testPostServiceWithEmptyCredentials()\n";

        $credentials = array(); // No credentials set

        $this->setExpectedException('Payer\Sdk\Exception\ValidationException');

        $gateway = Client::create($credentials);
    }

}