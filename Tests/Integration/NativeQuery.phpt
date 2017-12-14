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
		(new Storage\NativeQuery($this->database, 'DROP TABLE IF EXISTS scalars'))->execute();
		(new Storage\NativeQuery($this->database, 'CREATE TABLE scalars (name text, age smallint, good boolean, bad boolean, id integer)'))->execute();
		Assert::same([], (new Storage\NativeQuery($this->database, 'SELECT * FROM scalars'))->row());
		Assert::same([], (new Storage\NativeQuery($this->database, 'SELECT * FROM scalars'))->rows());
		Assert::false((new Storage\NativeQuery($this->database, 'SELECT * FROM scalars'))->field());
	}
}


(new NativeQuery())->run();