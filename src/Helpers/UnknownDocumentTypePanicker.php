<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 22.10.18
 * Time: 21:28
 */

namespace Maslosoft\Mangan\Helpers;

use Maslosoft\Mangan\Events\Event;
use Maslosoft\Mangan\Events\UnknownDocumentType;
use Maslosoft\Mangan\Exceptions\TransformatorException;

/**
 * This class will try to do something on unknown documents
 * or will panic.
 *
 * @package Maslosoft\Mangan\Helpers
 */
class UnknownDocumentTypePanicker
{
	public static function tryHandle(&$data, $parent, $parentField)
	{
		$className = '';
		$handled = false;
		if(!empty($parent))
		{
			$event = new UnknownDocumentType();
			$event->setData($data);
			$event->parent = $parent;
			$event->field = $parentField;

			$handled = Event::handled($parent, UnknownDocumentType::EventName, $event);
			if($handled)
			{
				$data = $event->getData();
				if(empty($data['_class']))
				{
					$handled = false;
				}
				else
				{
					$className = $data['_class'];
				}
			}
		}
		if(!$handled)
		{
			throw new TransformatorException('Could not determine document type');
		}
		return $className;
	}
}