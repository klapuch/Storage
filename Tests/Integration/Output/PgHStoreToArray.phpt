<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.1
 */

namespace Klapuch\Storage\Integration\Output;

use Klapuch\Storage\Output;
use Klapuch\Storage\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PgHStoreToArray extends TestCase\PostgresDatabase {
	public function testConvertingToArray() {
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Output\PgHStoreToArray(
				$this->connection,
				'name=>Dom,race=>human',
				'hSTORE',
				new Output\FakeConversion()
			))->value()
		);
	}

	public function testEmptyHstoreAsEmptyArray() {
		Assert::same(
			[],
			(new Output\PgHStoreToArray(
				$this->connection,
				'',
				'hstore',
				new Output\FakeConversion()
			))->value()
		);
	}

	public function testDelegationForNotHstore() {
		Assert::same(
			'foo',
			(new Output\PgHStoreToArray(
				$this->connection,
				'name=>Dom',
				'text',
				new Output\FakeConversion('foo')
			))->value()
		);
	}
}

(new PgHStoreToArray())->run();