<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * PDO with safe setting
 * PDO with caching ability of prepared statements
 */
final class SafePDO extends \PDO {
	private const OPTIONS = [
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		\PDO::ATTR_EMULATE_PREPARES => false,
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
	];

	private static $statements = [];

	public function __construct(string $dsn, string $user, string $password) {
		parent::__construct($dsn, $user, $password, self::OPTIONS);
	}

	public function prepare($statement, $options = []): \PDOStatement {
		$key = md5($statement);
		if (!isset(static::$statements[$key]))
			static::$statements[$key] = parent::prepare($statement, $options);
		return static::$statements[$key];
	}
}