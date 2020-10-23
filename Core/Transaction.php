<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * Transaction for PDO (in this moment for postgres)
 */
final class Transaction {
	private Connection $connection;

	public function __construct(Connection $connection) {
		$this->connection = $connection;
	}

	/**
	 * @return mixed
	 * @throws \Throwable
	 */
	public function start(\Closure $callback, string $mode = '') {
		$this->connection->exec(sprintf('START TRANSACTION %s', $mode));
		try {
			$result = $callback();
			$this->connection->exec('COMMIT TRANSACTION');
			return $result;
		} catch (\Throwable $ex) {
			$this->connection->exec('ROLLBACK TRANSACTION');
			throw $ex;
		}
	}
}
