<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * In-memory PDO
 */
final class MemoryPDO extends MetaPDO {
	private $memory;
	private $origin;

	public function __construct(\PDO $origin, array $memory) {
		$this->origin = $origin;
		$this->memory = $memory;
	}

	public function prepare($statement, $options = []): \PDOStatement {
		if ($this->direct($statement)) {
			return new class($this->memory, $statement) extends \PDOStatement {
				private $memory;
				private $statement;

				public function __construct(array $memory, string $statement) {
					$this->memory = $memory;
					$this->statement = $statement;
				}

				public function fetch(
					$fetchStyle = null,
					$cursorOrientation = \PDO::FETCH_ORI_NEXT,
					$cursorOffset = 0
				): array {
					return $this->memory;
				}

				public function fetchAll(
					$fetchStyle = null,
					$fetchArgument = null,
					$ctorArgs = null
				): array {
					return $this->memory;
				}

				/**
				 * @param int $columnNumber
				 * @return mixed
				 */
				public function fetchColumn($columnNumber = 0) {
					preg_match('~^SELECT\s+(\w+)~', $this->statement, $column);
					return $columnNumber === 0 ? $this->memory[$column[1]] : false;
				}
			};
		}
		return $this->origin->prepare($statement, $options);
	}

	private function identifier(string $statement): bool {
		return (bool) preg_match('~^SELECT\s+[a-z_*]~i', $statement);
	}

	private function function(string $statement): bool {
		return (bool) preg_match('~^SELECT\s+\w+\(~i', $statement);
	}

	/**
	 * Will be the statement called directly?
	 * @param string $statement
	 * @return bool
	 */
	private function direct(string $statement): bool {
		return $this->identifier($statement)
			&& !$this->function($statement);
	}
}