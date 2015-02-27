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

namespace Maslosoft\Mangan\Annotations;

use Maslosoft\Mangan\Meta\ManganPropertyAnnotation;

/**
 * ToJson annotation
 * Use this annotation to ignore or include field when converting to JSON array
 * @Target('property')
 * @template ToJson(false)
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class ToJsonAnnotation extends ManganPropertyAnnotation
{

	public $value;

	public function init()
	{
		$this->_entity->toJson = (bool)$this->value;
	}

}