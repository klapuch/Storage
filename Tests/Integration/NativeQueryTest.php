<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Integration;

use Klapuch\Storage;
use Klapuch\Storage\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class NativeQueryTest extends TestCase\PostgresDatabase {
	public function testPassingWithEmptySet(): void {
		(new Storage\NativeQuery($this->connection, 'DROP TABLE IF EXISTS scalars CASCADE'))->execute();
		(new Storage\NativeQuery($this->connection, 'CREATE TABLE scalars (name text, age smallint, good boolean, bad boolean, id integer)'))->execute();
		Assert::same([], (new Storage\NativeQuery($this->connection, 'SELECT * FROM scalars'))->row());
		Assert::same([], (new Storage\NativeQuery($this->connection, 'SELECT * FROM scalars'))->rows());
		Assert::false((new Storage\NativeQuery($this->connection, 'SELECT * FROM scalars'))->field());
	}
}


(new NativeQueryTest())->run();
