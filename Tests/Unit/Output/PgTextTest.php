<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Unit\Output;

use Klapuch\Storage\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class PgTextTest extends Tester\TestCase {
	public function testTextWithoutModification(): void {
		Assert::same('foo', (new Output\PgText('foo', 'text', new Output\FakeConversion()))->value());
	}
}

(new PgTextTest())->run();
