<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Klapuch\Storage\Integration;

use Tracy;
use Tester;
use Tester\Assert;
use Klapuch\Storage;

require __DIR__ . '/../bootstrap.php';

final class MonitoredDatabase extends Tester\TestCase {
	public function testMonitoringSimpleQuery() {
		Assert::same(
			['fetch'],
			(new Storage\MonitoredDatabase(
				new Storage\FakeDatabase()
			))->fetch('SELECT me, you FROM world')
		);
		ob_start();
		Tracy\Debugger::getBar()->render();
		$bar = ob_get_clean();
		Assert::contains('SELECT me, you FROM world', $bar);
		Assert::contains(htmlspecialchars('<h2>SELECT<\/h2>'), $bar);
	}

	public function testMonitoringLongQueryWithTruncating() {
		$query = sprintf('SELECT %s FROM world', str_repeat('a, ', 4000));
		Assert::same(
			['fetchAll'],
			(new Storage\MonitoredDatabase(
				new Storage\FakeDatabase()
			))->fetchAll($query)
		);
		ob_start();
		Tracy\Debugger::getBar()->render();
		$bar = ob_get_clean();
		Assert::notContains($query, $bar);
		Assert::contains(htmlspecialchars('<h2>SELECT<\/h2>'), $bar);
	}

	public function testCapitalizedTitle() {
		Assert::equal(
			new \PDOStatement(),
			(new Storage\MonitoredDatabase(
				new Storage\FakeDatabase()
			))->query('delete * from world')
		);
		ob_start();
		Tracy\Debugger::getBar()->render();
		Assert::contains(htmlspecialchars('<h2>DELETE<\/h2>'), ob_get_clean());
	}

	public function testIgnoringInvalidQuery() {
		Assert::same(
			'fetchColumn',
			(new Storage\MonitoredDatabase(
				new Storage\FakeDatabase()
			))->fetchColumn('INSERT INTO idk () (abc)')
		);
		ob_start();
		Tracy\Debugger::getBar()->render();
		Assert::contains(htmlspecialchars('<h2>INSERT<\/h2>'), ob_get_clean());
	}

	public function testUnknownOperation() {
		(new Storage\MonitoredDatabase(
			new Storage\FakeDatabase()
		))->exec('WITH UPDATE do Something');
		ob_start();
		Tracy\Debugger::getBar()->render();
		$bar = ob_get_clean();
		Assert::contains(htmlspecialchars('<h2>WITH<\/h2>'), $bar);
	}
}

(new MonitoredDatabase())->run();
