<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\ManganTest\Models\Event;

use Maslosoft\Mangan\Document;
use Maslosoft\Mangan\Events\Event;
use Maslosoft\Mangan\Helpers\ParentChildTrashHandlers;
use Maslosoft\Mangan\Interfaces\TrashInterface;

/**
 * ChildDocument
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class ChildDocument extends Document
{

	use \Maslosoft\Mangan\Traits\Model\WithParentTrait,
	  \Maslosoft\Mangan\Traits\Model\TrashableTrait;

	public $title = '';

	public function __construct($scenario = 'insert', $lang = '')
	{
		parent::__construct($scenario, $lang);
		static $once = false;
		// Also check if has handler because of EventDestroyer
		if (!$once || !Event::hasHandler($this, TrashInterface::EventBeforeRestore))
		{
			(new ParentChildTrashHandlers)->registerChild($this, ParentDocument::class);
			$once = true;
		}
	}

	public function __toString()
	{
		return $this->title;
	}

}
