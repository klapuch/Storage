<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

abstract class Transaction {
	protected $database;

	final public function __construct(Database $database) {
		$this->database = $database;
	}

	/**
	 * Start the transaction with proper commit/rollback
	 * And rethrowing an exception in case error occur
	 * @param \Closure $callback
	 * @return mixed
	 * @throws \Throwable
	 */
	final public function start(\Closure $callback) {
		$this->begin();
		try {
			$result = $callback();
			$this->commit();
			return $result;
		} catch(\Throwable $ex) {
			$this->rollback();
			if($ex instanceof \PDOException) {
				throw new \RuntimeException(
					'Error on the database side. Rolled back.',
					(int)$ex->getCode(),
					$ex
				);
			}
			throw $ex;
		}
	}

	/**
	 * Begin the transaction
	 * @return void
	 */
	abstract protected function begin(): void;

	/**
	 * Commit the transaction
	 * @return void
	 */
	abstract protected function commit(): void;

	/**
	 * Rollback the transaction
	 * @return void
	 */
	abstract protected function rollback(): void;
}