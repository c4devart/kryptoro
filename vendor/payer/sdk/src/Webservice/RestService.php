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

use Payer\Sdk\Exception\ValidationException;
use Payer\Sdk\Transport\Http;
use Payer\Sdk\Transport\Http\Request;
use Payer\Sdk\Transport\HttpTransportInterface;

class RestService extends WebserviceInterface implements PayerRestInterface
{

	/**
	 * HTTP Transport Handler
	 *
	 * @var HttpTransportInterface
	 *
	 */
	protected $http;

	/**
	 * Instantiate the Payer Rest Interface
	 *
	 * @param HttpTransportInterface $http
	 * @param string $domain domain
	 * @param array $credentials Rest credentials
	 * @throws ValidationException
	 */
	public function __construct(
		HttpTransportInterface $http,
		$domain,
		array $credentials
	) {
		$this->http = $http;

		$this->domain = $domain;
		$this->relativePath = '/api';

		// Checks if the credentials are valid
		if (!$this->validateCredentials($credentials))
		{
			throw new ValidationException("Rest credential validation failed");
		}
		$this->setCredentials($credentials);
	}

	/**
	 * Test the Payer Rest API connection
	 *
	 * @return Http\Response
	 */
	public function ping()
	{
		return $this->_handle($this->relativePath . '/ping');
	}

	/**
	 * Validates the Rest credentials
	 *
	 * @param $credentials array Payer Rest credentials
	 * @return bool Returns true/false upon validation success or failure
	 *
	 */
	public function validateCredentials(array $credentials)
	{
		if (empty($credentials['username']))
			return false;

		if (empty($credentials['password']))
			return false;

		return true;
	}

	/**
	 * Http Transport Wrapper
	 *
	 * @param $location
	 * @param string $data
	 * @param array $headers
	 * @param string $method
	 * @return Http\Response
	 */
	private function _handle(
		$location,
		$data = '',
		array $headers = array(),
		$method = 'GET'
	) {
		$credentials = $this->getCredentials();

		// Set the Authentication Header
		if (!array_key_exists("Authorization", $headers)) {
			$headers['Authorization'] = 'Basic ' . base64_encode($credentials['username'] . ':' . $credentials['password']);
		}

		$request = new Request(
			$this->domain . $location,
			$method,
			$headers,
			$data
		);

		$response = $this->http->request($request);

		return $response->getData();
	}

}