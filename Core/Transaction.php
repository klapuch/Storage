<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * Transaction for PDO (in this moment for postgres)
 */
final class Transaction {
	private $database;
	private $isolation;

	public function __construct(\PDO $database, string $isolation = 'read committed') {
		$this->database = $database;
		$this->isolation = $isolation;
	}

	/**
	 * Start the transaction with proper begin-commit-rollback flow
	 * @param \Closure $callback
	 * @return mixed
	 * @throws \Throwable
	 */
	public function start(\Closure $callback) {
		$this->database->exec(sprintf('START TRANSACTION ISOLATION LEVEL %s', $this->isolation));
		try {
			$result = $callback();
			$this->database->exec('COMMIT TRANSACTION');
			return $result;
		} catch (\Throwable $ex) {
			$this->database->exec('ROLLBACK TRANSACTION');
			throw $ex;
		}
	}
}