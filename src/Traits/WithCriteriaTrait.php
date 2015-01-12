<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Mangan\Traits;

use Maslosoft\Mangan\Criteria;
use Maslosoft\Mangan\Interfaces\IWithCriteria;

/**
 * WithCriteriaTrait
 * @see IWithCriteria
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
trait WithCriteriaTrait
{

	/**
	 * Criteria
	 * @var Criteria
	 */
	private $_criteria = null;

	/**
	 * Returns the mongo criteria associated with this model.
	 * @param boolean $createIfNull whether to create a criteria instance if it does not exist. Defaults to true.
	 * @return Criteria the query criteria that is associated with this model.
	 * This criteria is mainly used by {@link scopes named scope} feature to accumulate
	 * different criteria specifications.
	 * @since v1.0
	 */
	public function getDbCriteria($createIfNull = true)
	{
		if ($this->_criteria === null)
		{
			if (($c = $this->defaultScope()) !== [] || $createIfNull)
			{
				$this->_criteria = new Criteria($c);
			}
		}
		return $this->_criteria;
	}

	/**
	 * Set girrent object, this will override previous criteria
	 *
	 * @param Criteria|array $criteria
	 * @since v1.0
	 */
	public function setDbCriteria($criteria)
	{
		if (is_array($criteria))
		{
			$this->_criteria = new Criteria($criteria);
		}
		else if ($criteria instanceof Criteria)
		{
			$this->_criteria = $criteria;
		}
		else
		{
			$this->_criteria = new Criteria();
		}
	}

}