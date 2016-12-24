<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class MySqlTransaction extends Transaction {
	protected function begin(): void {
		$this->database->exec('START TRANSACTION');
	}

	protected function commit(): void {
		$this->database->exec('COMMIT');
	}

	protected function rollback(): void {
		$this->database->exec('ROLLBACK');
	}
}