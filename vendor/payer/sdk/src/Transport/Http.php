<?php namespace Payer\Sdk\Transport;

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

use Payer\Sdk\Exception\HttpTransportException;
use Payer\Sdk\Transport\Http\Response;
use Payer\Sdk\Transport\Http\Request;

class Http implements HttpTransportInterface
{

    /**
     * cURL Handler
     *
     * @var Curl
     *
     */
    private $_curl;

    public function __construct()
    {
        $this->_curl = new Curl;
    }

    /**
     * Create a new Http Request
     *
     * @param Request $request
     * @return Response
     * @throws HttpTransportException
     *
     */
    public function request(Request $request)
    {
        $this->_curl->setOpt(CURLOPT_URL, $request->getUrl());

        $this->_curl->setOpt(CURLOPT_RETURNTRANSFER, true);

        if ($request->getMethod() === 'POST') {
            $this->_curl->setOpt(CURLOPT_POST, true);
            $this->_curl->setOpt(CURLOPT_POSTFIELDS, $request->getData());
        }

        $headers = array();
        foreach($request->getHeaders() as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }
        $this->_curl->setOpt(CURLOPT_HTTPHEADER, $headers);

        $this->_curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->_curl->setOpt(CURLOPT_CONNECTTIMEOUT, 15);
        $this->_curl->setOpt(CURLOPT_TIMEOUT, 15);

        $this->_curl->setOpt(CURLOPT_SSL_VERIFYHOST, 2);
        $this->_curl->setOpt(CURLOPT_SSL_VERIFYPEER, true);

        $body  = $this->_curl->exec();
        $info  = $this->_curl->info();
        $error = $this->_curl->error();

        if ($body === false) {
            throw new HttpTransportException($error);
        }

        $response = new Response(
            $request,
            intval($info['http_code']),
            array(),
            strval($body)
        );

        $this->_curl->close();

        return $response;
    }

}