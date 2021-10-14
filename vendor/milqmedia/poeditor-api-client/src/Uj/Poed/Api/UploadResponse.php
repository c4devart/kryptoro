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

class UploadResponse extends AbstractEntity
{
    /**
     * @var DefinitionsUpdatedResponse
     */
    protected $definitions = null;

    /**
     * @var TermsSynchronizedResponse
     */
    protected $terms = null;

	/**
     * @return DefinitionsUpdatedResponse $definitions
     */
    public function getDefinitions ()
    {
        return $this->definitions;
    }

	/**
     * @param array|DefinitionsUpdatedResponse $definitions
     */
    public function setDefinitions ($definitions)
    {
        if (is_array($definitions) === true) {
            $definitions = new DefinitionsUpdatedResponse($definitions);
        }
        $this->definitions = $definitions;
    }

	/**
     * @return TermsSynchronizedResponse $terms
     */
    public function getTerms ()
    {
        return $this->terms;
    }

	/**
     * @param array|TermsSynchronizedResponse $terms
     */
    public function setTerms ($terms)
    {
        if (is_array($terms) === true) {
            $terms = new TermsSynchronizedResponse($terms);
        }
        $this->terms = $terms;
    }

	public function toArray()
	{
	    return array (
	        'terms' => $this->terms->toArray(),
	        'definitions' => $this->definitions->toArray()
        );
	}
}
