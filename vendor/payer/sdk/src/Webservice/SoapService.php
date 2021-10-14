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

use Exception;
use RunTimeException;
use SoapClient;

use Payer\Sdk\Exception\InvalidRequestException;
use Payer\Sdk\Exception\ValidationException;
use Payer\Sdk\Exception\WebserviceException;

class SoapService extends WebserviceInterface implements PayerSoapInterface
{

	/**
	 * Payer Soap Wsdl
	 *
	 * @var string
	 *
	 */
	protected $wsdl;

	/**
	 * The id of the current Payer Soap session
	 *
	 * @var string
	 *
	 */
	protected $sessionId;

	/**
	 * Php Soap Client
	 *
	 * @var SoapClient
	 *
	 */
	protected $soap;

	/**
	 * Instantiate the Payer Soap Interface
	 *
	 * @param null|string $domain Payer Domain
	 * @param array $credentials
	 * @throws RuntimeException
	 * @throws ValidationException
	 * @throws InvalidRequestException
	 */
	public function __construct(
		$domain,
		array $credentials
	) {
		if (!extension_loaded('soap')) {
			throw new RuntimeException('Payer Soap Service requires an installed soap extension');
		}

		$this->domain = $domain;
		$this->relativePath = '/services/PublicPayerCore?wsdl';

		$this->wsdl = $this->getDomain() . $this->getRelativePath();

		// Instantiates the Soap client
		$this->soap = new SoapClient($this->wsdl, array());

		// Check if the credentials are valid
		if (!$this->validateCredentials($credentials)) {
			throw new ValidationException("Soap credential validation failed");
		}
		$this->setCredentials($credentials);

	}

	/**
	 * Binds an invoice to a specific template entry
	 *
	 * @param $invoiceNumber
	 * @param $entryId
	 * @return array TemplateEntryBinding
	 * @throws WebserviceException
	 *
	 */
	public function bindInvoiceToTemplateEntry(
		$invoiceNumber,
		$entryId
	) {
		try {
			return $this->soap->bindInvoiceToTemplateEntry($this->sessionId, $invoiceNumber, $entryId);
		} catch (Exception $e) {
			$this->_handleException($e->getMessage());
		}
	}

	/**
	 * Destroys the current session.
	 *
	 * IMPORTANT: Please use this in the end of the request chain.
	 *
	 * @return string Session token
	 *
	 */
	public function close()
	{
		try {
			return $this->sessionId = $this->soap->destroySession($this->sessionId);
		} catch (Exception $e) {
			$this->_handleException($e->getMessage());
		}
	}

	/**
	 * Transfers the order to an invoice
	 *
	 * @param $orderId
	 * @return int The invoice number
	 * @throws WebserviceException
	 *
	 */
	public function commitOrder($orderId)
	{
		try {
			return  $this->soap->commitOrder(
				$this->sessionId,
				$orderId
			);
		} catch (Exception $e) {
			$this->_handleException($e->getMessage());
		}
	}

	/**
	 * Transfers the order to an invoice
	 *
	 * @param $referenceId
	 * @return int The invoice number
	 * @throws WebserviceException
	 *
	 */
	public function commitOrderByReference($referenceId)
	{
		try {
			return  $this->soap->commitOrderByReference(
				$this->sessionId,
				$referenceId
			);
		} catch (Exception $e) {
			$this->_handleException($e->getMessage());
		}
	}

	/**
	 * Creates a new order
	 *
	 * @param $OrderHeader
	 * @param $OrderEntries
	 * @param $Options
	 * @return int The order id
	 * @throws WebserviceException
	 *
	 */
	public function createOrder(
		$OrderHeader,
		$OrderEntries,
		$Options
	) {
		try {
			return $this->soap->createDirectOrder(
				$this->sessionId,
				$OrderHeader,
				$OrderEntries,
				$Options
			);
		} catch (Exception $e) {
			$this->_handleException($e->getMessage());
		}
	}

	/**
	 * Used in cases of recurring payments
	 *
	 * @param string $uniqReferenceId Provided after initiating a session
	 * @param string $merchantReferenceId The merchants order number
	 * @param int|float $amount The amount to debit
	 * @param string $currencyId The currency of the debitation
	 * @param string $description The debit description
	 * @param int $vatPercentage The vat percentage of the debit amount
	 * @return int Transaction Id
	 *
	 */
	public function debitByReference(
		$uniqReferenceId,
		$merchantReferenceId,
		$amount,
		$currencyId,
		$description,
		$vatPercentage
	) {
		try {
			return $this->soap->debitByReference(
				$this->sessionId,
				$uniqReferenceId,
				$merchantReferenceId,
				(float)$amount + 0.0,
				$currencyId,
				$description,
				(int)$vatPercentage + 0
			);
		} catch (Exception $e) {
			$this->_handleException($e->getMessage());
		}
	}

