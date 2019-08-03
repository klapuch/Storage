<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

use Klapuch\Sql\Statement\Statement;

/**
 * Query built from SQL statement with parameters
 */
final class BuiltQuery implements Query {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \Klapuch\Sql\Statement\Statement */
	private $statement;

	public function __construct(Connection $connection, Statement $statement) {
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
			$this->statement->parameters()
		))->field();
	}

	public function row(int $style = \PDO::FETCH_ASSOC): array {
		return (new TypedQuery(
			$this->connection,
			$this->statement->sql(),
			$this->statement->parameters()
		))->row($style);
	}

	public function rows(int $style = \PDO::FETCH_ASSOC): array {
		return (new TypedQuery(
			$this->connection,
			$this->statement->sql(),
			$this->statement->parameters()
		))->rows($style);
	}

	public function execute(): \PDOStatement {
		return (new TypedQuery(
			$this->connection,
			$this->statement->sql(),
			$this->statement->parameters()
		))->execute();
	}
}
