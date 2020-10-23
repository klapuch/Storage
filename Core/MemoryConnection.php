<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * In-memory connection
 */
final class MemoryConnection implements Connection {
	/** @var mixed[] */
	private array $memory;

	private Connection $origin;

	/**
	 * @param mixed[] $memory
	 */
	public function __construct(Connection $origin, array $memory) {
		$this->origin = $origin;
		$this->memory = $memory;
	}

	public function exec(string $statement): void {
		$this->origin->exec($statement);
	}

	public function prepare(string $statement): \PDOStatement {
		return new MemoryStatement($this->memory, $statement);
	}
}
