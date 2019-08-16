<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

use Predis;

final class CachedPDOStatement extends \PDOStatement {
	/** @var mixed[] */
	private static $cache = [];

	/** @var \PDOStatement */
	private $origin;

	/** @var string */
	private $statement;

	/** @var \SplFileInfo */
	private $file;

	public function __construct(
		\PDOStatement $origin,
		string $statement,
		\SplFileInfo $file
	) {
		$this->origin = $origin;
		$this->statement = $statement;
		$this->file = $file;
	}

	public function execute($inputParameters = null): bool {
		return $this->origin->execute(...func_get_args());
	}

	public function fetch(
		$fetchStyle = null,
		$cursorOrientation = \PDO::FETCH_ORI_NEXT,
		$cursorOffset = 0
	): array {
		return $this->origin->fetch(...func_get_args()) ?: [];
	}

	public function fetchAll(
		$fetchStyle = null,
		$fetchArgument = null,
		$ctorArgs = null
	): array {
		return (array) $this->origin->fetchAll(...func_get_args());
	}

	/**
	 * @param int $columnNumber
	 * @return mixed
	 */
	public function fetchColumn($columnNumber = 0) {
		return $this->origin->fetchColumn(...func_get_args());
	}

	public function columnCount(): int {
		return $this->origin->columnCount();
	}

	public function getColumnMeta($column): array {
		$key = md5($this->statement);
		if (isset(static::$cache[$key][$column])) {
			return static::$cache[$key][$column];
		}
		['table' => $table] = $this->origin->getColumnMeta($column);
		$schema = require $this->file->getpathname();
		return static::$cache[$key][$column] = $schema[$table][$column];
	}
}