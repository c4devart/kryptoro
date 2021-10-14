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

class Project extends AbstractEntity
{
	/**
	 * @var string
	 */
	protected $id = null;

	/**
	 * @var string
	 */
	protected $name = null;

	/**
	 * @var string
	 */
	protected $public = null;
	
	/**
	 * @var boolean
	 */
	protected $open = true;

	/**
	 * @var string
	 */
	protected $created = null;

	/**
	 * @return string $id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}
	
	/**
	 * @return boolean $open
	 */
	public function getOpen()
	{
		return (bool) $this->open;
	}

	/**
	 * @param boolean $open
	 */
	public function setOpen($open)
	{
		$this->open = (bool) $open;
	}

	/**
	 * @return string $name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string $public
	 */
	public function getPublic()
	{
		return $this->public;
	}

	/**
	 * @param string $public
	 */
	public function setPublic($public)
	{
		$this->public = $public;
	}

	/**
	 * @return string $created
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * @param string $created
	 */
	public function setCreated($created)
	{
		$this->created = $created;
	}

	public function addLanguage($languageCode)
	{
		$this->assertApiClientIsReferenced();

		return $this->apiClient->addProjectLanguage($this->id, $languageCode);
	}

	public function deleteLanguage($languageCode)
	{
		$this->assertApiClientIsReferenced();

		return $this->apiClient->deleteProjectLanguage($this->id, $languageCode);
	}

	public function getTerms()
	{
		$this->assertApiClientIsReferenced();

		return $this->apiClient->getProjectTerms($this->id);
	}

	public function getDefinitions($languageCode)
	{
		$this->assertApiClientIsReferenced();
	
		return $this->apiClient->getProjectLanguageDefinitions($this->id, $languageCode);
	}
}
