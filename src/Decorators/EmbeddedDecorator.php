<?php

/**
 * This software package is licensed under New BSD license.
 *
 * @package maslosoft/mangan
 * @licence New BSD
 * @copyright Copyright (c) Piotr Masełkowski <pmaselkowski@gmail.com>
 * @copyright Copyright (c) Maslosoft
 * @copyright Copyright (c) Others as mentioned in code
 * @link http://maslosoft.com/mangan/
 */

namespace Maslosoft\Mangan\Decorators;

use Maslosoft\Mangan\Interfaces\IOwnered;
use Maslosoft\Mangan\Meta\DocumentPropertyMeta;
use Maslosoft\Mangan\Meta\ManganMeta;
use Maslosoft\Mangan\Transformers\ITransformator;

/**
 * EmbeddedDecorator
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class EmbeddedDecorator implements IDecorator
{

	public function read($model, $name, &$dbValue, $transformatorClass = ITransformator::class)
	{
		self::ensureClass($model, $name, $dbValue);
		$embedded = $transformatorClass::toModel($dbValue);
		if($embedded instanceof IOwnered)
		{
			$embedded->setOwner($model);
		}
		$model->$name = $embedded;
	}

	public function write($model, $name, &$dbValue, $transformatorClass = ITransformator::class)
	{
		$dbValue = $transformatorClass::fromModel($model->$name);
	}

	public static function ensureClass($model, $name, &$dbValue)
	{
		if (!array_key_exists('_class', $dbValue))
		{
			$meta = ManganMeta::create($model)->$name;
			/* @var $meta DocumentPropertyMeta */
			$dbValue['_class'] = $meta->embedded->class;
		}
	}
}
