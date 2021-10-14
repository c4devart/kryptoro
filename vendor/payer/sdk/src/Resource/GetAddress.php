<?php namespace Payer\Sdk\Resource;
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
use Payer\Sdk\Format\GetAddress as GetAddressFormatter;
use Payer\Sdk\PayerGatewayInterface;
use Payer\Sdk\Transport\Http\Response;
use Payer\Sdk\Transport\Http\Request;
use Payer\Sdk\Transport\Http;

class GetAddress extends PayerResource
{
    /**
     * Get address object formatter
     *
     * @var DataFormatter
     *
     */
    private $_formatter;

    /**
     * Resource Location
     *
     * @var string
     *
     */
    protected $relativePath = '/api/getAddress';

    public function __construct(PayerGatewayInterface $gateway)
    {
        $this->gateway = $gateway;

        $this->_formatter = new GetAddressFormatter();
    }

    /**
     * Fetches the defined customers address details.
     *
     * NOTICE: This method depends on valid Post credentials
     *
     * @param array $input The GetAddress object
     * @return string An array of address details as json
     * @throws InvalidRequestException
     *
     */
    public function create(array $input)
    {
        $input = $this->_formatter->filterGetAddress($input);

        $identityNumber = $input['identity_number'];
        if (empty($identityNumber)) {
            throw new InvalidRequestException("Missing argument: 'identity_number'");
        }

        $zipCode = $input['zip_code'];
        if (empty($zipCode)) {
            throw new InvalidRequestException("Missing argument: 'zip_code'");
        }

        $challengeToken = $input['challenge_token'];
        if (empty($challengeToken)) {
            throw new InvalidRequestException("Missing argument: 'challenge_token'");
        }

        $post = $this->gateway->getPostService();
        $credentials = $post->getCredentials();

        $requestHeaders = array();
        $requestHeaders['Content-Type'] = 'application/json; charset=utf-8';

        $data = array(
            'identity_number'   => $identityNumber,
            'zip_code'          => $zipCode,
            'challenge_token'   => $challengeToken,
            'agent_id'          => $credentials['agent_id']
        );

        $request = new Request(
            $this->gateway->getDomain() . $this->relativePath,
            'POST',
            $requestHeaders,
            json_encode($data)
        );

        $http = new Http;
        $response = $http->request($request);

        $customer = Response::fromJson($response->getData());

        return array(
            'status'            => $customer['status'],
            'identity_number'   => $customer['identity_number'],
            'organisation'      => $customer['organisation'],
            'first_name'        => $customer['first_name'],
            'last_name'         => $customer['last_name'],
            'address_1'         => $customer['address_1'],
            'address_2'         => $customer['address_2'],
            'zip_code'          => $customer['zip_code'],
            'city'              => $customer['city'],
            'country'           => $customer['country']
        );
    }

}
