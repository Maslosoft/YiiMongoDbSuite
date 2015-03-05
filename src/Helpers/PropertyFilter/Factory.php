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

namespace Maslosoft\Mangan\Helpers\PropertyFilter;

use Maslosoft\Mangan\Mangan;
use Maslosoft\Mangan\Meta\DocumentPropertyMeta;
use Maslosoft\Mangan\Meta\DocumentTypeMeta;
use Maslosoft\Mangan\Transformers\Filters\Unfiltered;
use Maslosoft\Transformers\Filters\ITransformatorFilter;

/**
 * Factory
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Factory
{

	/**
	 * Filter instances
	 * @var ITransformatorFilter[][]
	 */
	private static $_configs = [];

	public static function create($transformatorClass, DocumentTypeMeta $documentMeta, DocumentPropertyMeta $fieldMeta)
	{
		$filterNames = self::getManganFilters($documentMeta->connectionId, $transformatorClass);
		if ($filterNames)
		{
			if(count($filterNames) > 1)
			{
				return new MultiFilter($filterNames);
			}
			return current($filterNames);
		}
		return new Unfiltered();
	}

	private static function getManganFilters($connectionId, $transformatorClass)
	{
		if(!isset(self::$_configs[$connectionId]))
		{
			self::$_configs[$connectionId] = [];
		}
		if (!isset(self::$_configs[$connectionId][$transformatorClass]))
		{
			self::$_configs[$connectionId] = [];
			self::$_configs[$connectionId][$transformatorClass] = [];
			$mangan = new Mangan($connectionId);
			$tranformator = new $transformatorClass;
			foreach ($mangan->filters as $implementer => $filterClasses)
			{
				foreach ($filterClasses as $filterClass)
				{
					if ($tranformator instanceof $implementer)
					{
						self::$_configs[$connectionId][$transformatorClass][] = new $filterClass;
					}
				}
			}
		}
		return self::$_configs[$connectionId][$transformatorClass];
	}

}
