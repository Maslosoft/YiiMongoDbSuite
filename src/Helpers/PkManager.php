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

namespace Maslosoft\Mangan\Helpers;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Mangan\Criteria;
use Maslosoft\Mangan\Exceptions\CriteriaException;
use Maslosoft\Mangan\Helpers\PkManager;
use Maslosoft\Mangan\Helpers\Sanitizer\Sanitizer;
use Maslosoft\Mangan\Meta\ManganMeta;
use MongoId;

/**
 * Primary key manager
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class PkManager
{

	/**
	 * Prepare multi pk criteria
	 * @param AnnotatedInterface $model
	 * @param mixed[] $pkValues
	 * @param Criteria|null $criteria
	 */
	public static function prepareAll($model, $pkValues, Criteria $criteria = null)
	{
		if (null === $criteria)
		{
			$criteria = new Criteria();
		}
		$conditions = [];
		foreach ($pkValues as $pkValue)
		{
			$c = PkManager::prepare($model, $pkValue);
			foreach ($c->getConditions() as $field => $value)
			{
				$conditions[$field][] = $value;
			}
		}
		foreach ($conditions as $field => $value)
		{
			$criteria->addCond($field, 'in', $value);
		}
		return $criteria;
	}

	/**
	 * Prepare pk criteria from user provided data
	 * @param AnnotatedInterface $model
	 * @param mixed|mixed[] $pkValue
	 * @return Criteria
	 * @throws CriteriaException
	 */
	public static function prepare(AnnotatedInterface $model, $pkValue)
	{
		$pkField = ManganMeta::create($model)->type()->primaryKey? : '_id';
		$criteria = new Criteria();

		if (is_array($pkField))
		{
			foreach ($pkField as $name)
			{
				if (!array_key_exists($name, $pkValue))
				{
					throw new CriteriaException(sprintf('Composite primary key field `%s` not specied for model `%s`, required fields: `%s`', $name, get_class($model), implode('`, `', $pkField)));
				}
				self::_prepareField($model, $name, $pkValue[$name], $criteria);
			}
		}
		else
		{
			self::_prepareField($model, $pkField, $pkValue, $criteria);
		}
		return $criteria;
	}

	/**
	 * Create pk criteria from model data
	 * @param AnnotatedInterface $model
	 * @return Criteria
	 */
	public static function prepareFromModel(AnnotatedInterface $model)
	{
		return self::prepare($model, self::getFromModel($model));
	}

	/**
	 * Get primary key from model
	 * @param AnnotatedInterface $model
	 * @return MongoId|mixed|mixed[]
	 */
	public static function getFromModel(AnnotatedInterface $model)
	{
		$pkField = ManganMeta::create($model)->type()->primaryKey? : '_id';
		$pkValue = [];
		$sanitizer = new Sanitizer($model);
		if (is_array($pkField))
		{
			foreach ($pkField as $name)
			{
				$pkValue[$name] = $sanitizer->write($name, $model->$name);
			}
		}
		else
		{
			$pkValue = $sanitizer->write($pkField, $model->$pkField);
		}
		return $pkValue;
	}

	/**
	 * Get pk criteria from raw array
	 * @param mixed[] $data
	 * @param AnnotatedInterface $model
	 * @return mixed[]
	 */
	public static function getFromArray($data, AnnotatedInterface $model)
	{
		$pkField = ManganMeta::create($model)->type()->primaryKey? : '_id';
		$pkValue = [];
		$sanitizer = new Sanitizer($model);
		if (is_array($pkField))
		{
			foreach ($pkField as $name)
			{
				$pkValue[$name] = $sanitizer->write($name, $data[$name]);
			}
		}
		else
		{
			$pkValue = $sanitizer->write($pkField, $data[$pkField]);
		}
		return $pkValue;
	}

	/**
	 * Apply pk value to model
	 * @param AnnotatedInterface $model
	 * @param MongoId|mixed|mixed[] $pkValue
	 * @return type
	 * @throws CriteriaException
	 */
	public static function applyToModel(AnnotatedInterface $model, $pkValue)
	{
		$pkField = ManganMeta::create($model)->type()->primaryKey? : '_id';
		$sanitizer = new Sanitizer($model);
		if (is_array($pkField))
		{
			foreach ($pkField as $name)
			{
				if (!array_key_exists($name, $pkValue))
				{
					throw new CriteriaException(sprintf('Composite primary key field `%s` not specied for model `%s`, required fields: `%s`', $name, get_class($model), implode('`, `', $pkField)));
				}
				$model->$name = $sanitizer->read($name, $pkValue[$name]);
			}
		}
		else
		{
			$model->$pkField = $sanitizer->read($pkField, $pkValue);
		}
		return $pkValue;
	}

	/**
	 * Compare primary keys. For both params primary keys values or models can be used.
	 * Example use:
	 * <pre>
	 * <code>
	 * $model = new Model();
	 * $pk = ['_id' => new MongoId()];
	 * PkManager::compare($model, $pk);
	 *
	 * $pk1 = ['keyOne' => 1, 'keyTwo' => 2];
	 * $pk2 = ['keyOne' => 1, 'keyTwo' => 2];;
	 * PkManager::compare($pk1, $pk2);
	 *
	 * $model1 = new Model();
	 * $model2 = new Model();
	 * PkManager::compare($model1, $model2);
	 *
	 * </code>
	 * </pre>
	 * @param AnnotatedInterface|mixed[] $source
	 * @param AnnotatedInterface|mixed[] $target
	 * @return boolean true if pk's points to same document
	 */
	public static function compare($source, $target)
	{
		$models = false;

		// Check if both params are models
		if ($source instanceof AnnotatedInterface && $target instanceof AnnotatedInterface)
		{
			// If different types return false
			if (!$source instanceof $target)
			{
				return false;
			}
			$models = true;
		}

		$src = self::_compareNormalize($source);
		$trg = self::_compareNormalize($target);

		// Different pk's
		if (count($src) !== count($trg))
		{
			return false;
		}

		// Different pk keys
		if (array_keys($src) !== array_keys($trg))
		{
			return false;
		}

		/**
		 * TODO Currently commented sanitizers. Investigate if it's nessesary.
		 */
		// Get sanitizer if one of params is model
		// NOTE: if both params are models,
		// ignore sanitizers as it's values are already sanitized by _compareNormalize()
//		if ($models)
//		{
//			if ($source instanceof AnnotatedInterface)
//			{
//				$sanitizer = new Sanitizer($source);
//			}
//			elseif ($target instanceof AnnotatedInterface)
//			{
//				$sanitizer = new Sanitizer($target);
//			}
//		}

		// Compare values
		foreach ($src as $name => $srcVal)
		{
			// This is safe as keys are checked previously
			$trgVal = $trg[$name];

			// Apply sanitizers
//			if ($sanitizer)
//			{
//				$srcVal = $sanitizer->read($name, $srcVal);
//				$trgVal = $sanitizer->read($name, $trgVal);
//			}

			// Special case for mongo id
			if ($srcVal instanceof \MongoId || $trgVal instanceof \MongoId)
			{
				if ((string) $srcVal !== (string) $trgVal)
				{
					return false;
				}
				continue;
			}

			// Finally compare values
			if ($srcVal !== $trgVal)
			{
				return false;
			}
		}

		return true;
	}

	private static function _compareNormalize($value)
	{
		if ($value instanceof AnnotatedInterface)
		{
			$value = self::getFromModel($value);
		}

		// Simple pk
		if(!is_array($value))
		{
			return [$value];
		}

		// Composite pk
		return $value;
	}

	/**
	 * Create pk criteria for single field
	 * @param AnnotatedInterface $model Model instance
	 * @param string $name
	 * @param mixed $value
	 * @param Criteria $criteria
	 */
	private static function _prepareField(AnnotatedInterface $model, $name, $value, Criteria &$criteria)
	{
		$sanitizer = new Sanitizer($model);
		$criteria->addCond($name, '==', $sanitizer->write($name, $value));
	}

}
