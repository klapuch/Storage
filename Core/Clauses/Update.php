<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

interface Update extends Clause {
	public function set(array $values): Set;
}