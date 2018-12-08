<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.1
 */

namespace Klapuch\Storage\Unit;

use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class PgJsonToArray extends Tester\TestCase {
	public function testConvertingArrayToArray() {
		Assert::same(
			[1, 'abc'],
			(new Storage\PgJsonToArray(
				'[1, "abc"]',
				'jSON',
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testConvertingObjectToArray() {
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Storage\PgJsonToArray(
				'{"name": "Dom", "race": "human"}',
				'jSON',
				new Storage\FakeConversion()
			))->value()
		);
	}

	public function testDelegationForNotHstore() {
		Assert::same(
			'foo',
			(new Storage\PgJsonToArray(
				'{"name": "Dom", "race": "human"}',
				'text',
				new Storage\FakeConversion('foo')
			))->value()
		);
	}
}

(new PgJsonToArray())->run();