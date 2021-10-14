<?php
/**
 * Unjudder Api Client Module for Poeditor.com on top of Zendframework 2
 *
 * @link http://github.com/unjudder/poeditor-php-client for the canonical source repository
 * @copyright Copyright (c) 2013 unjudder
 * @license http://unjudder.com/license/new-bsd New BSD License
 * @package Uj\Poed
 */
namespace Uj\Poed\Entity;

use Uj\Poed\Api\Client;

abstract class AbstractEntity
{
	/**
	 * @var Client
	 */
	protected $apiClient;

	public function __construct(array $data = null, Client $apiClient = null)
	{
		if ($data !== null) {
			$this->importData($data);
		}
		if ($apiClient !== null) {
			$this->setApiClient($apiClient);
		}
	}

	public function importData(array $data)
	{
		foreach ($data as $property => $value) {
			$setter = 'set' . ucfirst($property);
			$this->$setter($value);
		}
	}

	public static function fromArray(array $data)
	{
		return new static($data);
	}

	public function assertApiClientIsReferenced()
	{
		if ($this->apiClient === null) {
			throw new \Exception('No Api Client is referenced for this entity.');
		}
	}

	public function setApiClient(Client $apiClient)
	{
		$this->apiClient = $apiClient;
	}

	public function getApiClient()
	{
		return $this->apiClient;
	}

	public function toArray()
	{
		return array();
	}
}
