<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

interface InsertInto extends Clause {
	public function returning(array $columns): Returning;
}