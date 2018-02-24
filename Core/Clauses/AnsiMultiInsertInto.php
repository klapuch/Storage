<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class AnsiMultiInsertInto implements InsertInto {
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
			'INSERT INTO %s (%s) VALUES %s',
			$this->table,
			implode(', ', array_keys(current($this->values))),
			implode(
				', ',
				array_map(
					function(array $row): string {
						return sprintf('(%s)', implode(', ', $row));
					},
					$this->values
				)
			)
		);
	}
}