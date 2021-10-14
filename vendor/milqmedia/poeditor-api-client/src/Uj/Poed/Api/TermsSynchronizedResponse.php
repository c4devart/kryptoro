<?php
/**
 * Unjudder Api Client Module for Poeditor.com on top of Zendframework 2
 *
 * @link http://github.com/unjudder/poeditor-php-client for the canonical source repository
 * @copyright Copyright (c) 2013 unjudder
 * @license http://unjudder.com/license/new-bsd New BSD License
 * @package Uj\Poed
 */
namespace Uj\Poed\Api;

class TermsSynchronizedResponse extends TermsAddedResponse
{
	/**
	 * @var integer
	 */
	protected $updated = null;

	/**
	 * @var integer
	 */
	protected $deleted = null;

	/**
	 * @return integer $updated
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 * @param integer $updated
	 */
	public function setUpdated($updated)
	{
		$this->updated = $updated;
	}

	/**
	 * @return integer $deleted
	 */
	public function getDeleted()
	{
		return $this->deleted;
	}

	/**
	 * @param integer $deleted
	 */
	public function setDeleted($deleted)
	{
		$this->deleted = $deleted;
	}

	public function toArray()
	{
		return array_merge(
			parent::toArray(),
			array(
				'updated' => $this->updated,
				'deleted' => $this->deleted		
			)		
		);
	}
}
