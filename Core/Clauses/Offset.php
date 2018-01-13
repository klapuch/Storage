<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

interface Offset {
	public function limit(int $limit): Limit;
}