<?php
declare(strict_types = 1);
namespace Klapuch\Database;

final class PDODatabase implements Database {
	private $connection;
	const OPTIONS = [
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		\PDO::ATTR_EMULATE_PREPARES => false,
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
	];

	public function __construct(string $dsn, string $user, string $password) {
		try {
			$this->connection = new \PDO($dsn, $user, $password, self::OPTIONS);
		} catch(\PDOException $ex) {
			throw new \RuntimeException(
				'Connection to database was not successful',
				503,
				$ex
			);
		}
	}

	public function fetch(string $query, array $parameters = []): array {
		return $this->query($query, $parameters)->fetch() ?: [];
	}

	public function fetchAll(string $query, array $parameters = []): array {
		return $this->query($query, $parameters)->fetchAll() ?: [];
	}

	public function fetchColumn(string $query, array $parameters = []) {
		return $this->query($query, $parameters)->fetchColumn();
	}

	public function query(string $query, array $parameters = []): \PDOStatement {
		try {
			$statement = $this->connection->prepare($query);
			if($this->onlyPlaceholders($parameters)) {
				$statement->execute(array_values($parameters));
			} elseif($this->onlyNamedParameters($parameters)) {
				$statement->execute($parameters);
			} else {
				throw new \PDOException(
					'Parameters must be either named or placeholders'
				);
			}
			return $statement;
		} catch(\PDOException $ex) {
			if($ex->getCode() === self::UNIQUE_CONSTRAINT) {
				throw new UniqueConstraint(
					$ex->getMessage(),
					(int)$ex->getCode(),
					$ex
				);
			}
			throw $ex;
		}
	}

	public function exec(string $query) {
		return $this->connection->exec($query);
	}

	/**
	 * Do the parameters consist only from named parameters?
	 * @param array $parameters
	 * @return bool
	 */
	private function onlyNamedParameters(array $parameters = []): bool {
		return array_filter(
			$parameters,
			function($parameter): bool {
				return is_string($parameter)
				&& substr($parameter, 0, 1) === ':';
			},
			ARRAY_FILTER_USE_KEY
		) === $parameters;
	}

	/**
	 * Do the parameters consist only from placeholders?
	 * @param array $parameters
	 * @return bool
	 */
	private function onlyPlaceholders(array $parameters = []): bool {
		return array_filter(
			$parameters,
			'is_int',
			ARRAY_FILTER_USE_KEY
		) === $parameters;
	}
}
