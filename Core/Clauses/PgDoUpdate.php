<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class PgDoUpdate implements Clause {
	private $clause;
	private $values;

	public function __construct(Clause $clause, array $values) {
		$this->clause = $clause;
		$this->values = $values;
	}

	public function sql(): string {
		return (new AnsiSet(
			new class ($this->clause) implements Clause {
				private $clause;

				public function __construct(Clause $clause) {
					$this->clause = $clause;
				}

				public function sql(): string {
					return sprintf('%s DO UPDATE', $this->clause->sql());
				}
			},
			$this->values
		))->sql();
	}

}