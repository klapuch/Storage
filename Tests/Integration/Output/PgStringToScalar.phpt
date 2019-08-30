<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.1
 */

namespace Klapuch\Storage\Integration\Output;

use Klapuch\Storage\Output;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PgStringToScalar extends \Tester\TestCase {
	public function testConvertingInt() {
		Assert::same(10, (new Output\PgStringToScalar('10', 'integer'))->value());
		Assert::same(10, (new Output\PgStringToScalar('10', 'int'))->value());
		Assert::same(10, (new Output\PgStringToScalar('10', 'INTEGER'))->value());
		Assert::same(10, (new Output\PgStringToScalar('10', 'INT'))->value());
		Assert::same(10, (new Output\PgStringToScalar('10', 'smallint'))->value());
		Assert::same(10, (new Output\PgStringToScalar('10', 'SMALLINT'))->value());
		Assert::same(10, (new Output\PgStringToScalar('10', 'BIGINT'))->value());
		Assert::same(10, (new Output\PgStringToScalar('10', 'bigint'))->value());
	}

	public function testUnknownTypeToBeString() {
		Assert::same('10', (new Output\PgStringToScalar('10', 'text'))->value());
		Assert::same('10', (new Output\PgStringToScalar('10', 'foo'))->value());
	}

	public function testConvertingToBool() {
		Assert::true((new Output\PgStringToScalar('t', 'boolean'))->value());
		Assert::false((new Output\PgStringToScalar('f', 'boolean'))->value());
		Assert::false((new Output\PgStringToScalar('f', 'bool'))->value());
		Assert::false((new Output\PgStringToScalar('f', 'BOOLEAN'))->value());
		Assert::false((new Output\PgStringToScalar('f', 'BOOL'))->value());
	}

	public function testKeepingNullAsNull() {
		Assert::null((new Output\PgStringToScalar(null, 'integer'))->value());
		Assert::null((new Output\PgStringToScalar(null, 'boolean'))->value());
		Assert::null((new Output\PgStringToScalar(null, 'text'))->value());
	}
}

(new PgStringToScalar())->run();