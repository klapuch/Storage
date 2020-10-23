<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * Cached database connection
 */
class CachedConnection implements Connection {
	private Connection $origin;

	private \SplFileInfo $temp;

	public function __construct(Connection $origin, \SplFileInfo $temp) {
		$this->origin = $origin;
		$this->temp = $temp;
	}

	public function prepare(string $statement): \PDOStatement {
		return new CachedPDOStatement(
			$this->origin->prepare($statement),
			$statement,
			$this->temp,
		);
	}

	public function exec(string $statement): void {
		$this->origin->exec($statement);
	}
}
