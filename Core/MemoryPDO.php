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
			return new class($this->memory) extends \PDOStatement {
				private $memory;

				public function __construct(array $memory) {
					$this->memory = $memory;
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
				 * @param int $column
				 * @return mixed
				 */
				public function fetchColumn($column = 0) {
					$row = $this->fetch();
					if (isset($row[$column]))
						return $row[$column];
					return $column === 0 ? current($row) : false;
				}
			};
		}
		return $this->origin->prepare($statement, $options);
	}
}