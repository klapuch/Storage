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
			(new Storage\PgHStoreToArray(
				$this->connection,
				'name=>Dom,race=>human',
				'hSTORE',
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testDelegationForNotHstore() {
		Assert::same(
			'foo',
			(new Storage\PgHStoreToArray(
				$this->connection,
				'name=>Dom',
				'text',
				new Storage\FakeConversion('foo')
			))->value()
		);
	}
}

(new PgHStoreToArray())->run();