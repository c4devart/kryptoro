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

use Payer\Sdk\Exception\ValidationException;
use Payer\Sdk\Format\Credential;
use Payer\Sdk\Transport\Http;
use Payer\Sdk\Webservice\PayerPostInterface;
use Payer\Sdk\Webservice\PayerRestInterface;
use Payer\Sdk\Webservice\PayerSoapInterface;
use Payer\Sdk\Webservice\PostService;
use Payer\Sdk\Webservice\RestService;
use Payer\Sdk\Webservice\SoapService;

class Gateway extends PayerGatewayInterface {

	/**
	 * Payer Gateway Constructor
	 *
	 * @param array $credentials
	 * @param array $options
	 * @throws ValidationException
	 *
	 */
	public function __construct(
		array $credentials,
		array $options
	) {
		$this->_setupOptions($options);
		$this->_setupServices($credentials);
	}

	/**
	 * Sets the Payer Post Interface
	 *
	 * @param PayerPostInterface $post
	 *
	 */
	private function _setPostService(PayerPostInterface $post)
	{
		$this->post = $post;
	}

	/**
	 * Sets the Payer Rest Interface
	 *
	 * @param PayerRestInterface $rest
	 *
	 */
	private function _setRestService(PayerRestInterface $rest)
	{
		$this->rest = $rest;
	}

	/**
	 * Sets the Payer Soap Interface
	 *
	 * @param PayerSoapInterface $soap
	 *
	 */
	private function _setSoapService(PayerSoapInterface $soap)
	{
		$this->soap = $soap;
	}

	/**
	 * Setups extended options
	 *
	 * @param $options
	 *
	 */
	private function _setupOptions($options)
	{
		if (array_key_exists("domain", $options)) {
			$this->domain = $options['domain'];
		}
	}

	/**
	 * Initiates the available Payer Webservices depending on what credential
	 * type that has been added into the credential array.
	 *
	 * @param $credentials
	 * @throws ValidationException
	 *
	 */
	private function _setupServices($credentials)
	{
		if (empty($credentials)) {
			throw new ValidationException("Missing credentials");
		}

		$formatter = new Credential;
		$credentials = $formatter->filterCredentials($credentials);

		if (!empty($credentials['post'])) {
			$credentials['post']['agent_id'] = $credentials['agent_id'];
			$this->_setPostService(
				new PostService($this->getDomain(), $credentials['post'])
			);
		}

		if (!empty($credentials['rest'])) {
			$credentials['rest']['agent_id'] = $credentials['agent_id'];
			$this->_setRestService(
				new RestService(
					new Http,
					$this->getDomain(),
					$credentials['rest']
				)
			);
		}

		if (!empty($credentials['soap'])) {
			$credentials['soap']['agent_id'] = $credentials['agent_id'];
			$this->_setSoapService(
				new SoapService($this->getDomain(), $credentials['soap'])
			);

		}
	}

}