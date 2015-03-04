<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Mangan\Transformers;

use Maslosoft\Addendum\Interfaces\IAnnotated;
use Maslosoft\Mangan\Exceptions\TransformatorException;
use Maslosoft\Mangan\Helpers\Decorator\Decorator;
use Maslosoft\Mangan\Helpers\PropertyFilter\Filter;
use Maslosoft\Mangan\Helpers\Sanitizer\Sanitizer;
use Maslosoft\Mangan\Interfaces\IModel;
use Maslosoft\Mangan\Meta\ManganMeta;

/**
 * Transformer
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
abstract class Transformer
{

	/**
	 * Returns the given object as an associative array
	 * @param IModel|object $model
	 * @param bool $withClassName Whenever to include special _class field
	 * @param string[] $fields Fields to transform
	 * @return array an associative array of the contents of this object
	 */
	public static function fromModel($model, $withClassName = true, $fields = [])
	{
		$meta = ManganMeta::create($model);
		$decorator = new Decorator($model, get_called_class());
		$sanitizer = new Sanitizer($model);
		$filter = new Filter($model, get_called_class());
		$arr = [];
		foreach ($meta->fields() as $name => $fieldMeta)
		{
			if($fields && !in_array($name, $fields))
			{
				continue;
			}
			if (!$filter->fromModel($model, $meta->$name))
			{
				continue;
			}
			$model->$name = $sanitizer->write($name, $model->$name);
			$decorator->write($name, $arr);
		}
		/**
		 * TODO This should be handled by decorator
		 */
		if ($withClassName)
		{
			$arr['_class'] = get_class($model);
		}
		return $arr;
	}

	/**
	 * Create document from array
	 * TODO Enforce $className if collection is homogenous
	 * @param mixed[] $data
	 * @param stirng|object $className
	 * @param IAnnotated $instance
	 * @return IAnnotated
	 * @throws TransformatorException
	 */
	public static function toModel($data, $className = null, $instance = null)
	{
		if (!$data)
		{
			return null;
		}
		if (is_object($className))
		{
			$className = get_class($className);
		}
		if (!$className)
		{
			if (array_key_exists('_class', $data))
			{
				$className = $data['_class'];
			}
			else
			{
				throw new TransformatorException('Could not determine document type');
			}
		}
		return self::_toDocument($className, $data, $instance);
	}

	private static function _toDocument($className, $data, $instance)
	{
		if ($instance)
		{
			$model = $instance;
		}
		else
		{
			$model = new $className;
		}
		$meta = ManganMeta::create($model);
		$decorator = new Decorator($model, get_called_class());
		$sanitizer = new Sanitizer($model);
		$filter = new Filter($model, get_called_class());
		foreach ($data as $name => $value)
		{
			$fieldMeta = $meta->$name;
			/* @var \Maslosoft\Mangan\Meta\DocumentPropertyMeta $fieldMeta */
			if (!$fieldMeta)
			{
				continue;
			}
			if (!$filter->toModel($model, $fieldMeta))
			{
				continue;
			}
			$decorator->read($name, $value);
			$model->$name = $sanitizer->read($name, $model->$name);
		}
		return $model;
	}

}
