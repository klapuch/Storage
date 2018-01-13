<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

interface Offset extends Clause {
	public function limit(int $limit): Limit;
}