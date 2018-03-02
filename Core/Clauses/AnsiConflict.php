<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class AnsiConflict implements Conflict {
	private $clause;
	private $target;

	public function __construct(Clause $clause, array $target) {
		$this->clause = $clause;
		$this->target = $target;
	}

	public function doUpdate(array $values = []): Clause {
		return new PgDoUpdate($this, $values);
	}

	public function doNothing(): Clause {
		return new PgDoNothing($this);
	}

	public function sql(): string {
		return sprintf(
			'%s ON CONFLICT%s',
			$this->clause->sql(),
			$this->target
				? sprintf(' (%s)', implode(', ', $this->target))
				: ''
		);
	}
}