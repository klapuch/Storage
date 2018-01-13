<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

interface Limit {
	public function offset(int $offset): Offset;
}