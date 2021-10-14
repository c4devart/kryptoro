<?php namespace Payer\Sdk\Webservice;
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

abstract class WebserviceInterface
{

    /**
     * The Payer Webservice Credentials
     *
     * @var array
     *
     */
    protected $credentials;

    /**
     * Payer Domain
     *
     * @var string
     */
    protected $domain;

    /**
     * Payer Webservice Location
     *
     * @var string
     */
    protected $relativePath;


    /**
     * Returns the Webservice Credentials
     *
     * @return array
     *
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Sets the Webservice Credentials
     *
     * @param $credentials
     *
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * Returns the current Payer Domain
     *
     * @return string
     *
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Sets the current Payer Domain
     *
     * @param $domain
     *
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Returns the resource location
     *
     * @return string
     *
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * Sets the resource location
     *
     * @param $relativePath
     *
     */
    public function setRelativePath($relativePath)
    {
        $this->relativePath = $relativePath;
    }

    /**
     * This method has to be implemented by every Webservice Interface which
     * takes care of the validation of the credentials
     *
     * @param array $credentials Webservice credentials
     *
     */
    abstract function validateCredentials(array $credentials);

}