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

class Response
{

    /**
     * The Payload
     *
     * @var string
     *
     */
    protected $data;

    /**
     * The Http Headers
     *
     * @var array
     */
    protected $headers;


    /**
     * The originating Http Request
     *
     * @var Request
     *
     */
    protected $request;

    /**
     * The Http Status Code
     *
     * @var string
     *
     */
    protected $statusCode;

    /**
     * Create a new Http Response object
     *
     * @param Request $request
     * @param $statusCode
     * @param array $headers
     * @param string $data
     *
     */
    public function __construct(
        Request $request,
        $statusCode,
        array $headers = array(),
        $data = ''
    ) {
        $this->request = $request;
        $this->statusCode = $statusCode;
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
     * Returns the Http Header array
     *
     * @return array
     *
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Returns the Http Status Code
     *
     * @return string
     *
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Returns the originating Http Request
     *
     * @return string
     *
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Converts from array to String
     *
     * @param array $data
     * @return string Json
     *
     */
    public static function fromArray(array $data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Converts from Json to an associative array.
     *
     * @param string $data
     * @return array Associative array
     *
     */
    public static function fromJson($data)
    {
        return json_decode($data, true);
    }

}