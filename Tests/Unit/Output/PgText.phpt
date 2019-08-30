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

final class PgText extends Tester\TestCase {
	public function testTextWithoutModification() {
		Assert::same('foo', (new Output\PgText('foo', 'text', new Output\FakeConversion()))->value());
	}
}

(new PgText())->run();