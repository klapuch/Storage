<?php
declare(strict_types = 1);
namespace Klapuch\Database;

interface Transaction {
	/**
	 * Start the transaction with proper commit/rollback
	 * And rethrowing an exception in case error occur
	 * @param \Closure $callback
	 * @return mixed
	 * @throws \Throwable
	 */
	public function start(\Closure $callback);
}