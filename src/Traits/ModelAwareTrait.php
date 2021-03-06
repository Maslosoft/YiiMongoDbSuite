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

namespace Maslosoft\Mangan\Traits;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;

/**
 * ModelAwareTrait
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
trait ModelAwareTrait
{

	/**
	 * Instance of model
	 * @Ignored
	 * @Persistent(false)
	 * @var AnnotatedInterface
	 * @since v1.0
	 */
	public $model;

	/**
	 * Get model used by this data provider
	 * @Ignored
	 * @return AnnotatedInterface
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Set model
	 * @Ignored
	 * @param AnnotatedInterface $model
	 * @return static
	 */
	public function setModel(AnnotatedInterface $model)
	{
		$this->model = $model;
		return $this;
	}

}
