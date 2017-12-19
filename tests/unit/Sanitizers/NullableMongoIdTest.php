<?php

namespace Sanitizers;

use Codeception\TestCase\Test;
use Maslosoft\Mangan\EntityManager;
use Maslosoft\ManganTest\Models\Sanitizers\ModelWithNullableMongoId;
use MongoId;
use UnitTester;

class NullableMongoIdTest extends Test
{

	/**
	 * @var UnitTester
	 */
	protected $tester;

	// tests
	public function testIfWillAllowNullForMongoId()
	{
		$model = new ModelWithNullableMongoId();
		$em = new EntityManager($model);
		$em->save();
		$this->assertInstanceOf(MongoId::class, $model->_id);
		$this->assertNull($model->parentId);

		$model->parentId = new MongoId();
		$em->save();
		$this->assertInstanceOf(MongoId::class, $model->_id);
		$this->assertInstanceOf(MongoId::class, $model->parentId);
	}

}
