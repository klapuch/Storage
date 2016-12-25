<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

/**
 * Transaction for PDO
 */
final class Transaction {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	/**
	 * Start the transaction with proper begin-commit-rollback flow
	 * @param \Closure $callback
	 * @return mixed
	 * @throws \Throwable
	 */
	final public function start(\Closure $callback) {
		$this->database->beginTransaction();
		try {
			$result = $callback();
			$this->database->commit();
			return $result;
		} catch(\Throwable $ex) {
			$this->database->rollback();
			throw $ex;
		}
	}
}