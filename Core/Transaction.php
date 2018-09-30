<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * Transaction for PDO (in this moment for postgres)
 */
final class Transaction {
	private $connection;

	public function __construct(Connection $connection) {
		$this->connection = $connection;
	}

	/**
	 * Start the transaction with proper begin-commit-rollback flow
	 * @param \Closure $callback
	 * @return mixed
	 * @throws \Throwable
	 */
	public function start(\Closure $callback) {
		$this->connection->exec('START TRANSACTION');
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