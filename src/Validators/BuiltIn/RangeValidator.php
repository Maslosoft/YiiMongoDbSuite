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

use InvalidArgumentException;
use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Mangan\Interfaces\Validators\ValidatorInterface;
use Maslosoft\Mangan\Meta\ManganMeta;
use Maslosoft\Mangan\Validators\Traits\AllowEmpty;
use Maslosoft\Mangan\Validators\Traits\Messages;
use Maslosoft\Mangan\Validators\Traits\OnScenario;
use Maslosoft\Mangan\Validators\Traits\Safe;
use Maslosoft\Mangan\Validators\Traits\SkipOnError;
use Maslosoft\Mangan\Validators\Traits\Strict;

/**
 * RangeValidator
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class RangeValidator implements ValidatorInterface
{

	use AllowEmpty,
	  Messages,
	  Strict,
	  OnScenario,
	  Safe,
	  SkipOnError;

	/**
	 * @var array list of valid values that the attribute value should be among
	 */
	public $range;

	/**
	 * @var boolean whether to invert the validation logic. Defaults to false. If set to true,
	 * the attribute value should NOT be among the list of values defined via {@link range}.
	 * @since 1.1.5
	 * */
	public $not = false;

	/**
	 * @Label('{attribute} is not in the list')
	 * @var string
	 */
	public $msgIsNot = '';

	/**
	 * @Label('{attribute} is in the list')
	 * @var string
	 */
	public $msgIs = '';

	public function isValid(AnnotatedInterface $model, $attribute)
	{
		$value = $model->$attribute;
		if ($this->allowEmpty && empty($value))
		{
			return true;
		}
		if (!is_array($this->range))
		{
			$msg = sprintf('The "range" property must be specified with a list of values on attribute `%s` of model `%s`', $attribute, get_class($model));
			throw new InvalidArgumentException($msg);
		}
		$result = false;
		if ($this->strict)
		{
			$result = in_array($value, $this->range, true);
		}
		else
		{
			foreach ($this->range as $r)
			{
				$result = $r === '' || $value === '' ? $r === $value : $r == $value;
				if ($result)
				{
					break;
				}
			}
		}
		$label = ManganMeta::create($model)->field($attribute)->label;
		if (!$this->not && !$result)
		{
			$this->addError('msgIsNot', ['{attribute}' => $label]);
			return false;
		}
		elseif ($this->not && $result)
		{
			$this->addError('msgIs', ['{attribute}' => $label]);
			return false;
		}
		return true;
	}

}
