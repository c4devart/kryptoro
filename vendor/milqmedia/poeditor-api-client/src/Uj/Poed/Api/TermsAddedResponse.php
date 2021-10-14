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

use Uj\Poed\Entity\AbstractEntity;

class TermsAddedResponse extends AbstractEntity
{
	/**
	 * @var integer
	 */
	protected $parsed = null;

	/**
	 * @var integer
	 */
	protected $added = null;

	/**
	 * @return integer $parsed
	 */
	public function getParsed()
	{
		return $this->parsed;
	}

	/**
	 * @param integer $parsed
	 */
	public function setParsed($parsed)
	{
		$this->parsed = $parsed;
	}

	/**
	 * @return integer $added
	 */
	public function getAdded()
	{
		return $this->added;
	}

	/**
	 * @param integer $added
	 */
	public function setAdded($added)
	{
		$this->added = $added;
	}
}
