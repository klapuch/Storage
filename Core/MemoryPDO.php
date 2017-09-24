<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

/**
 * In-memory PDO
 */
final class MemoryPDO extends \PDO {
	private $memory;
	private $origin;
	private $tables;

	public function __construct(\PDO $origin, array $memory, array $tables) {
		$this->origin = $origin;
		$this->memory = $memory;
		$this->tables = $tables;
	}

	public function prepare($statement, $options = []): \PDOStatement {
		if ($this->identifier($statement) && !$this->function($statement) && !$this->direct($statement, $this->tables)) {
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

	private function identifier(string $statement): bool {
		return (bool) preg_match('~^SELECT\s+[a-z_*]~i', $statement);
	}

	private function function(string $statement): bool {
		return (bool) preg_match('~^SELECT\s+[\w\d_]+\(~i', $statement);
	}

	/**
	 * Will be the statement called directly?
	 * @param string $statement
	 * @param string[] $tables
	 * @return bool
	 */
	private function direct(string $statement, array $tables): bool {
		preg_match_all('~FROM\s+([\w\d_]+)~i', $statement, $from);
		preg_match_all('~JOIN\s+([\w\d_]+)~i', $statement, $join);
		array_shift($from);
		array_shift($join);
		return (bool) array_udiff($tables, array_merge(current($from), current($join)), 'strcasecmp');
	}
}