<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

use Predis;

/**
 * Cached database connection
 */
class CachedConnection implements Connection {
	private $origin;
	private $redis;

	public function __construct(Connection $origin, Predis\ClientInterface $redis) {
		$this->origin = $origin;
		$this->redis = $redis;
	}

	public function prepare($statement): \PDOStatement {
		return new CachedPDOStatement(
			$this->origin->prepare($statement),
			$statement,
			$this->redis
		);
	}

	public function exec(string $statement): void {
		$this->origin->exec($statement);
	}

	public function schema(): Schema {
		return new CachedSchema($this, $this->redis);
	}
}