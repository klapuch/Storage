<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

interface From {
	public function where(string $comparison): Where;
	public function join(string $type, string $table, string $condition): Join;
	public function groupBy(array $columns): GroupBy;
	public function having(string $condition): Having;
	public function orderBy(array $orders): OrderBy;
	public function limit(int $limit): Limit;
	public function offset(int $offset): Offset;
}