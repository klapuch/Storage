<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class PgDoNothing implements Clause {
	private $clause;

	public function __construct(Clause $clause) {
		$this->clause = $clause;
	}

	public function sql(): string {
		return sprintf('%s DO NOTHING', $this->clause->sql());
	}

}