<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Mangan\Options;

use Maslosoft\Addendum\Collections\Meta;
use Maslosoft\Mangan\Document;
use Maslosoft\Mangan\Helpers\PropertyMaker;

/**
 * EntityOptions
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class EntityOptions
{

	use \Maslosoft\Mangan\Traits\Defaults\MongoClientOptions;

	/**
	 * Model instance
	 * @var Document
	 */
	private $_model = null;

	/**
	 *
	 * @var Meta
	 */
	private $_meta = null;

	/**
	 * Values of this instance
	 * @var mixed[]
	 */
	private $_values = [];
	private $_defaults = [];

	public function __construct(Document $model)
	{
		// This is to use get/set
		foreach ($this->_getOptionNames() as $name)
		{
			PropertyMaker::defineProperty($this, $name, $this->_defaults);
		}

		$this->_meta = Meta::create($model);
	}

	public function __get($name)
	{
		if (array_key_exists($name, $this->_values))
		{
			return $this->_values[$name]; // We have flag set, return it
		}
		if (isset($this->_meta->type()->$name))
		{
			return $this->_meta->type()->$name;
		}
		/**
		 * TODO Below does not work
		 */
		return $this->getMongoDBComponent()->$name;
	}

	public function __set($name, $value)
	{
		$this->_values[$name] = $value;
	}

	public function __unset($name)
	{
		unset($this->_values[$name]);
	}

}