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

final class PostgresHStore extends TestCase\PostgresDatabase {
	public function testCastingToArray() {
		Assert::same(
			['name' => 'Dom', 'race' => 'human'],
			(new Storage\PostgresHStore($this->database, 'name=>Dom,race=>human'))->cast()
		);
	}
}

(new PostgresHStore())->run();