<?php
/**
 * @testCase
 * @phpVersion > 7.1.0
 */
namespace Klapuch\Storage\Unit;

use Tester;
use Tester\Assert;
use Klapuch\Storage\TestCase;
use Klapuch\{
	Storage, Log
};

require __DIR__ . '/../bootstrap.php';

final class MonitoredPDO extends TestCase\PostgresDatabase {
	public function testPrepareShortQuery() {
		$location = Tester\FileMock::create('');
		(new Storage\MonitoredPDO(
			$this->database,
			new Log\FakeLogs($location)
		))->prepare('SELECT name, type FROM test');
		Assert::contains('SELECT name, type FROM test', file_get_contents($location));
	}

	public function testQueryShortQuery() {
		$location = Tester\FileMock::create('');
		(new Storage\MonitoredPDO(
			$this->database,
			new Log\FakeLogs($location)
		))->query('SELECT name, type FROM test');
		Assert::contains('SELECT name, type FROM test', file_get_contents($location));
	}

	public function testLongPrepareWithoutTruncating() {
		$location = Tester\FileMock::create('');
		$query = sprintf('SELECT %s FROM test', str_repeat('a, ', 4000));
		(new Storage\MonitoredPDO(
			$this->database,
			new Log\FakeLogs($location)
		))->prepare($query);
		Assert::contains($query, file_get_contents($location));
		Assert::true(strlen(file_get_contents($location)) >= strlen($query));
	}

	public function testLongQueryWithoutTruncating() {
		$location = Tester\FileMock::create('');
		$query = sprintf('SELECT %s FROM test', str_repeat('a, ', 4000));
		try {
			(new Storage\MonitoredPDO(
				$this->database,
				new Log\FakeLogs($location)
			))->query($query);
		} catch(\PDOException $ex) {
			Assert::contains($query, file_get_contents($location));
			Assert::true(strlen(file_get_contents($location)) >= strlen($query));
		}
	}

	public function testMonitoringInvalidQuery() {
		$location = Tester\FileMock::create('');
		$query = 'INSERT INTO idk () (abc)';
		try {
			(new Storage\MonitoredPDO(
				$this->database,
				new Log\FakeLogs($location)
			))->query($query);
		} catch(\PDOException $ex) {
			Assert::contains($query, file_get_contents($location));
		}
	}
}

(new MonitoredPDO())->run();