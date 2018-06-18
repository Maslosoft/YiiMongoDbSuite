<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 07.03.18
 * Time: 17:46
 */

namespace Maslosoft\Mangan\Helpers;


use Countable;
use Iterator;
use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Addendum\Utilities\ClassChecker;
use Maslosoft\Mangan\Mangan;
use Maslosoft\Mangan\Meta\ManganMeta;

/**
 * Iterate over composition of documents.
 *
 * NOTE: This will include only AnnotatedInterface instances.
 *
 * @package Maslosoft\Mangan\Helpers
 */
class CompositionIterator implements Iterator, Countable
{
	private $model = null;

	private $direct = false;

	private $types = [];

	/**
	 * Models holder
	 * @var null|AnnotatedInterface[]
	 */
	private $models = null;

	private $pointer = 0;

	public function __construct(AnnotatedInterface $model)
	{
		$this->model = $model;
	}

	/**
	 * Limit results to only direct descendants.
	 * @return $this
	 */
	public function direct()
	{
		$this->direct = true;
		return $this;
	}

	/**
	 * Limit results to only to the type provided.
	 *
	 * The `$type` should be class or interface name
	 * or object instance.
	 *
	 * Repeated calls will add types uniquely.
	 *
	 * @param $type string|object
	 * @param $include boolean Whether to include this type or skip
	 * @return $this
	 */
	public function ofType($type, $include = true)
	{
		if (is_object($type))
		{
			$type = get_class($type);
		}
		assert(is_string($type));
		assert(ClassChecker::exists($type));
		$this->types[$type] = $include;
		return $this;
	}

	private function init()
	{
		if (null === $this->models)
		{
			$this->models = [];
			$this->iterate($this->model);
		}
	}

	private function iterate($model)
	{
		foreach (ManganMeta::create($model)->fields() as $name => $meta)
		{
			if (is_array($model->$name))
			{
				foreach ($model->$name as $child)
				{
					if ($this->skip($child))
					{
						continue;
					}
					if ($this->doInclude($child))
					{
						$this->models[] = $child;
					}
					if($this->recurse())
					{
						$this->iterate($child);
					}
				}
				continue;
			}
			if ($this->skip($model->$name))
			{
				continue;
			}
			if ($this->doInclude($model->$name))
			{
				$this->models[] = $model->$name;
			}
			if($this->recurse())
			{
				$this->iterate($model->$name);
			}
		}
	}

	private function skip($model)
	{
		// Non-object
		if (!is_object($model))
		{
			return true;
		}
		// Skip if not annotated
		if (!$model instanceof AnnotatedInterface)
		{
			return true;
		}
		return false;
	}

	/**
	 * Whether to include `$model` in result
	 * @param $model
	 * @return bool
	 */
	private function doInclude($model)
	{
		// Don't skip if no types
		if (empty($this->types))
		{
			return true;
		}

		// Include if is_a type
		foreach ($this->types as $type => $include)
		{
			if (is_a($model, $type))
			{
				return $include;
			}
		}
		return false;
	}

	private function recurse()
	{
		return !$this->direct;
	}

	public function current()
	{
		$this->init();
		return $this->models[$this->pointer];
	}

	public function next()
	{
		$this->init();
		++$this->pointer;
	}

	public function key()
	{
		$this->init();
		return $this->pointer;
	}

	public function valid()
	{
		$this->init();
		return isset($this->models[$this->pointer]);
	}

	public function rewind()
	{
		$this->init();
		$this->pointer = 0;
	}

	public function count()
	{
		$this->init();
		return count($this->models);
	}


}