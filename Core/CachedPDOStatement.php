<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

use Predis;

final class CachedPDOStatement extends \PDOStatement {
	private const NAMESPACE = 'postgres:column:meta:';
	private $origin;
	private $statement;
	private $redis;
	private static $cache = [];

	public function __construct(
		\PDOStatement $origin,
		string $statement,
		Predis\ClientInterface $redis
	) {
		$this->origin = $origin;
		$this->redis = $redis;
		$this->statement = $statement;
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
		return $this->origin->fetchAll(...func_get_args());
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
		$key = self::NAMESPACE . md5($this->statement);
		if (isset(static::$cache[$key][$column])) {
			return static::$cache[$key][$column];
		}
		if (!$this->redis->hexists($key, $column)) {
			$this->redis->hset($key, $column, (new StringData())->serialize($this->origin->getColumnMeta($column)));
			$this->redis->persist($key);
		}
		return static::$cache[$key][$column] = (new StringData())->unserialize($this->redis->hget($key, $column));
	}
}