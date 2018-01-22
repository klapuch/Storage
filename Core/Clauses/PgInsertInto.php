<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

use Klapuch\Storage;

final class PgInsertInto implements InsertInto {
	private $table;
	private $values;

	public function __construct(string $table, array $values) {
		$this->table = $table;
		$this->values = $values;
	}

	public function returning(array $columns): Returning {
		return new Returning($this, $columns);
	}

	public function sql(): string {
		return sprintf(
			'INSERT INTO %s (%s) VALUES (%s)',
			$this->table,
			implode(', ', array_keys($this->values)),
			implode(', ', array_map([$this, 'cast'], $this->values))
		);
	}

	// @codingStandardsIgnoreStart Used by callback
	/**
	 * @param mixed $value
	 * @return string
	 */
	private function cast($value): string {
		return (new Storage\PgLiteral($value))->value();
	}
	// @codingStandardsIgnoreEnd
}