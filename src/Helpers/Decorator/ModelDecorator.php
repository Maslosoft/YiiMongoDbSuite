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

namespace Maslosoft\Mangan\Helpers\Decorator;

use Maslosoft\Mangan\Decorators\IDecorator;
use Maslosoft\Mangan\Helpers\Transformator;
use Maslosoft\Mangan\Meta\DocumentPropertyMeta;
use Maslosoft\Mangan\Meta\DocumentTypeMeta;

/**
 * ModelDecorator
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class ModelDecorator extends Transformator
{

	/**
	 * Read value from database
	 * @param mixed $dbValue
	 */
	public function read(&$dbValue)
	{
		$decorator = Factory::createForModel($this->getTransformatorClass(), $this->getMeta()->type());
		$decorator->read($this->getModel(), $dbValue, $this->getTransformatorClass());
	}

	/**
	 * Write value into database
	 * @param mixed $dbValue
	 */
	public function write(&$dbValue)
	{
		$decorator = Factory::createForModel($this->getTransformatorClass(), $this->getMeta()->type());
		$decorator->write($this->getModel(), $dbValue, $this->getTransformatorClass());
	}

	/**
	 * Get transformer
	 * @param type $transformatorClass
	 * @param DocumentTypeMeta $modelMeta
	 * @param DocumentPropertyMeta $meta
	 * @return IDecorator
	 */
	protected function _getTransformer($transformatorClass, DocumentTypeMeta $modelMeta, DocumentPropertyMeta $meta)
	{
		return Factory::createForModel($transformatorClass, $modelMeta);
	}

}