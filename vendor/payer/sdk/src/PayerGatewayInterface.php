<?php namespace Payer\Sdk;
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

use Payer\Sdk\Exception\InvalidRequestException;
use Payer\Sdk\Webservice\PayerPostInterface;
use Payer\Sdk\Webservice\PayerRestInterface;
use Payer\Sdk\Webservice\PayerSoapInterface;

abstract class PayerGatewayInterface
{

    /**
     * Payer Domain
     *
     * @var string
     *
     */
    protected $domain;

    /**
     * The Payer Post Webservice Gateway
     *
     * @var PayerPostInterface
     *
     */
    protected $post;

    /**
     * The Payer Rest Webservice Gateway
     *
     * @var PayerRestInterface
     *
     */
    protected $rest;

    /**
     * The Payer Soap Webservice Gateway
     *
     * @var PayerSoapInterface
     *
     */
    protected $soap;

    /**
     * Payer Wsdl Url
     *
     * @var string
     *
     */
    protected $wsdl;

    /**
     * Returns the Payer Domain
     *
     * @return string
     *
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Sets the Payer Domain
     *
     * @param $domain
     *
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Returns the Payer WSDL url
     *
     * @return string
     *
     */
    public function getWsdl()
    {
        return $this->wsdl;
    }

    /**
     * Sets the Payer WSDL url
     *
     * @param $wsdl
     *
     */
    public function setWsdl($wsdl)
    {
        $this->wsdl = $wsdl;
    }

    /**
     * Sets the Payer Post Service Interface
     *
     * @throws InvalidRequestException
     * @return PayerPostInterface
     *
     */
    public function getPostService()
    {
        if (!isset($this->post)) {
            throw new InvalidRequestException("Post service not initiated");
        }

        return $this->post;
    }

    /**
     * Returns the Payer Rest Interface
     *
     * @throws InvalidRequestException
     * @return PayerRestInterface
     *
     */
    public function getRestService()
    {
        if (!isset($this->rest)) {
            throw new InvalidRequestException("Rest service not initiated");
        }

        return $this->rest;
    }

    /**
     * Returns the Payer Soap Interface
     *
     * @throws InvalidRequestException
     * @return PayerSoapInterface
     *
     */
    public function getSoapService()
    {
        if (!isset($this->soap)) {
            throw new InvalidRequestException("Soap service not initiated");
        }

        return $this->soap;
    }

}