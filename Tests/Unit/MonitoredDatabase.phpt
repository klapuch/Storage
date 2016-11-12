<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Klapuch\Storage\Unit;

use Tester;
use Tester\Assert;
use Klapuch\{
	Storage, Log
};

require __DIR__ . '/../bootstrap.php';

final class MonitoredDatabase extends Tester\TestCase {
	public function testMonitoringSimpleQuery() {
		$location = Tester\FileMock::create('');
		Assert::same(
			['fetch'],
			(new Storage\MonitoredDatabase(
				new Storage\FakeDatabase(),
				new Log\FakeLogs($location)
			))->fetch('SELECT me, you FROM world')
		);
		Assert::contains('SELECT me, you FROM world', file_get_contents($location));
	}

	public function testMonitoringLongQueryWithoutTruncating() {
		$location = Tester\FileMock::create('');
		$query = sprintf('SELECT %s FROM world', str_repeat('a, ', 4000));
		Assert::same(
			['fetchAll'],
			(new Storage\MonitoredDatabase(
				new Storage\FakeDatabase(),
				new Log\FakeLogs($location)
			))->fetchAll($query)
		);
		Assert::contains($query, file_get_contents($location));
		Assert::true(strlen(file_get_contents($location)) >= strlen($query));
	}

	public function testMonitoringInvalidQuery() {
		$location = Tester\FileMock::create('');
		$query = 'INSERT INTO idk () (abc)';
		Assert::same(
			'fetchColumn',
			(new Storage\MonitoredDatabase(
				new Storage\FakeDatabase(),
				new Log\FakeLogs($location)
			))->fetchColumn($query)
		);
		Assert::contains($query, file_get_contents($location));
	}

	public function testMonitoringQueryCommand() {
		$location = Tester\FileMock::create('');
		$query = 'DELETE * FROM world';
		Assert::type(
			\PDOStatement::class,
			(new Storage\MonitoredDatabase(
				new Storage\FakeDatabase(),
				new Log\FakeLogs($location)
			))->query($query)
		);
		Assert::contains($query, file_get_contents($location));
	}

	public function testMonitoringExecCommand() {
		$location = Tester\FileMock::create('');
		$query = 'UPDATE world SET me = "you"';
		Assert::null(
			(new Storage\MonitoredDatabase(
				new Storage\FakeDatabase(),
				new Log\FakeLogs($location)
			))->exec($query)
		);
		Assert::contains($query, file_get_contents($location));
	}
}

(new MonitoredDatabase())->run();
