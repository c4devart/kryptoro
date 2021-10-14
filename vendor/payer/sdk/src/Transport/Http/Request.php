<?php namespace Payer\Sdk\Transport\Http;
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

class Request
{

    /**
     * The Http Body
     *
     * @var string
     *
     */
    protected $data;

    /**
     * Http Headers
     *
     * @var array
     *
     */
    protected $headers;

    /**
     * Http Method
     *
     * @var string
     *
     */
    protected $method;

    /**
     * Resource Location
     *
     * @var string
     *
     */
    protected $url;

    /**
     * Create a new Http Request object
     *
     * @param $url
     * @param string $method
     * @param array $headers
     * @param string $data
     *
     */
    public function __construct(
        $url,
        $method = 'GET',
        array $headers = array(),
        $data = ''
    ) {
        $this->url = $url;
        $this->method = $method;
        $this->headers = $headers;
        $this->data = $data;
    }

    /**
     * Returns the Http Body
     *
     * @return string
     *
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns an array of Http Header attributes
     *
     * @return array
     *
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Returns the method of the Http Request
     *
     * @return string
     *
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns the resource location
     *
     * @return string
     *
     */
    public function getUrl()
    {
        return $this->url;
    }

}