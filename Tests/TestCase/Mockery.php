<?php
declare(strict_types = 1);
namespace Klapuch\Storage\TestCase;

use Tester;

abstract class Mockery extends Tester\TestCase {
	protected function mock($class) {
		return \Mockery::mock($class);
	}

	protected function tearDown() {
		parent::tearDown();
		\Mockery::close();
	}
}