<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Mangan\Annotations;

use Maslosoft\Addendum\Collections\MetaAnnotation;

/**
 * CollectionName
 * Use this annotation to set custom collection name
 * @Target('class')
 * @template CollectionName('${name}')
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class CollectionNameAnnotation extends MetaAnnotation
{

	public $value;

	public function init()
	{
		$this->_entity->collectionName = $this->value;
	}

}