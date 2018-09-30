<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * in-memory statement
 */
final class MemoryStatement extends \PDOStatement {
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
		return $columnNumber === 0
			? $this->memory[$column[1]]
			: false;
	}
}