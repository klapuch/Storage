<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * PDO with safe setting
 */
final class SafePDO extends \PDO {
	private const OPTIONS = [
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		\PDO::ATTR_EMULATE_PREPARES => false,
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
	];

	public function __construct(string $dsn, string $user, string $password) {
		parent::__construct($dsn, $user, $password, self::OPTIONS);
	}
}