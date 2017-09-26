<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Klapuch\Storage\Integration;

use Klapuch\Storage;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class PgStringToScalar extends \Tester\TestCase {
	public function testConvertingInt() {
		Assert::same(10, (new Storage\PgStringToScalar('10', 'integer'))->value());
		Assert::same(10, (new Storage\PgStringToScalar('10', 'int'))->value());
		Assert::same(10, (new Storage\PgStringToScalar('10', 'INTEGER'))->value());
		Assert::same(10, (new Storage\PgStringToScalar('10', 'INT'))->value());
	}

	public function testUnknownTypeToBeString() {
		Assert::same('10', (new Storage\PgStringToScalar('10', 'text'))->value());
		Assert::same('10', (new Storage\PgStringToScalar('10', 'foo'))->value());
	}

	public function testConvertingToBool() {
		Assert::true((new Storage\PgStringToScalar('t', 'boolean'))->value());
		Assert::false((new Storage\PgStringToScalar('f', 'boolean'))->value());
		Assert::false((new Storage\PgStringToScalar('f', 'bool'))->value());
		Assert::false((new Storage\PgStringToScalar('f', 'BOOLEAN'))->value());
		Assert::false((new Storage\PgStringToScalar('f', 'BOOL'))->value());
	}

	public function testKeepingNullAsNull() {
		Assert::null((new Storage\PgStringToScalar(null, 'integer'))->value());
		Assert::null((new Storage\PgStringToScalar(null, 'boolean'))->value());
		Assert::null((new Storage\PgStringToScalar(null, 'text'))->value());
	}
}

(new PgStringToScalar())->run();