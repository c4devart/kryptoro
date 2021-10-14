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

class Term extends AbstractEntity
{
	/**
	 * @var string
	 */
	protected $term = null;

	/**
	 * @var string
	 */
	protected $context = null;

	/**
	 * @var string
	 */
	protected $reference = null;

	/**
	 * @var string
	 */
	protected $created = null;

	/**
	 * @var string
	 */
	protected $updated = null;

	/**
	 * @var string
	 */
	protected $plural = null;
	
	/**
	 * @return object Uj\Poed\Entity\Term
	 */
	public function __construct($term = null) {
		
		$this->setTerm($term);
		
		return $this;
	}
	
	/**
	 * @return string $term
	 */
	public function getTerm()
	{
		return $this->term;
	}

	/**
	 * @param string $term
	 */
	public function setTerm($term)
	{
		$this->term = $term;
	}

	/**
	 * @return string $context
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * @param string $context
	 */
	public function setContext($context)
	{
		$this->context = $context;
	}

	/**
	 * @return string $reference
	 */
	public function getReference()
	{
		return $this->reference;
	}

	/**
	 * @param string $reference
	 */
	public function setReference($reference)
	{
		$this->reference = $reference;
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

	/**
	 * @return string $updated
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 * @param string $updated
	 */
	public function setUpdated($updated)
	{
		$this->updated = $updated;
	}

	/**
	 * @return string
	 */
	public function getPlural()
	{
		return $this->plural;
	}

	/**
	 * @param string $plural
	 */
	public function setPlural($plural)
	{
		$this->plural = $plural;
	}

	public function toArray()
	{
		return array(
			'term' => $this->term,
			'context' => $this->context,
			'reference' => $this->reference,
			'created' => $this->created,
			'updated' => $this->updated,
			'plural' => $this->plural
		);
	}
}
