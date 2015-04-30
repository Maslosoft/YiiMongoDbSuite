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

namespace Maslosoft\Mangan\Annotations\Validators;

use Maslosoft\Addendum\Helpers\ParamsExpander;
use Maslosoft\Mangan\Meta\ValidatorMeta;

/**
 * NOTE: This class is automatically generated from Yii validator class.
 * This is not actual validator. For validator class @see CInValidator.
 */

/**
 * CRangeValidator validates that the attribute value is among the list (specified via {@link range}).
 * You may invert the validation logic with help of the {@link not} property (available since 1.1.5).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.0
 */
class InValidatorAnnotation extends ValidatorAnnotation
{

	/**
	 * @var array list of valid values that the attribute value should be among
	 */
	public $range = NULL;

	/**
	 * @var boolean whether the comparison is strict (both type and value must be the same)
	 */
	public $strict = false;

	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty = true;

	/**
	 * @var boolean whether to invert the validation logic. Defaults to false. If set to true,
	 * the attribute value should NOT be among the list of values defined via {@link range}.
	 * @since 1.1.5
	 * */
	public $not = false;

	public function init()
	{
		$this->_entity->validators[] = new ValidatorMeta(ParamsExpander::expand($this, [
			'range',
			'strict',
			'allowEmpty',
			'not',
			'message',
			'skipOnError',
			'on',
			'safe',
			'enableClientValidation',
			'except',
			'proxy'
		]));
	}
}