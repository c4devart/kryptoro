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
use Payer\Sdk\Webservice\WebserviceInterface;

use Payer\Test\IntegrationPayerResourceStub;

class PayerTestCase extends \PHPUnit_Framework_TestCase {

    /**
     * Payer Webservice Credentials
     *
     * @var array
     *
     */
    protected $credentials;

    /**
     * Payer Webservice Gateway
     *
     * @var WebserviceInterface
     */
    protected $gateway;

    /**
     * Payer Dummy Resource Stub
     *
     * @var PayerResourceStub
     */
    protected $stub;

    /**
     *  Initializes the Payer test environment
     *
     */
    protected function setUp()
    {
        $this->credentials = array(

            // 'agent_id' => '',

            // 'post' => array(
            //     'key_1'             => '',
            //     'key_2'             => ''
            // ),

            // 'soap'  => array(
            //     'username' => '',
            //     'password' => ''
            // )

        );

        $this->gateway = Client::create($this->credentials);

        $this->stub = new PayerResourceStub($this->gateway);
    }

}