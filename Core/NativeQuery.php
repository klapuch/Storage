<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

/**
 * Simple native query without changes
 */
final class NativeQuery implements Query {
	private $database;
	private $statement;
	private $parameters;

	public function __construct(\PDO $database, string $statement, array $parameters = []) {
		$this->database = $database;
		$this->statement = $statement;
		$this->parameters = $parameters;
	}

	/**
	 * @return mixed
	 */
	public function field() {
		return $this->execute()->fetchColumn();
	}

	public function row(): array {
		return $this->execute()->fetch() ?: [];
	}

	public function rows(): array {
		return $this->execute()->fetchAll();
	}

	public function execute(): \PDOStatement {
		$statement = $this->database->prepare($this->statement);
		$statement->execute($this->parameters);
		return $statement;
	}
}