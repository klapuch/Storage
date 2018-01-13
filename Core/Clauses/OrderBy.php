<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

interface OrderBy extends Clause {
	public function limit(int $limit): Limit;
	public function offset(int $offset): Offset;
}