	/**
	 * Returns an array of all available template entries for invoice binding
	 *
	 * @return array Template Entry objects
	 * @throws WebserviceException
	 *
	 */
	public function getActiveInvoiceTemplateEntries()
	{
		try {
			return $this->soap->getActiveInvoiceTemplateEntries($this->sessionId);
		} catch (Exception $e) {
			$this->_handleException($e->getMessage());
		}
	}

	/**
	 * Activates the invoice
	 *
	 * @param $invoiceNumber
	 * @param bool $printAsService
	 * @param string $activationOptions
	 * @return int The transaction id
	 * @throws WebserviceException
	 *
	 */
	public function invoiceActivation(
		$invoiceNumber,
		$printAsService = false,
		$activationOptions = ''
	) {
		try {
			return $this->soap->invoiceActivationEx(
				$this->sessionId,
				$invoiceNumber,
				$printAsService,
				$activationOptions
			);
		} catch (Exception $e) {
			$this->_handleException($e->getMessage());
		}
	}

	/**
	 * Fetches the current invoice state
	 *
	 * @param $invoiceNumber
	 * @return array The invoice status object
	 * @throws WebserviceException
	 *
	 */
	public function invoiceStatus($invoiceNumber)
	{
		try {
			return $this->soap->invoiceStatus(
				$this->sessionId,
				$invoiceNumber
			);
		} catch (Exception $e) {
			$this->_handleException($e->getMessage());
		}
	}

	/**
	 * Fetches the current order state
	 *
	 * @param $orderId
	 * @return array The order status object
	 * @throws WebserviceException
	 *
	 */
	public function orderStatus($orderId)
	{
		try {
			return $this->soap->orderStatus(
				$this->sessionId,
				$orderId
			);
		} catch (Exception $e) {
			$this->_handleException($e->getMessage());
		}
	}

	/**
	 *	Sends a copy of an invoice with the specified delivery type
	 *
	 * @param int|string $invoiceNumber
	 * @param array $options
	 * @return int The send queue process id
	 * @throws WebserviceException
	 *
	 */
	public function sendInvoice(
		$invoiceNumber,
		$options
	) {
		try {
			return $this->soap->sendInvoice(
				$this->sessionId,
				(int)$invoiceNumber,
				$options
			);
		} catch (Exception $e) {
			$this->_handleException($e->getMessage());
		}
	}

	/**
	 * Performs a settlement on a pending, only authorized, payment
	 *
	 * @return int The transaction id
	 * @throws WebserviceException
	 *
	 */
	public function settlement($settlementId, $amount)
	{
		try {
			return $this->soap->delayedSettlement(
				$this->sessionId,
				$settlementId,
				$amount
			);
		} catch (Exception $e) {
			$this->_handleException($e->getMessage());
		}
	}

	/**
	 * Refunds the transaction by the entered amount
	 *
	 * @param string|int $TransactionId The id of the transaction to refund
	 * @param string $Reason The reason for the refund
	 * @param int|float $Amount The amount to refund
	 * @param int $Vat The vat in percentage (e.g. 25, 12, 6)
	 * @return int|string The transaction id of the refund
	 *
	 */
	public function simpleRefund(
		$TransactionId,
		$Reason,
		$Amount,
		$Vat
	) {
		try {
			return $this->soap->simpleRefundEx(
				$this->sessionId,
				$TransactionId,
				$Reason,
				$Amount + 0.0,
				$Vat + 0
			);
		} catch (Exception $e) {
			$this->_handleException($e->getMessage());
		}
	}

	/**
	 * Initiates a new Payer Soap session
	 *
	 * IMPORTANT: Needs to be called to establish the Payer Soap session
	 *
	 * @throws InvalidRequestException
	 *
	 */
	public function start()
	{
		try {
			$this->sessionId = $this->soap->createSessionEx(
				$this->credentials['username'],
				$this->credentials['password'],
				$this->credentials['agent_id']
			);
		} catch (Exception $e) {
			$this->_handleException($e->getMessage());
		}
	}

	/**
	 * Validates the Soap credentials
	 *
	 * @param array $credentials
	 * @return bool
	 *
	 */
	public function validateCredentials(array $credentials)
	{
		if (empty($credentials['agent_id']))
			return false;

		if (empty($credentials['username']))
			return false;

		if (empty($credentials['password']))
			return false;

		return true;
	}

	/**
	 * Handles Webservice Exceptions
	 *
	 * @param string $message Exception message
	 * @throws WebserviceException
	 *
	 */
	private function _handleException($message)
	{
		throw new WebserviceException($message);
	}

}
