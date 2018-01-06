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
		$this->database->prepare(
			sprintf(
				'START TRANSACTION ISOLATION LEVEL %s',
				$this->isolation
			)
		)->execute();
		try {
			$result = $callback();
			$this->database->prepare('COMMIT TRANSACTION')->execute();
			return $result;
		} catch (\Throwable $ex) {
			$this->database->prepare('ROLLBACK TRANSACTION')->execute();
			throw $ex;
		}
	}
}