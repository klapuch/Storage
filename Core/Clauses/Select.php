<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

interface Select extends Clause {
	public function from(array $tables): From;
}