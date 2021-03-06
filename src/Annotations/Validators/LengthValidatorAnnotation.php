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

namespace Maslosoft\Mangan\Annotations\Validators;

use Maslosoft\Addendum\Helpers\ParamsExpander;
use Maslosoft\Mangan\Meta\ValidatorMeta;
use Maslosoft\Mangan\Validators\Proxy\StringProxy;
use Maslosoft\Mangan\Validators\Traits\AllowEmpty;
use Maslosoft\Mangan\Validators\Traits\When;

/**
 * StringValidator validates that the attribute value is of certain length.
 *
 * Note, this validator should only be used with string-typed attributes.
 *
 * In addition to the {@link message} property for setting a custom error message,
 * StringValidator has a couple custom error messages you can set that correspond to different
 * validation scenarios. For defining a custom message when the string is too short,
 * you may use the {@link tooShort} property. Similarly with {@link tooLong}. The messages may contain
 * placeholders that will be replaced with the actual content. In addition to the "{attribute}"
 * placeholder, recognized by all validators (see {@link Validator}), StringValidator allows for the following
 * placeholders to be specified:
 * <ul>
 * <li>{min}: when using {@link tooShort}, replaced with minimum length, {@link min}, if set.</li>
 * <li>{max}: when using {@link tooLong}, replaced with the maximum length, {@link max}, if set.</li>
 * <li>{length}: when using {@link message}, replaced with the exact required length, {@link is}, if set.</li>
 * </ul>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.0
 */
class LengthValidatorAnnotation extends ValidatorAnnotation
{

	use AllowEmpty,
	  When;

	/**
	 * @var integer maximum length. Defaults to null, meaning no maximum limit.
	 */
	public $max = null;

	/**
	 * @var integer minimum length. Defaults to null, meaning no minimum limit.
	 */
	public $min = null;

	/**
	 * @var integer exact length. Defaults to null, meaning no exact length limit.
	 */
	public $is = null;

	/**
	 * @var string user-defined error message used when the value is too short.
	 */
	public $tooShort = null;

	/**
	 * @var string user-defined error message used when the value is too long.
	 */
	public $tooLong = null;

	/**
	 * @var string
	 */
	public $msgInvalid = '';

	/**
	 * @var string
	 */
	public $msgTooShort = '';

	/**
	 * @var string
	 */
	public $msgTooLong = '';

	/**
	 * @var string
	 */
	public $msgLength = '';

	/**
	 * @var string the encoding of the string value to be validated (e.g. 'UTF-8').
	 * This property is used only when mbstring PHP extension is enabled.
	 * The value of this property will be used as the 2nd parameter of the
	 * mb_strlen() function. If this property is not set, the application charset
	 * will be used.
	 * If this property is set false, then strlen() will be used even if mbstring is enabled.
	 * @since 1.1.1
	 */
	public $encoding = null;

	public function init()
	{
		$this->proxy = StringProxy::class;
		$this->getEntity()->validators[] = new ValidatorMeta(ParamsExpander::expand($this, [
					'max',
					'min',
					'is',
					'when',
					'tooShort',
					'tooLong',
					'msgInvalid',
					'msgTooShort',
					'msgTooLong',
					'msgLength',
					'allowEmpty',
					'encoding',
					'message',
					'skipOnError',
					'on',
					'safe',
					'except',
					'proxy'
		]));
	}

}
