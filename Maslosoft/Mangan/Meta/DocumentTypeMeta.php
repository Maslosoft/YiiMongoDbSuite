<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Mangan\Meta;

use Maslosoft\Addendum\Collections\MetaType;
use Maslosoft\Mangan\Helpers\PropertyMaker;
use ReflectionClass;

/**
 * Model meta container
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class DocumentTypeMeta extends MetaType
{

	use \Maslosoft\Mangan\Traits\Defaults\MongoClientOptions,
	  \Maslosoft\Mangan\Traits\Access\GetSet;

	/**
	 * Collection name
	 * @var string
	 */
	public $collectionName = '';

	/**
	 * Connection ID
	 * @var string
	 */
	public $connectionId = '';

	/**
	 * Primary key field or fields
	 * @var string|array
	 */
	public $primaryKey = null;

	/**
	 * Whenever to use cursors
	 * @var bool
	 */
	public $useCursor = false;

	/**
	 * Values of properties
	 * @var mixed
	 */
	private $_values = [];

	public function __construct(ReflectionClass $info = null)
	{
		// Client Options must be unset to allow cascading int EntityOptions
		foreach ($this->_getOptionNames() as $name)
		{
			PropertyMaker::defineProperty($this, $name);
		}
		foreach (['collectionName', 'connectionId'] as $name)
		{
			PropertyMaker::defineProperty($this, $name);
		}
	}

	public function __get($name)
	{
		if ($this->_hasGetter($name))
		{
			return parent::__get($name);
		}
		if(!array_key_exists($name, $this->_values))
		{
			throw new \Maslosoft\Mangan\ManganException(sprintf('Trying to read unitialized property `%s`', $name));
		}
		return $this->_values[$name];
	}

	public function __set($name, $value)
	{
		if ($this->_hasSetter($name))
		{
			return parent::__set($name);
		}
		$this->_values[$name] = $value;
	}

	public function __isset($name)
	{
		return array_key_exists($name, $this->_values);
	}

	public function getCollectionName()
	{
		if ($this->_values['collectionName'])
		{
			return $this->_values['collectionName'];
		}
		return str_replace('\\', '.', $this->name);
	}

	public function setCollectionName($name)
	{
		$this->_values['collectionName'] = $name;
	}

}
