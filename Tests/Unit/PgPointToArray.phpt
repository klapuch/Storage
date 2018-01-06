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

final class PgPointToArray extends Tester\TestCase {
	public function testConvertingToXAndY() {
		Assert::same(
			['x' => 50.556785, 'y' => 70.0],
			(new Storage\PgPointToArray('(50.556785,70)'))->value()
		);
	}
}

(new PgPointToArray())->run();