<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1.0
 */
namespace Klapuch\Storage\Integration;

use Klapuch\Storage;
use Klapuch\Storage\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class ApplicationQuery extends TestCase\PostgresDatabase {
	public function testExceptionAsReadableMessage() {
		$ex = Assert::exception(function() {
			(new Storage\ApplicationQuery(
				new Storage\ParameterizedQuery(
					$this->database,
					'SELECT exception_procedure(?)',
					['abc']
				)
			))->execute();
		}, \UnexpectedValueException::class, 'abc');
		Assert::type(\PDOException::class, $ex->getPrevious());
	}

	public function testApplyingOnlyForExceptions() {
		$ex = Assert::exception(function() {
			(new Storage\ApplicationQuery(
				new Storage\ParameterizedQuery(
					$this->database,
					'SELECT * FROM xxx'
				)
			))->execute();
		}, \PDOException::class);
		Assert::contains('ERROR:', $ex->getMessage());
		Assert::null($ex->getPrevious());
	}
}

(new ApplicationQuery())->run();