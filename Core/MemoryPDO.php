<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

/**
 * In-memory PDO
 */
final class MemoryPDO extends \PDO {
	private $memory;
	private $origin;

	public function __construct(\PDO $origin, array $memory) {
		$this->origin = $origin;
		$this->memory = $memory;
	}

	public function prepare($statement, $options = []): \PDOStatement {
		if (preg_match('~^SELECT~', $statement)) {
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
					if (count($this->memory) === count($this->memory, COUNT_RECURSIVE))
						return $this->memory;
					return current($this->memory);
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
					preg_match('~^SELECT\s+([\w\d_]+)~', $this->statement, $column);
					return $columnNumber === 0 ? $this->fetch()[$column[1]] : false;
				}
			};
		}
		return $this->origin->prepare($statement, $options);
	}
}