<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

use Klapuch\Lock;

final class CachedPDOStatement extends \PDOStatement {
	private const NAMESPACE = 'postgres_column_meta';

	/** @var mixed[] */
	private static $cache = [];

	/** @var \PDOStatement */
	private $origin;

	/** @var string */
	private $statement;

	/** @var \SplFileInfo */
	private $temp;

	public function __construct(
		\PDOStatement $origin,
		string $statement,
		\SplFileInfo $temp
	) {
		$this->origin = $origin;
		$this->statement = $statement;
		$this->temp = $temp;
	}

	public function execute($inputParameters = null): bool {
		return $this->origin->execute(...func_get_args());
	}

	public function fetch(
		$fetchStyle = null,
		$cursorOrientation = \PDO::FETCH_ORI_NEXT,
		$cursorOffset = 0
	): array {
		return $this->origin->fetch(...func_get_args()) ?: [];
	}

	public function fetchAll(
		$fetchStyle = null,
		$fetchArgument = null,
		$ctorArgs = null
	): array {
		return (array) $this->origin->fetchAll(...func_get_args());
	}

	/**
	 * @param int $columnNumber
	 * @return mixed
	 */
	public function fetchColumn($columnNumber = 0) {
		return $this->origin->fetchColumn(...func_get_args());
	}

	public function columnCount(): int {
		return $this->origin->columnCount();
	}

	public function getColumnMeta($column): array {
		$dir = implode(DIRECTORY_SEPARATOR, [$this->temp->getPathname(), self::NAMESPACE, md5($this->statement)]);
		$filename = sprintf('%s/%d.php', $dir, $column);
		if (isset(self::$cache[$filename][$column])) {
			return self::$cache[$filename][$column];
		}
		if (!is_file($filename)) {
			(new Lock\Semaphore($filename))->synchronized(function () use ($dir, $column, $filename): void {
				if (!is_file($filename)) {
					@mkdir($dir, 0777, true); // @ directory may exists
					if (@file_put_contents($filename, sprintf('<?php return %s;', var_export($this->origin->getColumnMeta($column), true))) === false) {
						throw new \RuntimeException(sprintf('File is "%s" is not writable', $filename));
					}
				}
			});
		}
		self::$cache[$filename][$column] = require $filename;
		return self::$cache[$filename][$column];
	}
}