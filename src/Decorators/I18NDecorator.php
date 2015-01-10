<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Mangan\Decorators;

use Maslosoft\Mangan\Interfaces\I18NAble;
use Maslosoft\Mangan\Interfaces\IModel;
use Maslosoft\Mangan\ManganException;

/**
 * This creates i18n fields
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class I18NDecorator implements IDecorator
{

	/**
	 * This will be called when getting value.
	 * This should return end user value.
	 * @param IModel $model Document model which will be decorated
	 * @param string $name Field name
	 * @param mixed $dbValue
	 * @return bool Return true if value should be assigned to model
	 */
	public function read($model, $name, &$dbValue)
	{
		if (!$model instanceof I18NAble)
		{
			throw new ManganException(sprintf('Model class %s must implement interface %s to support I18N fields. You can use trait I18NAbleTrait as default implementation.', get_class($model), I18NAble::class));
		}
		$model->setRawI18N($dbValue);
		$model->$name = $dbValue[$model->getLang()];
		return true;
	}

	/**
	 * This will be called when setting value.
	 * This should return db acceptable value
	 * @param IModel $model Document model which will be decorated
	 * @param string $name Field name
	 * @param mixed $dbValue
	 * @return bool Return true to store value to database
	 */
	public function write($model, $name, &$dbValue)
	{
		if (!$model instanceof I18NAble)
		{
			throw new ManganException(sprintf('Model class %s must implement interface %s to support I18N fields. You can use trait I18NAbleTrait as default implementation.', get_class($model), I18NAble::class));
		}
		foreach($model->getRawI18N() as $code => $value)
		{
			$dbValue[$code] = $value;
		}
		$dbValue[$model->getLang()] = $model->$name;
		return true;
	}

}
