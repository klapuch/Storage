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

final class PgText extends Tester\TestCase {
	public function testTextWithoutModification() {
		Assert::same('foo', (new Storage\PgText('foo', 'text', new Storage\FakeConversion()))->value());
	}
}

(new PgText())->run();