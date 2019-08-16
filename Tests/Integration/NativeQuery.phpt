<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.1
 */

namespace Klapuch\Storage\Integration;

use Klapuch\Storage;
use Klapuch\Storage\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class NativeQuery extends TestCase\PostgresDatabase {
	public function testPassingWithEmptySet() {
		Assert::same([], (new Storage\NativeQuery($this->connection, 'SELECT * FROM scalars'))->row());
		Assert::same([], (new Storage\NativeQuery($this->connection, 'SELECT * FROM scalars'))->rows());
		Assert::false((new Storage\NativeQuery($this->connection, 'SELECT * FROM scalars'))->field());
	}
}


(new NativeQuery())->run();