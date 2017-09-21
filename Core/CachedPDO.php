<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

/**
 * Cached PDO
 */
final class CachedPDO extends \PDO {
	private static $statements = [];
	private $origin;

	public function __construct(\PDO $origin) {
		$this->origin = $origin;
	}

	public function prepare($statement, $options = []): \PDOStatement {
		if (!isset(static::$statements[$statement]))
			static::$statements[$statement] = $this->origin->prepare($statement, $options);
		return static::$statements[$statement];
	}
}