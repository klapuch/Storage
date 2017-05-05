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

final class ParameterizedQuery extends TestCase\PostgresDatabase {
	public function testEmptySetToArray() {
		$statement = 'SELECT * FROM test WHERE name = :name AND type = :type';
		$parameters = ['name' => 'Dom', 'type' => 'A'];
		$query = new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		);
		Assert::same([], $query->row());
		Assert::same([], $query->rows());
	}

	/**
	 * @throws \UnexpectedValueException Parameters can not be mixed
	 */
	public function testThrowingOnMismatch() {
		$statement = 'SELECT * FROM test WHERE name = :name AND type = :type';
		$parameters = [':name' => 'Dom', 1 => 'A'];
		(new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		))->rows();
	}

	public function testAddingMissingColon() {
		$statement = 'SELECT * FROM test WHERE name = :name AND type = :type';
		$parameters = ['name' => 'Dom', 'type' => 'A'];
		$query = new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		);
		Assert::noError(function() use ($query) {
			$query->row();
			$query->rows();
			$query->field();
		});
	}

	public function testAddingMissingColonIfNeeded() {
		$statement = 'SELECT * FROM test WHERE name = :name AND type = :type';
		$parameters = ['name' => 'Dom', ':type' => 'A'];
		$query = new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		);
		Assert::noError(function() use ($query) {
			$query->row();
			$query->rows();
			$query->field();
		});
	}

	public function testBareParameters() {
		$statement = 'SELECT * FROM test WHERE name = ? AND type = ?';
		$parameters = ['Dom', 'A'];
		$query = new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		);
		Assert::noError(function() use ($query) {
			$query->row();
			$query->rows();
			$query->field();
		});
	}

	public function testEmptyParametersWithoutUsage() {
		$statement = 'SELECT * FROM test';
		$query = new Storage\ParameterizedQuery($this->database, $statement, []);
		Assert::noError(function() use ($query) {
			$query->row();
			$query->rows();
			$query->field();
		});
	}

	public function testArrangingMessedUpPositions() {
		$statement = 'SELECT * FROM test WHERE name = ? AND type = ?';
		$parameters = [1 => 'Dom', 4 => 'A'];
		$query = new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		);
		Assert::noError(function() use ($query) {
			$query->row();
			$query->rows();
			$query->field();
		});
	}

	/**
	 * @throws \UnexpectedValueException Not all parameters are used
	 */
	public function testThrowingOnNotEnoughBareParametersAsEmptyOnes() {
		$statement = 'SELECT * FROM test WHERE name = ?';
		(new Storage\ParameterizedQuery($this->database, $statement, []))->rows();
	}

	/**
	 * @throws \UnexpectedValueException Not all parameters are used
	 */
	public function testThrowingOnNotEnoughNamedParametersAsEmptyOnes() {
		$statement = 'SELECT * FROM test WHERE name = :name';
		(new Storage\ParameterizedQuery($this->database, $statement, []))->rows();
	}

	/**
	 * @throws \UnexpectedValueException Not all parameters are used
	 */
	public function testThrowingOnAllOverusedBareParameters() {
		$statement = 'SELECT * FROM test';
		$parameters = ['Dom', 'A'];
		(new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		))->rows();
	}

	/**
	 * @throws \UnexpectedValueException Not all parameters are used
	 */
	public function testThrowingOnSomeOverusedBareParameters() {
		$statement = 'SELECT * FROM test WHERE name = ?';
		$parameters = ['Dom', 'A'];
		(new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		))->rows();
	}

	/**
	 * @throws \UnexpectedValueException Not all parameters are used
	 */
	public function testThrowingOnAllOverusedNamedParameters() {
		$statement = 'SELECT * FROM test';
		$parameters = [':name' => 'Dom', ':type' => 'A'];
		(new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		))->rows();
	}

	/**
	 * @throws \UnexpectedValueException Not all parameters are used
	 */
	public function testThrowingOnSomeOverusedNamedParameters() {
		$statement = 'SELECT * FROM test WHERE name = :name';
		$parameters = [':name' => 'Dom', ':type' => 'A'];
		(new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		))->rows();
	}

	/**
	 * @throws \UnexpectedValueException Not all parameters are used
	 */
	public function testThrowingOnNotEnoughPlaceholderParameters() {
		$statement = 'SELECT * FROM test WHERE name = ? AND type = ? AND id = ?';
		$parameters = ['Dom', 'A'];
		(new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		))->rows();
	}

	/**
	 * @throws \UnexpectedValueException Not all parameters are used
	 */
	public function testThrowingOnNotEnoughNamedParameters() {
		$statement = 'SELECT * FROM test WHERE name = :name AND type = :type AND id = :id';
		$parameters = [':name' => 'Dom', ':type' => 'A'];
		(new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		))->rows();
	}

	public function testNoErrorOnWeirdFormattedStatement() {
		$statement = 'SELECT * FROM
			test
			WHERE name =               
					:name           
		';
		$parameters = [':name' => 'Dom'];
		$query = new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		);
		Assert::noError(function() use ($query) {
			$query->row();
			$query->rows();
			$query->field();
		});
	}

	/**
	 * @throws \UnexpectedValueException Not all parameters are used
	 */
	public function testThrowingOnDifferentlyNamedParameters() {
		$statement = 'SELECT * FROM test WHERE name = :name';
		$parameters = [':foo' => 'Dom'];
		(new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		))->rows();
	}

	/**
	 * @throws \UnexpectedValueException Not all parameters are used
	 */
	public function testThrowingOnSpaceAfterColon() {
		$statement = 'SELECT * FROM test WHERE name = : name';
		$parameters = [':name' => 'Dom'];
		(new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		))->rows();
	}

	public function testNoSpacesAroundPlaceholderParameters() {
		$statement = 'INSERT INTO test (name, type) VALUES (?, ?)';
		$parameters = ['foo', 'B'];
		$query = new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		);
		Assert::noError(function() use ($query) {
			$query->execute();
		});
	}

	public function testNoSpacesAroundNamedParameters() {
		$statement = 'INSERT INTO test (name, type) VALUES (:name, :type)';
		$parameters = [':name' => 'foo', ':type' => 'A'];
		$query = new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		);
		Assert::noError(function() use ($query) {
			$query->execute();
		});
	}

	/**
	 * @throws \UnexpectedValueException Not all parameters are used
	 */
	public function testThrowingOnCaseInsensitiveNamedParameters() {
		$statement = 'SELECT * FROM test WHERE name = :name';
		$parameters = [':NAME' => 'Dom'];
		(new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		))->rows();
	}

	public function testMultipleNamedParametersInStatement() {
		$statement = 'SELECT name FROM test WHERE name = :name AND name = :name';
		$parameters = [':name' => 'Dom'];
		$query = new Storage\ParameterizedQuery(
			$this->database,
			$statement,
			$parameters
		);
		Assert::noError(function() use ($query) {
			$query->row();
		});
	}

	public function testThrowingToUniqueConstraint() {
		$query = "INSERT INTO test (id, name, type) VALUES (1, 'Dom', 'A')";
		$this->database->exec($query);
		$ex = Assert::exception(function() use ($query) {
			(new Storage\ParameterizedQuery(
				$this->database,
				$query
			))->execute();
		}, Storage\UniqueConstraint::class);
		Assert::same(23505, $ex->getCode());
		Assert::type(\PDOException::class, $ex->getPrevious());
		Assert::same('23505', $ex->getPrevious()->getCode());
	}

	public function testReThrowing() {
		$query = "INSERT INTO test (id, name, type) VALUES (1, 'Dom', 'A')";
		$ex = Assert::exception(function() use ($query) {
			(new Storage\ParameterizedQuery(
				$this->database,
				$query . 'FOOOOOOOOOOOOO'
			))->execute();
		}, \PDOException::class);
	}

	public function testPostgreRecasting() {
		$query = 'SELECT name::INT FROM test';
		Assert::noError(function() use ($query) {
			(new Storage\ParameterizedQuery(
				$this->database,
				$query
			))->execute();
		});
	}
}


(new ParameterizedQuery())->run();