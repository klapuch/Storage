<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * Simple native query without changes
 */
final class NativeQuery implements Query {
	private $connection;
	private $statement;
	private $parameters;

	public function __construct(Connection $connection, string $statement, array $parameters = []) {
		$this->connection = $connection;
		$this->statement = $statement;
		$this->parameters = $parameters;
	}

	/**
	 * @return mixed
	 */
	public function field() {
		return $this->execute()->fetchColumn();
	}

	public function row(int $style = \PDO::FETCH_ASSOC): array {
		return $this->execute()->fetch($style) ?: [];
	}

	public function rows(int $style = \PDO::FETCH_ASSOC): array {
		return $this->execute()->fetchAll($style);
	}

	public function execute(): \PDOStatement {
		$statement = $this->connection->prepare($this->statement);
		$statement->execute($this->parameters);
		return $statement;
	}
}