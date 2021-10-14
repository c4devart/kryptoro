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

class Definition extends AbstractEntity
{
	/**
	 * @var Term
	 */
	protected $term = null;

	/**
	 * @var string
	 */
	protected $form = null;

	/**
	 * @var integer
	 */
	protected $fuzzy = null;

	/**
	 * @return Term $term
	 */
	public function getTerm()
	{
		return $this->term;
	}

	/**
	 * @param \Uj\Poed\Entity\Term $term
	 */
	public function setTerm(Term $term)
	{
		$this->term = $term;
	}

	/**
	 * @return string $form
	 */
	public function getForm()
	{
		return $this->form;
	}

	/**
	 * @param string $form
	 */
	public function setForm($form)
	{
		$this->form = $form;
	}

	/**
	 * @return integer $fuzzy
	 */
	public function getFuzzy()
	{
		return $this->fuzzy;
	}

	/**
	 * @param integer $fuzzy
	 */
	public function setFuzzy($fuzzy)
	{
		$this->fuzzy = $fuzzy;
	}

	protected $updated = null;

	public function setUpdated($updated){
            	$this->updated = $updated;
        }

	public function getUpdated(){
		return $this->updated;
	}

	public function toArray()
	{
	    return array(
	        'term' => $this->term->toArray(),
	        'definition' => array(
	            'forms' => array($this->form),
	            'fuzzy' => $this->fuzzy
	        )
        );
	}
}
