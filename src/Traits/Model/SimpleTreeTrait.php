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

namespace Maslosoft\Mangan\Traits\Model;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Mangan\EntityManager;
use Maslosoft\Mangan\Events\Event;
use Maslosoft\Mangan\Events\ModelEvent;
use Maslosoft\Mangan\Helpers\RawFinder;
use Maslosoft\Mangan\Interfaces\SimpleTreeInterface;
use Maslosoft\Mangan\Interfaces\TrashInterface;
use MongoId;

/**
 * Related Tree Trait
 * @see SimpleTreeInterface
 * @author Piotr
 */
trait SimpleTreeTrait
{

	use WithParentTrait;

	/**
	 * @RelatedArray(join = {'_id' = 'parentId'}, updatable = true)
	 * @RelatedOrdering('order')
	 * @var AnnotatedInterface[]
	 */
	public $children = [];

	/**
	 * @Label('Manual sort')
	 * @var int
	 */
	public $order = 0;

	/**
	 * NOTE: This must be called by class using this trait
	 * @Ignored
	 */
	public function initTree()
	{
		if ($this instanceof TrashInterface)
		{
			// Trash related events
			$onBeforeTrash = function(ModelEvent $event)
			{
				$event->handled = true;
			};
			$onBeforeTrash->bindTo($this);
			Event::on($this, TrashInterface::EventBeforeTrash, $onBeforeTrash);


			$onAfterTrash = function(ModelEvent $event)
			{
				foreach ($event->sender->children as $child)
				{
					$child->trash();
				}
			};
			$onAfterTrash->bindTo($this);
			Event::on($this, TrashInterface::EventAfterTrash, $onAfterTrash);


			$onAfterRestore = function(ModelEvent $event)
			{
				// Root nodes does not have parentId
				if ($this->parentId)
				{
					// Put node to root if parent does not exists
					/**
					 * TODO Use exists here instead of raw finder.
					 * TODO investigate why rawfinder was used here.
					 */
					if (!(new RawFinder($this))->findByPk(new MongoId($this->parentId)))
					{
						$this->parentId = null;
						(new EntityManager($this))->update(['parentId']);
					}
				}
			};
			$onAfterRestore->bindTo($this);
			Event::on($this, TrashInterface::EventAfterRestore, $onAfterRestore);
		}
	}

	/**
	 * Move to a new parent
	 * @param string|MongoId $parentId
	 * @param string[]|MongoId[] $order
	 * @Ignored
	 */
	public function moveTo($parentId, $order = [])
	{
		$this->parentId = $parentId;
		(new EntityManager($this))->update(['parentId']);

		$i = 0;

		$node = new static;
		$em = new EntityManager($node);
		foreach ((array) $order as $id)
		{
			$node->_id = $id;
			$node->order = $i++;
			$em->update(['order']);
		}
	}

}
