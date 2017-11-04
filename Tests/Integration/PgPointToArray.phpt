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

final class PgPointToArray extends TestCase\PostgresDatabase {
	public function testConvertingToXAndY() {
		Assert::same(
			['x' => 50.556785, 'y' => 70.0],
			(new Storage\PgPointToArray($this->database, '(50.556785,70)'))->value()
		);
	}

	public function testAllowingNull() {
		Assert::null((new Storage\PgPointToArray($this->database, null))->value());
	}
}

(new PgPointToArray())->run();