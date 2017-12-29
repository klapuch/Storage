<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

/**
 * Cached query with arbitrary storage
 */
final class CachedPDO extends \PDO {
	private static $cache = [];
	private $origin;

	public function __construct(\PDO $origin) {
		$this->origin = $origin;
	}

	public function prepare($statement, $options = []): \PDOStatement {
		return new class($this->origin, $statement, $options, static::$cache) extends \PDOStatement {
			private $origin;
			private $statement;
			private $options;
			private $cache;
			private $parameters = [];

			public function __construct(\PDO $origin, string $statement, array $options, array &$cache) {
				$this->origin = $origin;
				$this->statement = $statement;
				$this->options = $options;
				$this->cache = &$cache;
			}

			public function execute($parameters = null): bool {
				$this->parameters = $parameters;
				return true;
			}

			public function fetch(
				$fetchStyle = null,
				$cursorOrientation = \PDO::FETCH_ORI_NEXT,
				$cursorOffset = 0
			): array {
				return $this->load($this->statement, $this->parameters, __FUNCTION__, function() {
					$statement = $this->origin->prepare($this->statement, $this->options);
					$statement->execute($this->parameters);
					return $statement->fetch();
				});
			}

			public function fetchAll(
				$fetchStyle = null,
				$fetchArgument = null,
				$ctorArgs = null
			): array {
				return $this->load($this->statement, $this->parameters, __FUNCTION__, function() {
					$statement = $this->origin->prepare($this->statement, $this->options);
					$statement->execute($this->parameters);
					return $statement->fetchAll();
				});
			}

			/**
			 * @param int $columnNumber
			 * @return mixed
			 */
			public function fetchColumn($columnNumber = 0) {
				return $this->load($this->statement, $this->parameters, __FUNCTION__, function() {
					$statement = $this->origin->prepare($this->statement, $this->options);
					$statement->execute($this->parameters);
					return $statement->fetchColumn();
				});
			}

			/**
			 * @param string $statement
			 * @param array $parameters
			 * @param string $fetch
			 * @param callable $result
			 * @return mixed
			 */
			private function load(string $statement, array $parameters, string $fetch, callable $result) {
				[$hashedParameters, $hashedStatement] = [$rawParameters = md5(serialize($parameters)), md5($statement)];
				if (!array_key_exists($hashedParameters, $this->cache[$hashedStatement][$fetch] ?? []))
					$this->cache[$hashedStatement][$fetch][$hashedParameters] = $result();
				return $this->cache[$hashedStatement][$fetch][$hashedParameters];
			}
		};
	}
}