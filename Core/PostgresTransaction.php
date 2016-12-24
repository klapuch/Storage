<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class PostgresTransaction extends Transaction {
	protected function begin(): void {
		$this->database->exec('BEGIN TRANSACTION');
	}

	protected function commit(): void {
		$this->database->exec('COMMIT TRANSACTION');
	}

	protected function rollback(): void {
		$this->database->exec('ROLLBACK TRANSACTION');
	}
}