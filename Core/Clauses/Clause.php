<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

interface Clause {
	public function sql(): string;
}