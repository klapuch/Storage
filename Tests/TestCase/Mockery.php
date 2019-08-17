<?php
declare(strict_types = 1);

namespace Klapuch\Storage\TestCase;

use Mockery\LegacyMockInterface;
use Tester;

abstract class Mockery extends Tester\TestCase {
	protected function mock($class): LegacyMockInterface {
		return \Mockery::mock($class);
	}

	protected function tearDown(): void {
		parent::tearDown();
		\Mockery::close();
	}
}