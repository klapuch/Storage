<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

/**
 * Calling PDO::prepare() and PDOStatement::execute() for statements that will be
 * issued multiple times with different parameter values optimizes the performance
 * of your application by allowing the driver to negotiate client and/or
 * server side caching of the query plan and meta information
 */
final class SideCachedPDO extends \PDO {
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