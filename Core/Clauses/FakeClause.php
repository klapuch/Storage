<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Clauses;

final class FakeClause implements Clause {
	public function sql(): string {
		return '';
	}
}