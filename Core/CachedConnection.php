<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * Cached database connection
 */
class CachedConnection implements Connection {
	/** @var \Klapuch\Storage\Connection */
	private $origin;

	/** @var \SplFileInfo */
	private $file;

	public function __construct(Connection $origin, \SplFileInfo $file) {
		$this->origin = $origin;
		$this->file = $file;
	}

	public function prepare($statement): \PDOStatement {
		return new CachedPDOStatement(
			$this->origin->prepare($statement),
			$statement,
			$this->file
		);
	}

	public function exec(string $statement): void {
		$this->origin->exec($statement);
	}

	public function schema(): Schema {
		return new CachedSchema($this, $this->file);
	}
}