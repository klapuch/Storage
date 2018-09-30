<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * In-memory connection
 */
final class MemoryConnection implements Connection {
	private $memory;
	private $origin;

	public function __construct(Connection $origin, array $memory) {
		$this->origin = $origin;
		$this->memory = $memory;
	}

	public function exec(string $statement): void {
		$this->origin->exec($statement);
	}

	public function prepare($statement): \PDOStatement {
		if ($this->direct($statement))
			return new MemoryStatement($this->memory, $statement);
		return $this->origin->prepare($statement);
	}

	public function schema(): Schema {
		return $this->origin->schema();
	}

	private function identifier(string $statement): bool {
		return (bool) preg_match('~^SELECT\s+[a-z_*]~i', $statement);
	}

	private function function(string $statement): bool {
		return (bool) preg_match('~^SELECT\s+\w+\(~i', $statement);
	}

	/**
	 * Will be the statement called directly?
	 * @param string $statement
	 * @return bool
	 */
	private function direct(string $statement): bool {
		return $this->identifier($statement)
			&& !$this->function($statement);
	}
}