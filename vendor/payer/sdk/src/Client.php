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

class Client {

	/**
	 * Payer Resource Domain
	 *
	 */
	const PAYER_DOMAIN = 'https://secure.payer.se/PostAPI_V1';

	/**
	 * This is the main method that initiates the Payer Gateway
	 * and all available Payment Services.
	 *
	 *
	 * NOTICE: At least one of the webservice credentials has to be
	 * added in the array to be able to initiate the Payment Gateway.
	 *
	 * You can find your personal credentials at your Payer
	 * Administration page.
	 *
	 * @param array $credentials Mandatory
	 * @param array $options Non-mandatory
	 * @return PayerGatewayInterface
	 *
	 */
	public static function create(
		$credentials,
		$options = array()
	) {
		if (empty($options['domain'])) {
			$options['domain'] = self::PAYER_DOMAIN;
		}

		return new Gateway($credentials, $options);
	}

}
