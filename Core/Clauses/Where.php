<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

interface Where extends Clause {
	public function where(string $condition): self;
	public function orWhere(string $condition): self;
	public function groupBy(array $columns): GroupBy;
	public function having(string $condition): Having;
	public function orderBy(array $orders): OrderBy;
	public function limit(int $limit): Limit;
	public function offset(int $offset): Offset;
}