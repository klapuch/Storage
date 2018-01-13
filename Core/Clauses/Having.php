<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

interface Having {
	public function orderBy(array $orders): OrderBy;
	public function limit(int $limit): Limit;
	public function offset(int $offset): Offset;
}