<?php

/**
 * This software package is licensed under AGPL or Commercial license.
 *
 * @package maslosoft/mangan
 * @licence AGPL or Commercial
 * @copyright Copyright (c) Piotr Masełkowski <pmaselkowski@gmail.com>
 * @copyright Copyright (c) Maslosoft
 * @copyright Copyright (c) Others as mentioned in code
 * @link https://maslosoft.com/mangan/
 */

namespace Maslosoft\Mangan\Decorators\Model;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Mangan\Interfaces\Decorators\Model\ModelDecoratorInterface;
use Maslosoft\Mangan\Interfaces\Transformators\TransformatorInterface;

/**
 * ClassNameDecorator
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class ClassNameDecorator implements ModelDecoratorInterface
{

	/**
	 * This will be called when getting value.
	 * This should return end user value.
	 * @param AnnotatedInterface $model Document model which will be decorated
	 * @param mixed $dbValues
	 * @param string $transformatorClass Transformator class used
	 */
	public function read($model, &$dbValues, $transformatorClass = TransformatorInterface::class)
	{

	}

	/**
	 * This will be called when setting value.
	 * This should return db acceptable value
	 * @param AnnotatedInterface $model Model which is about to be decorated
	 * @param mixed[] $dbValues Whole model values from database. This is associative array with keys same as model properties (use $name param to access value). This is passed by reference.
	 * @param string $transformatorClass Transformator class used
	 */
	public function write($model, &$dbValues, $transformatorClass = TransformatorInterface::class)
	{
		// NOTE: Class should also be stored for homogeneous
		// collections, so that collection could be transformed
		// to non-homogeneous later.
		$class = get_class($model);
		$dbValues['_class'] = $class;
		if(isset($model->_class))
		{
			$model->_class = $class;
		}
	}

}
