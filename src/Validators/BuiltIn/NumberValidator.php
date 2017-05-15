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

namespace Maslosoft\Mangan\Validators\BuiltIn;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Mangan\Interfaces\Validators\ValidatorInterface;
use Maslosoft\Mangan\Meta\ManganMeta;
use Maslosoft\Mangan\Validators\Traits\AllowEmpty;
use Maslosoft\Mangan\Validators\Traits\Messages;
use Maslosoft\Mangan\Validators\Traits\OnScenario;
use Maslosoft\Mangan\Validators\Traits\Safe;
use Maslosoft\Mangan\Validators\Traits\SkipOnError;

/**
 * NumberValidator
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class NumberValidator implements ValidatorInterface
{

	use AllowEmpty,
	  Messages,
	  OnScenario,
	  Safe,
	  SkipOnError;

	/**
	 * @var boolean whether the attribute value can only be an integer. Defaults to false.
	 */
	public $integerOnly = false;

	/**
	 * @var integer|float upper limit of the number. Defaults to null, meaning no upper limit.
	 */
	public $max = NULL;

	/**
	 * @var integer|float lower limit of the number. Defaults to null, meaning no lower limit.
	 */
	public $min = NULL;

	/**
	 * @var string user-defined error message used when the value is too big.
	 * @deprecated Use `msgTooSmall` instead
	 */
	public $tooBig = NULL;

	/**
	 * @var string user-defined error message used when the value is too small.
	 * @deprecated Use `msgTooBig` instead
	 */
	public $tooSmall = NULL;

	/**
	 * @Label('{attribute} must be a number')
	 * @var string
	 */
	public $msgNumber = '';

	/**
	 * @Label('{attribute} must be an integer')
	 * @var string
	 */
	public $msgInteger = '';

	/**
	 * @Label('{attribute} is too small (minimum is {min})')
	 * @var string
	 */
	public $msgTooSmall = '';

	/**
	 * @Label('{attribute} is too big (maximum is {max})')
	 * @var string
	 */
	public $msgTooBig = '';

	public function isValid(AnnotatedInterface $model, $attribute)
	{
		$value = $model->$attribute;
		if ($this->allowEmpty && empty($value))
		{
			return true;
		}

		$label = ManganMeta::create($model)->field($attribute)->label;
		if (!is_scalar($value))
		{
			$this->addError('msgNumber', ['{attribute}' => $label]);
			return false;
		}
		if (!is_numeric($value))
		{
			$this->addError('msgNumber', ['{attribute}' => $label]);
			return false;
		}
		if ($this->integerOnly)
		{

			if (!filter_var($value, FILTER_VALIDATE_INT))
			{
				$this->addError('msgInteger', ['{attribute}' => $label]);
				return false;
			}
		}
		else
		{
			if (!filter_var($value, FILTER_VALIDATE_FLOAT))
			{
				$this->addError('msgNumber', ['{attribute}' => $label]);
				return false;
			}
		}
		if ($this->min !== null && $value < $this->min)
		{
			if (!empty($this->tooSmall))
			{
				$this->addError($this->tooSmall, ['{min}' => $this->min, '{attribute}' => $label]);
				return false;
			}
			$this->addError('msgTooSmall', ['{min}' => $this->min, '{attribute}' => $label]);
			return false;
		}
		if ($this->max !== null && $value > $this->max)
		{
			if (!empty($this->tooBig))
			{
				$this->addError($this->tooBig, ['{max}' => $this->max, '{attribute}' => $label]);
			}
			$this->addError('msgTooBig', ['{max}' => $this->max, '{attribute}' => $label]);
			return false;
		}
		return true;
	}

}
