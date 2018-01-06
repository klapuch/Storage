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

final class PgHStoreToArray extends TestCase\PostgresDatabase {
	public function testConvertingToArray() {
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Storage\PgHStoreToArray($this->database, 'name=>Dom,race=>human'))->value()
		);
	}
}

(new PgHStoreToArray())->run();