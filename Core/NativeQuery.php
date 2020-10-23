<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * Simple native query without changes
 */
final class NativeQuery implements Query {
	private Connection $connection;

	private string $statement;

	/** @var mixed[] */
	private array $parameters;

	/**
	 * @param mixed[] $parameters
	 */
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

	/**
	 * @return mixed[]
	 */
	public function row(): array {
		$row = $this->execute()->fetch(\PDO::FETCH_ASSOC);
		return $row === false ? [] : $row;
	}

	/**
	 * @return mixed[]
	 */
	public function rows(): array {
		return (array) $this->execute()->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function execute(): \PDOStatement {
		$statement = $this->connection->prepare($this->statement);
		$statement->execute($this->parameters);
		return $statement;
	}
}
