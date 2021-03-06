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

namespace Maslosoft\Mangan\Transformers\Filters;

use Maslosoft\Mangan\EntityManager;
use Maslosoft\Mangan\Interfaces\Filters\Property\TransformatorFilterInterface;
use Maslosoft\Mangan\Meta\DocumentPropertyMeta;

/**
 * This filter is intended to mark attribute as eligible for mass assignment via EntityManager::setAttributes()
 * @see EntityManager::setAttributes()
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class SafeFilter implements TransformatorFilterInterface
{

	public function fromModel($model, DocumentPropertyMeta $fieldMeta)
	{
		return true;
	}

	public function toModel($model, DocumentPropertyMeta $fieldMeta)
	{
		// Unless marked as safe allow set
		if ($fieldMeta->safe === null)
		{
			return true;
		}
		return $fieldMeta->safe;
	}

}
