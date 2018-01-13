<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

interface Select {
	public function from(array $tables): From;
}