<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * in-memory statement
 */
final class MemoryStatement extends \PDOStatement {
	/** @var array<string, mixed> */
	private array $memory;

	private string $statement;

	/**
	 * @param array<string, mixed> $memory
	 */
	public function __construct(array $memory, string $statement) {
		$this->memory = $memory;
		$this->statement = $statement;
	}

	/**
	 * @return mixed[]
	 */
	public function fetch($fetchStyle = null, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $cursorOffset = 0): array {
		return $this->memory;
	}

	/**
	 * @return mixed[]
	 */
	public function fetchAll($fetchStyle = null, $fetchArgument = null, $ctorArgs = null): array {
		return $this->memory;
	}

	/**
	 * @return int|string|false|null
	 */
	public function fetchColumn($columnNumber = 0) {
		preg_match('~^SELECT\s+(\w+)~', $this->statement, $column);
		return $columnNumber === 0
			? $this->memory[$column[1]]
			: false;
	}
}
