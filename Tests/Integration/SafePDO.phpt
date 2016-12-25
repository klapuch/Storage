<?php
/**
 * @testCase
 * @phpVersion > 7.1.0
 */
namespace Klapuch\Storage\Integration;

use Tester\Assert;
use Klapuch\Storage;
use Klapuch\Storage\TestCase;

require __DIR__ . '/../bootstrap.php';

final class SafePDO extends TestCase\PostgresDatabase {
	protected function setUp() {
		parent::setUp();
		$this->prepareDatabase();
	}

	/**
	 * @throws \PDOException
	 */
	public function testThrowinOnError() {
		$statement = $this->database->prepare('FOO');
		$statement->execute();
	}

	/**
	 * @throws \PDOException
	 */
	public function testDisabledEmulatePrepares() {
		$statement = $this->database->prepare(
			'SELECT 1;SELECT 1'
		);
		$statement->execute();
	}

	public function testAssocFetch() {
		$statement = $this->database->prepare(
			'SELECT id, name FROM test WHERE id = ?'
		);
		$statement->execute([1]);
		Assert::equal(
			['id' => 1, 'name' => 'first'],
			$statement->fetch()
		);
	}

	private function prepareDatabase() {
		$statement = $this->database->prepare(
			'INSERT INTO test (id, name) VALUES (?, ?)'
		);
		$statement->execute([1, 'first']);
	}
}

(new SafePDO())->run();