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

namespace Maslosoft\Mangan\Sanitizers;

use Maslosoft\Mangan\Interfaces\Sanitizers\Property\ISanitizer;
use MongoId;

/**
 * MongoObjectId
 * This sanitizer forces MongoId type for both client and mongo
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class MongoObjectId implements ISanitizer
{

	public function read($model, $dbValue)
	{
		return $this->_cast($dbValue);
	}

	public function write($model, $phpValue)
	{
		return $this->_cast($phpValue);
	}

	protected function _cast($value)
	{
		if (!$value instanceof MongoId)
		{
			if (is_array($value) && isset($value['$id']))
			{
				$value = $value['$id'];
			}
			if (is_object($value) && isset($value->{'$id'}))
			{
				$value = $value->{'$id'};
			}
			$value = new MongoId($value);
		}
		return $value;
	}

}
