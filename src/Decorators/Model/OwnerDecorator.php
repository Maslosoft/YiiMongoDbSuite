<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Mangan\Decorators\Model;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Mangan\Interfaces\Decorators\Model\ModelDecoratorInterface;
use Maslosoft\Mangan\Interfaces\OwneredInterface;
use Maslosoft\Mangan\Interfaces\Transformators\TransformatorInterface;
use Maslosoft\Mangan\Meta\DocumentPropertyMeta;
use Maslosoft\Mangan\Meta\ManganMeta;

/**
 * Apply owners
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class OwnerDecorator implements ModelDecoratorInterface
{

	/**
	 * This will be called when getting value.
	 * This should return end user value.
	 * @param AnnotatedInterface $model Document model which will be decorated
	 * @param mixed $dbValues
	 * @param string $transformatorClass Transformator class used
	 * @return bool Return true if value should be assigned to model
	 */
	public function read($model, &$dbValues, $transformatorClass = TransformatorInterface::class)
	{
		foreach (ManganMeta::create($model)->properties('owned') as $name => $metaProperty)
		{
			/* @var $metaProperty DocumentPropertyMeta */
			if (!isset($model->$name))
			{
				continue;
			}
			if ($model->$name instanceof OwneredInterface)
			{
				$model->$name->setOwner($model);
			}
			if (is_array($model->$name))
			{
				foreach ($model->$name as $document)
				{
					if ($document instanceof OwneredInterface)
					{
						$document->setOwner($model);
					}
				}
			}
		}
	}

	/**
	 * This will be called when setting value.
	 * This should return db acceptable value
	 * @param AnnotatedInterface $model Model which is about to be decorated
	 * @param mixed[] $dbValues Whole model values from database. This is associative array with keys same as model properties (use $name param to access value). This is passed by reference.
	 * @param string $transformatorClass Transformator class used
	 * @return bool Return true to store value to database
	 */
	public function write($model, &$dbValues, $transformatorClass = TransformatorInterface::class)
	{

	}

}
