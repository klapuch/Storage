<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

use Klapuch\Sql;

/**
 * Query built from SQL statement with parameters
 */
final class BuiltQuery implements Query {
	private $connection;
	private $statement;

	public function __construct(Connection $connection, Sql\Statement $statement) {
		$this->connection = $connection;
		$this->statement = $statement;
	}

	/**
	 * @return mixed
	 */
	public function field() {
		return (new TypedQuery(
			$this->connection,
			$this->statement->sql(),
			$this->statement->parameters()->binds()
		))->field();
	}

	public function row(int $style = \PDO::FETCH_ASSOC): array {
		return (new TypedQuery(
			$this->connection,
			$this->statement->sql(),
			$this->statement->parameters()->binds()
		))->row($style);
	}

	public function rows(int $style = \PDO::FETCH_ASSOC): array {
		return (new TypedQuery(
			$this->connection,
			$this->statement->sql(),
			$this->statement->parameters()->binds()
		))->rows($style);
	}

	public function execute(): \PDOStatement {
		return (new TypedQuery(
			$this->connection,
			$this->statement->sql(),
			$this->statement->parameters()->binds()
		))->execute();
	}
}