<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class AnsiOffset implements Clause, Offset {
	private $clause;
	private $offset;

	public function __construct(Clause $clause, int $offset) {
		$this->clause = $clause;
		$this->offset = $offset;
	}

	public function limit(int $limit): Limit {
		return new AnsiLimit($this, $limit);
	}

	public function sql(): string {
		return sprintf('%s OFFSET %s', $this->clause->sql(), $this->offset);
	}

}