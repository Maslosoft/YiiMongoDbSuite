<?php

/**
 * @author Ianaré Sévi
 * @author Dariusz Górecki <darek.krk@gmail.com>
 * @author Invenzzia Group, open-source division of CleverIT company http://www.invenzzia.org
 * @copyright 2011 CleverIT http://www.cleverit.com.pl
 * @license New BSD license
 * @version 1.3
 * @category ext
 * @package ext.YiiMongoDbSuite
 */

namespace Maslosoft\Mangan;

use CDataProvider;

/**
 * Mongo document data provider
 *
 * Implements a data provider based on Document.
 *
 * DataProvider provides data in terms of Document objects which are
 * of class {@link modelClass}. It uses the AR {@link CActiveRecord::findAll} method
 * to retrieve the data from database. The {@link query} property can be used to
 * specify various query options, such as conditions, sorting, pagination, etc.
 *
 * @author canni
 * @since v1.0
 */
class DataProvider extends CDataProvider
{

	public static $CLS = __CLASS__;

	/**
	 * @var string the name of key field. Defaults to '_id', as a mongo default document primary key.
	 * @since v1.0
	 */
	public $keyField;

	/**
	 * @var string the primary ActiveRecord class name. The {@link getData()} method
	 * will return a list of objects of this class.
	 * @since v1.0
	 */
	public $modelClass;

	/**
	 * @var Document the AR finder instance (e.g. <code>Post::model()</code>).
	 * This property can be set by passing the finder instance as the first parameter
	 * to the constructor.
	 * @since v1.0
	 */
	public $model;

	/**
	 * @var Criteria
	 */
	private $_criteria;

	/**
	 * @var Sort
	 */
	private $_sort;

	/**
	 * Constructor.
	 * @param mixed $modelClass the model class (e.g. 'Post') or the model finder instance
	 * (e.g. <code>Post::model()</code>, <code>Post::model()->published()</code>).
	 * @param array $config configuration (name=>value) to be applied as the initial property values of this class.
	 * @since v1.0
	 */
	public function __construct($modelClass, $config = [])
	{
		if (is_string($modelClass))
		{
			$this->modelClass = $modelClass;
			$this->model = $modelClass::model();
		}
		else if ($modelClass instanceof Document)
		{
			$this->modelClass = get_class($modelClass);
			$this->model = $modelClass;
		}
		else
			throw new MongoException('Invalid model type for ' . __CLASS__);

		$this->_criteria = $this->model->getDbCriteria();
		if (isset($config['criteria']))
		{
			$this->_criteria->mergeWith($config['criteria']);
			unset($config['criteria']);
		}

		if (!$this->_criteria->getSelect())
		{
			$fields = array_keys($this->model->meta->fields());
			$fields = array_fill_keys($fields, true);
			$this->_criteria->setSelect($fields);
		}

		$this->setId($this->modelClass);
		foreach ($config as $key => $value)
			$this->$key = $value;

		if ($this->keyField !== null)
		{
			if (is_array($this->keyField))
				throw new MongoException('This DataProvider cannot handle multi-field primary key.');
		}
		else
			$this->keyField = '_id';
	}

	/**
	 * Returns the criteria.
	 * @return array the query criteria
	 * @since v1.0
	 */
	public function getCriteria()
	{
		return $this->_criteria;
	}

	/**
	 * Sets the query criteria.
	 * @param array $value the query criteria. Array representing the MongoDB query criteria.
	 * @since v1.0
	 */
	public function setCriteria($criteria)
	{
		if (is_array($criteria))
			$this->_criteria = new Criteria($criteria);
		else if ($criteria instanceof Criteria)
			$this->_criteria = $criteria;
	}

	/**
	 * Returns the sort object.
	 * @return Sort the sorting object. If this is false, it means the sorting is disabled.
	 */
	public function getSort()
	{
		if ($this->_sort === null)
		{
			$this->_sort = new Sort;
			$this->_sort->model = $this->model;
			if (($id = $this->getId()) != '')
				$this->_sort->sortVar = $id . '_sort';
		}
		return $this->_sort;
	}

	/**
	 * Fetches the data from the persistent data storage.
	 * @return array list of data items
	 * @since v1.0
	 */
	protected function fetchData()
	{
		if (($pagination = $this->getPagination()) !== false)
		{
			$pagination->setItemCount($this->getTotalItemCount());

			$this->_criteria->setLimit($pagination->getLimit());
			$this->_criteria->setOffset($pagination->getOffset());
		}

		/* if(($sort=$this->getSort())!==false && ($order=$sort->getOrderBy())!='')
		  {
		  $sort=array();
		  foreach($this->getSortDirections($order) as $name=>$descending)
		  {
		  $sort[$name]=$descending ? Criteria::SORT_DESC : Criteria::SORT_ASC;
		  }
		  $this->_criteria->setSort($sort);
		  } */
		if (($sort = $this->getSort()) !== false)
		{
			$sort->applyOrder($this->_criteria);
		}

		return $this->model->findAll($this->_criteria);
	}

	/**
	 * Returns the data items currently available, ensures that result is at leas empty array
	 * @param boolean $refresh whether the data should be re-fetched from persistent storage.
	 * @return array the list of data items currently available in this data provider.
	 */
	public function getData($refresh = false)
	{
		return parent::getData($refresh)? : [];
	}

	/**
	 * Fetches the data item keys from the persistent data storage.
	 * @return array list of data item keys.
	 * @since v1.0
	 */
	protected function fetchKeys()
	{
		$keys = [];
		foreach ($this->getData() as $i => $data)
			$keys[$i] = $data->{$this->keyField};

		return $keys;
	}

	/**
	 * Calculates the total number of data items.
	 * @return integer the total number of data items.
	 * @since v1.0
	 */
	public function calculateTotalItemCount()
	{
		return $this->model->count($this->_criteria);
	}

	/**
	 * Converts the "ORDER BY" clause into an array representing the sorting directions.
	 * @param string $order the "ORDER BY" clause.
	 * @return array the sorting directions (field name => whether it is descending sort)
	 * @since v1.0
	 */
	protected function getSortDirections($order)
	{
		$segs = explode(',', $order);
		$directions = [];
		foreach ($segs as $seg)
		{
			if (preg_match('/(.*?)(\s+(desc|asc))?$/i', trim($seg), $matches))
				$directions[$matches[1]] = isset($matches[3]) && !strcasecmp($matches[3], 'desc');
			else
				$directions[trim($seg)] = false;
		}
		return $directions;
	}

}