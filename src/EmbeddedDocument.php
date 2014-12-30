<?php

/**
 * @author Ianaré Sévi
 * @author Dariusz Górecki <darek.krk@gmail.com>
 * @author Invenzzia Group, open-source division of CleverIT company http://www.invenzzia.org
 * @author Piotr Maselkowski, Maslosoft
 * @copyright 2011 CleverIT http://www.cleverit.com.pl
 * @copyright 2013 Maslosoft http://maslosoft.com
 * @license New BSD license
 * @version 2.0.1
 * @category ext
 * @package maslosoft/yii-mangan
 */

namespace Maslosoft\Mangan;

use Maslosoft\Addendum\Collections\Meta;
use Maslosoft\Addendum\Interfaces\IAnnotated;
use Maslosoft\Mangan\Core\Component;
use Maslosoft\Mangan\Events\ClassNotFound;
use Maslosoft\Mangan\Events\ModelEvent;
use Maslosoft\Mangan\Helpers\Sanitizer\Sanitizer;
use Maslosoft\Mangan\Model\Model;
use MongoId;
use RuntimeException;
use stdClass;
use UnexpectedValueException;
use Yii;

/**
 * EmbeddedDocument
 *
 * @since v1.0.8
 * @property Meta $meta Model metadata
 */
abstract class EmbeddedDocument extends Model implements IAnnotated
{

	/**
	 * This holds key for document order
	 * @SafeValidator
	 * @var string
	 */
	public $_key = '';

	/**
	 * This holds type of this embedded document
	 * @SafeValidator
	 * @var string
	 */
	public $_class = null;

	/**
	 * Hold down owner pointer (if any)
	 *
	 * @var EmbeddedDocument $_owner
	 * @since v1.0.8
	 */
	protected $_owner = null;

	/**
	 * @todo Check if it is nessesary
	 * @var type
	 */
	private static $_attributes = [];

	/**
	 * Model metadata
	 * @Persistent(false)
	 * @var Meta
	 */
	public $meta = null;

	/**
	 * Current document language
	 * @var string
	 */
	private $_lang = '';

	/**
	 * Array with raw i18n attributes (with all language versions)
	 * @Persistent(false)
	 * @Readonly
	 * @var mixed[]
	 */
	public $rawI18N = null;

	/**
	 * Array with all not directly accessed fields values
	 * @var mixed[]
	 */
	private $_virtualValues = [];

	/**
	 * Sanitizer instance
	 * @Persistent(false)
	 * @var Sanitizer
	 */
	private $_sanitizer = null;

	/**
	 * Constructor.
	 * @param string $scenario name of the scenario that this model is used in.
	 * See {@link Model::scenario} on how scenario is used by models.
	 * @see getScenario
	 * @since v1.0.8
	 */
	public function __construct($scenario = 'insert', $lang = '')
	{
		$this->_class = get_class($this);

		$this->meta = Meta::create($this);
		$this->meta->initModel($this);

		$this->_sanitizer = new Sanitizer($this);
		
		$this->setLang($lang);
		$this->setScenario($scenario);
		$this->init();
		$this->attachBehaviors($this->behaviors());
		$this->afterConstruct();

		$this->initEmbeddedDocuments();
	}

	/**
	 * Initializes this model.
	 * This method is invoked in the constructor right after {@link scenario} is set.
	 * You may override this method to provide code that is needed to initialize the model (e.g. setting
	 * initial property values.)
	 * @since 1.0.8
	 */
	public function init()
	{

	}

// <editor-fold defaultstate="collapsed" desc="Events">
	/**
	 * @since v1.0.8
	 */
	public function onBeforeEmbeddedDocsInit($event)
	{
		$this->raiseEvent('onBeforeEmbeddedDocsInit', $event);
	}

	/**
	 * @since v1.0.8
	 */
	public function onAfterEmbeddedDocsInit($event)
	{
		$this->raiseEvent('onAfterEmbeddedDocsInit', $event);
	}

	/**
	 * @since v1.0.8
	 */
	public function onBeforeToArray($event)
	{
		$this->raiseEvent('onBeforeToArray', $event);
	}

	/**
	 * @since v1.0.8
	 */
	public function onAfterToArray($event)
	{
		$this->raiseEvent('onAfterToArray', $event);
	}

