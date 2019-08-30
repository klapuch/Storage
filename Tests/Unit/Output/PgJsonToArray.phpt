<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.1
 */

namespace Klapuch\Storage\Unit\Output;

use Klapuch\Storage\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PgJsonToArray extends Tester\TestCase {
	public function testConvertingArrayToArray() {
		Assert::same(
			[1, 'abc'],
			(new Output\PgJsonToArray(
				'[1, "abc"]',
				'jSON',
				new Output\FakeConversion()
			))->value()
		);
		Assert::same(
			[1, 'abc'],
			(new Output\PgJsonToArray(
				'[1, "abc"]',
				'jSONb',
				new Output\FakeConversion()
			))->value()
		);
	}

	public function testConvertingObjectToArray() {
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Output\PgJsonToArray(
				'{"name": "Dom", "race": "human"}',
				'jSON',
				new Output\FakeConversion()
			))->value()
		);
	}

	public function testDelegationForNotHstore() {
		Assert::same(
			'foo',
			(new Output\PgJsonToArray(
				'{"name": "Dom", "race": "human"}',
				'text',
				new Output\FakeConversion('foo')
			))->value()
		);
	}
}

(new PgJsonToArray())->run();