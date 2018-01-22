<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

interface Set extends Clause {
	public function where(string $comparison): Where;
}