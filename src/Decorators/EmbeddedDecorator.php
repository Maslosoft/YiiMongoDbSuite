<?php

/**
 * This software package is licensed under AGPL or Commercial license.
 *
 * @package maslosoft/mangan
 * @licence AGPL or Commercial
 * @copyright Copyright (c) Piotr Masełkowski <pmaselkowski@gmail.com>
 * @copyright Copyright (c) Maslosoft
 * @copyright Copyright (c) Others as mentioned in code
 * @link http://maslosoft.com/mangan/
 */

namespace Maslosoft\Mangan\Decorators;

use Maslosoft\Mangan\Events\ClassNotFound;
use Maslosoft\Mangan\Events\Event;
use Maslosoft\Mangan\Exceptions\ManganException;
use Maslosoft\Mangan\Helpers\NotFoundResolver;
use Maslosoft\Mangan\Interfaces\Decorators\Property\DecoratorInterface;
use Maslosoft\Mangan\Interfaces\Transformators\TransformatorInterface;
use Maslosoft\Mangan\Meta\DocumentPropertyMeta;
use Maslosoft\Mangan\Meta\ManganMeta;

/**
 * EmbeddedDecorator
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class EmbeddedDecorator implements DecoratorInterface
{

	public function read($model, $name, &$dbValue, $transformatorClass = TransformatorInterface::class)
	{
		self::ensureClass($model, $name, $dbValue);
		$embedded = $transformatorClass::toModel($dbValue, $model->$name, $model->$name);
		$model->$name = $embedded;
	}

	public function write($model, $name, &$dbValue, $transformatorClass = TransformatorInterface::class)
	{
		if (null === $model->$name)
		{
			$fieldMeta = ManganMeta::create($model)->$name;
			/* @var $fieldMeta DocumentPropertyMeta */
			$className = $fieldMeta->embedded->class;
			if (!is_string($className))
			{
				return null;
			}
			$dbValue[$name] = $transformatorClass::fromModel(new $className);
			return;
		}
		$dbValue[$name] = $transformatorClass::fromModel($model->$name);
	}

	public static function ensureClass($model, $name, &$dbValue)
	{
		if (!is_array($dbValue) || !array_key_exists('_class', $dbValue))
		{
			$fieldMeta = ManganMeta::create($model)->$name;
			/* @var $fieldMeta DocumentPropertyMeta */

			$class = $fieldMeta->embedded->class;
		}
		else
		{
			$class = $dbValue['_class'];
		}
		if (!class_exists($class))
		{
			$event = new ClassNotFound($model);
			$event->notFound = $class;
			if (Event::hasHandler($model, NotFoundResolver::EventClassNotFound) && Event::handled($model, NotFoundResolver::EventClassNotFound, $event))
			{
				$class = $event->replacement;
			}
			else
			{
				throw new ManganException(sprintf("Embedded model class `%s` not found in model `%s` field `%s`", $class, get_class($model), $name));
			}
		}
		$dbValue['_class'] = $class;
	}

}