	public function onClassNotFound($event)
	{
		$this->raiseEvent(__FUNCTION__, $event);
	}

	/**
	 * @since v1.0.8
	 */
	protected function beforeToArray()
	{
		$event = new ModelEvent($this);
		$this->onBeforeToArray($event);
		return $event->isValid;
	}

	/**
	 * @since v1.0.8
	 */
	protected function afterToArray()
	{
		$this->onAfterToArray(new ModelEvent($this));
	}

	/**
	 * @since v1.0.8
	 */
	protected function beforeEmbeddedDocsInit()
	{
		$event = new ModelEvent($this);
		$this->onBeforeEmbeddedDocsInit($event);
		return $event->isValid;
	}

	/**
	 * @since v1.0.8
	 */
	protected function afterEmbeddedDocsInit()
	{
		$this->onAfterEmbeddedDocsInit(new ModelEvent());
	}

	/**
	 * Embedded class not found event handling
	 * @param string $className
	 * @return string
	 */
	protected function classNotfound($className)
	{
		$event = new ClassNotFound();
		$event->notFound = $className;
		$this->onClassNotFound($event);
		return $event->replacement;
	}

	/**
	 * This ensures that embedded documents are also validated
	 * @since v1.0.8
	 */
	public function afterValidate()
	{
		if ($this->hasEmbeddedDocuments())
		{
			foreach ($this->meta->properties('embedded') as $field => $className)
			{
				if ($this->meta->$field->embeddedArray)
				{
					foreach ((array) $this->$field as $doc)
					{
						if ($doc instanceof EmbeddedDocument)
						{
							if (!$doc->validate())
							{
								$this->addErrors($doc->getErrors());
							}
						}
					}
				}
				else
				{
					if ($this->$field instanceof EmbeddedDocument)
					{
						if (!$this->$field->validate())
						{
							$this->addErrors($this->$field->getErrors());
						}
					}
				}
			}
		}
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Magic methods">
	/**
	 * Support for get accessors for fields
	 * Also dot notation is supported for embedded documents, which can be used
	 * while getting fields with variable variables
	 * @example Fieldname: testField; get method: getTestField;
	 * @param string $name
	 * @return mixed result of get<fieldName> function
	 */
	public function __get($name)
	{
		if (strstr($name, '.'))
		{
			$parts = explode('.', $name, 2);
			return $this->{$parts[0]}->{$parts[1]};
		}
		$meta = $this->meta->$name;
		if ($meta)
		{
			if ($meta->callGet)
			{
				return $this->{$meta->methodGet}();
			}
			return $this->getAttribute($name);
		}
		return parent::__get($name);
	}

	/**
	 * Support for set accessors for fields
	 * Also dot notation is supported for embedded documents, which can be used
	 * while getting fields with variable variables
	 * @example Fieldname: testField; set method: setTestField;
	 * @param string $name
	 * @param mixed $value
	 * @return mixed result of get<fieldName> function
	 */
	public function __set($name, $value)
	{
		if (strstr($name, '.'))
		{
			$parts = explode('.', $name, 2);
			$this->{$parts[0]}->{$parts[1]} = $value;
			return $this->{$parts[0]}->{$parts[1]} = $value;
		}
		$meta = $this->meta->$name;
		if ($meta)
		{
			if ($meta->readonly)
			{
				return '';
			}
			if ($meta->callSet)
			{
				return $this->{$meta->methodSet}($value);
			}
			return $this->setAttribute($name, $value);
		}
		return parent::__set($name, $value);
	}

	/**
	 * @since v1.3.2
	 * @see Component::__isset()
	 */
	public function __isset($name)
	{
		if (!empty($this->meta->$name->embedded))
		{
			return isset($this->_virtualValues[$name]);
		}
		else
		{
			return parent::__isset($name);
		}
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Default model implementation">
	/**
	 * Validation rules based on validator annotations
	 * @return mixed[][]
	 */
	public function rules()
	{
		$pattern = '~Validator$~';
		$result = [];
		foreach ($this->meta->fields() as $field => $meta)
		{
			foreach ($meta as $type => $value)
			{
				if (preg_match($pattern, $type))
				{
					$type = preg_replace($pattern, '', $type);
					$value = (array) $value;
					if (isset($value['class']))
					{
						$type = $value['class'];
						unset($value['class']);
					}
					$result[] = array_merge([$field, $type], $value);
				}
			}
		}
		return $result;
	}


	/**
	 * Returns the list of attribute names.
	 * By default, this method returns all public properties of the class.
	 * You may override this method to change the default.
	 * @return array list of attribute names. Defaults to all public properties of the class.
	 * @since v1.0.8
	 */
	public function attributeNames()
	{
		if (!isset(self::$_attributes[$className]))
		{
			return self::$_attributes[$className] = array_keys((array) $this->meta->fields());
		}
		else
		{
			return self::$_attributes[$className];
		}
	}

	public function attributeLabels()
	{
		$labels = [];
		foreach ($this->meta->properties('label') as $key => $label)
		{
			$labels[$key] = Yii::t('', $label);
		}
		return $labels;
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Array convertion">
	/**
	 * Returns the given object as an associative array
	 * Fires beforeToArray and afterToArray events
	 * @return array an associative array of the contents of this object
	 * @since v1.0.8
	 */
	public function toArray($associative = true)
	{
		if ($this->beforeToArray())
		{
			$arr = $this->_toArray($associative);
			$this->afterToArray();
			return $arr;
		}
		else
		{
			return [];
		}
	}

	/**
	 * This method does the actual convertion to an array
	 * Does not fire any events
	 * @return array an associative array of the contents of this object
	 * @since v1.3.4
	 */
	protected function _toArray($associative = true)
	{
		$arr = [];
		foreach ($this->meta->fields() as $name => $field)
		{
			// Type check is required here, so by default attribute is persistent
			if ($field->persistent !== false)
			{
				if ($field->i18n)
				{
					foreach (Yii::app()->languages as $lang => $langName)
					{
						$arr[$name][$lang] = $this->_attributeToArray($field, $name, $lang, $associative);
					}
				}
				else
				{
					$arr[$name] = $this->_attributeToArray($field, $name, null, $associative);
				}
			}
		}
		$arr['_class'] = $this->_class;
		return $arr;
	}

	protected function _attributeToArray($field, $name, $lang, $associative = true)
	{
		if ($field->embedded)
		{
			if ($field->embeddedArray)
			{
				$value = [];
				foreach ((array) $this->getAttribute($name, $lang) as $key => $docValue)
				{
					if (!$docValue instanceof Model)
					{
						continue;
					}
					if (!$docValue->_key)
					{
						$docValue->_key = (string) new MongoId();
					}
					$key = $docValue->_key;
					if (method_exists($docValue, 'toArray'))
					{
						$value[$key] = $docValue->toArray();
					}
					else
					{
						$value[$key] = $docValue->attributes;
					}
					if (!$associative)
					{
						$value = array_values($value);
					}
				}
			}
			else
			{
				$value = $this->getAttribute($name, $lang);
				if ($value instanceof Model)
				{
					if (method_exists($value, 'toArray'))
					{
						$value = $value->toArray();
					}
					else
					{
						$value = $value->attributes;
					}
				}
				else
				{
					$value = $field->default;
				}
			}
		}
		else
		{
			$value = $this->getAttribute($name, $lang);
		}
		return $value;
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Attributes handling">

	/**
	 * Get raw attribute, as is stored in db
	 * @param string $name
	 * @param string $lang Language of sttribute, or _all to get all languages as array
	 * @todo $lang param _all is experimental, do not use
	 * @return mixed
	 */
	public function getAttribute($name, $lang = '')
	{
		$meta = $this->meta->$name;
		/**
		 * FIXME For some reason _sanitizer is unset
		 */
		$this->_sanitizer = new Sanitizer($this);

		if (!$meta->direct)
		{
			if (!$lang)
			{
				$lang = $this->getLang();
			}
			// Init defaults
			if (!isset($this->_virtualValues[$name]) || ($meta->i18n && !isset($this->_virtualValues[$name][$lang])))
			{
				$value = $meta->default;
				if ($meta->embedded)
				{
					if ($meta->embeddedArray)
					{
						// Let document designer set default value, do NOT enforce empty array
						// $value = [];
					}
					else
					{
						$value = $this->_instantiateEmbedded($name);
					}
				}
				if ($meta->i18n)
				{
					$this->_virtualValues[$name][$lang] = $value;
				}
				else
				{
					$this->_virtualValues[$name] = $value;
				}
			}
			// Return value
			if ($meta->i18n)
			{
				return $this->_virtualValues[$name][$lang];
			}
			else
			{
				return $this->_virtualValues[$name];
			}
		}
		else
		{
			return $this->$name;
		}
	}

	/**
	 * Set raw attribute
	 * @param string $name
	 * @param mixed $value
	 * @param string $lang Language of sttribute, or _all to set all languages as array
	 * @todo $lang param _all is experimental, do not use
	 */
	public function setAttribute($name, $value, $lang = '')
	{
		$meta = $this->meta->$name;
		/**
		 * FIXME For some reason _sanitizer is unset
		 */
		$this->_sanitizer = new Sanitizer($this);
		if (!$meta->direct)
		{
			if ($meta->embedded)
			{
				if ($meta->embeddedArray)
				{
					$docs = [];
					foreach ((array) $value as $key => $docValue)
					{
						if ($docValue === null || $docValue === 'null' || $docValue == 'undefined' || $docValue === '')
						{
							continue;
						}
						if (empty($docValue['_key']))
						{
							$docValue['_key'] = (string) new MongoId();
						}
						$key = $docValue['_key'];
						$doc = $this->_instantiateEmbedded($name, $key, $docValue);
						$docs[$key] = $doc;
					}
					$value = $docs;
				}
				else
				{
					$value = $this->_instantiateEmbedded($name, null, $value);
				}
			}
			if ($meta->i18n)
			{
				if (!$lang)
				{
					$lang = $this->getLang();
				}
				if ($lang == '_all')
				{
					$this->_virtualValues[$name] = $value;
				}
				else
				{
					$this->_virtualValues[$name][$lang] = $value;
				}
			}
			else
			{
				$this->_virtualValues[$name] = $value;
			}
		}
		else
		{
			$this->$name = $value;
		}
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Getters and setters">

	/**
	 * Get current language code, defaults to Yii::app()->language
	 * @return string
	 */
	public function getLang()
	{
		if (!$this->_lang)
		{
			$this->_lang = Yii::app()->language;
		}
		if ($this->_owner)
		{
			return $this->_owner->getLang();
		}
		return $this->_lang;
	}

	/**
	 * Set current language, but only if it is defined in application languages
	 * @param string $value language code
	 */
	public function setLang($value)
	{
		if (!$value)
		{
			$value = Yii::app()->language;
		}
		if (in_array($value, array_keys(Yii::app()->languages)))
		{
			$this->_lang = $value;
		}
		return $this;
	}

	public function getRawI18N()
	{
		$result = new stdClass();
		foreach ($this->meta->fields() as $name => $field)
		{
			if ($field->i18n)
			{
				$result->$name = new stdClass();
				foreach (Yii::app()->languages as $lang => $langName)
				{
					$value = $this->getAttribute($name, $lang);
					$result->$name->$lang = $value;
				}
			}
		}
		return $result;
	}

	/**
	 * Return owner of this document
	 * @return EmbeddedDocument
	 * @since v1.0.8
	 */
	public function getOwner()
	{
		if ($this->_owner !== null)
		{
			return $this->_owner;
		}
		else
		{
			return null;
		}
	}

	/**
	 * Return root document of this document
	 * This can be used to attach events like afterDelete to root object
	 * @return Document
	 * @since 2.0.1
	 */
	public function getRoot()
	{
		if ($this->_owner !== null)
		{
			return $this->_owner->getRoot();
		}
		else
		{
			return $this;
		}
	}

	/**
	 * Set owner of this document
	 * @param EmbeddedDocument $owner
	 * @since v1.0.8
	 */
	public function setOwner(EmbeddedDocument $owner)
	{
		$this->_owner = $owner;
	}

	/**
	 * Override default setScenario method for populating to embedded records
	 * @see Model::setScenario()
	 * @todo Set scenario for embedded documents
	 * @since v1.0.8
	 */
	public function setScenario($value)
	{
//		if($this->hasEmbeddedDocuments() && $this->_embedded !== null)
//		{
//			foreach($this->_embedded as $doc)
//			{
//				$doc->setScenario($value);
//			}
//		}
		parent::setScenario($value);
	}

	public function getScenario()
	{
		if ($this->_owner)
		{
			return $this->_owner->getScenario();
		}
		return parent::getScenario();
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Embedded documents handling">

	/**
	 * @since v1.0.8
	 */
	protected function initEmbeddedDocuments()
	{
		if (!$this->hasEmbeddedDocuments() || !$this->beforeEmbeddedDocsInit())
		{
			return false;
		}

		$this->afterEmbeddedDocsInit();
	}

	/**
	 * @since v1.0.8
	 */
	public function embeddedDocuments()
	{
		return $this->meta->properties('embedded');
	}

	/**
	 * @since v1.0.8
	 */
	public function hasEmbeddedDocuments()
	{
		return count($this->embeddedDocuments()) > 0;
	}


	/**
	 * Create instance of embedded document, based on defined type or
	 * __class field if it is set in data
	 * @param type $name
	 * @param type $attributes
	 */
	private function _instantiateEmbedded($name, $key = null, $attributes = [])
	{
		$model = null;
		if ($attributes instanceof EmbeddedDocument)
		{
			$attributes->setScenario($this->getScenario());
//			$attributes->_key = $key;
			$attributes->setOwner($this);
			return $attributes;
		}
		else
		{
			$attributes = (array) $attributes;
		}
		// When setAttributes from external array, (with only one language parameter)
		// new instance MUST NOT be created, instead exising instance MUST be updated
		// EXCEPT When a different type should be set
		if (isset($this->_virtualValues[$name]))
		{
			if ($this->meta->$name->embeddedArray)
			{
				if (array_key_exists($key, $this->_virtualValues[$name]))
				{
					$model = $this->_virtualValues[$name][$key];
				}
			}
			else
			{
				$model = $this->$name;
			}
		}
		if (isset($attributes['_class']) && $attributes['_class'])
		{
			$docClassName = $attributes['_class'];
		}
		else
		{
			$docClassName = $this->meta->$name->embedded;
		}
		// This is for automatic doc type, and if its new instance
		// TODO Global default class name should be configurable, so simple @Embedded could be used
		if (!$docClassName)
		{
			throw new UnexpectedValueException(sprintf("Class for embedded field '%s' in class '%s' not defined, use @Embedded('ClassName') to define default class", $name, $this->_class));
		}

		// It default class is not set, but something is embedded use null
		if (!is_string($docClassName))
		{
			return null;
		}

		// Check if model should be replaced by different type
		if ($model && $model instanceof EmbeddedDocument && $model->_class && $model->_class !== $docClassName)
		{
			$model = null;
		}

		// Create a new instance if need
		if (!$model instanceof EmbeddedDocument)
		{
			if (!@class_exists($docClassName))
			{
				$notFound = $docClassName;
				$docClassName = $this->classNotfound($docClassName);
				if(!$docClassName)
				{
					throw new RuntimeException(sprintf('Trying to instantiate embedded document with class `%s`, but class or fallback not found', $notFound));
				}
			}
			$model = new $docClassName($this->getScenario(), $this->getLang());
		}
//		$model->_key = $key;
		$model->setOwner($this);
		if ($attributes && is_array($attributes))
		{
			foreach ($model->meta->fields() as $field => $meta)
			{
				if (isset($attributes[$field]))
				{
					if ($meta->i18n)
					{
						// TODO This is sloopy, will fail with international arrays
						// Difference here is by setting data either from db (with language keys) or normal assign - from post - without language keys
						// However this can be probably detected by using if(isset($this->_virtualValues[$name]))... code fragment from above
						// With new instance, loop should be used, if not new, use params as is, need further investigation
						if (!is_array($attributes[$field]))
						{
							$model->setAttribute($field, $attributes[$field]);
						}
						else
						{
							foreach (Yii::app()->languages as $lang => $langName)
							{
								if (array_key_exists($lang, $attributes[$field]))
								{
									$model->setAttribute($field, $attributes[$field][$lang], $lang);
								}
							}
						}
					}
					else
					{
						$model->setAttribute($field, $attributes[$field]);
					}
				}
			}
		}
		return $model;
	}

// </editor-fold>

}