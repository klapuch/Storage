<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class CachedPDOStatement extends \PDOStatement {
	private const NAMESPACE = 'postgres_column_meta';

	private \PDOStatement $origin;

	private string $statement;

	private \SplFileInfo $temp;

	/** @var array<string, array<mixed, mixed>> */
	private static array $cache = [];

	public function __construct(\PDOStatement $origin, string $statement, \SplFileInfo $temp) {
		$this->origin = $origin;
		$this->statement = $statement;
		$this->temp = $temp;
	}

	public function execute($inputParameters = null): bool {
		return $this->origin->execute(...func_get_args());
	}

	/**
	 * @return mixed[]
	 */
	public function fetch($fetchStyle = null, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $cursorOffset = 0): array {
		$row = $this->origin->fetch(...func_get_args());
		return $row === false ? [] : $row;
	}

	/**
	 * @return mixed[]
	 */
	public function fetchAll($fetchStyle = null, $fetchArgument = null, $ctorArgs = null): array {
		return (array) $this->origin->fetchAll(...func_get_args());
	}

	/**
	 * @return int|string|false|null
	 */
	public function fetchColumn($columnNumber = 0) {
		return $this->origin->fetchColumn(...func_get_args());
	}

	public function columnCount(): int {
		return $this->origin->columnCount();
	}

	/**
	 * @param mixed $column
	 * @return array<string, array<mixed, mixed>>
	 */
	public function getColumnMeta($column): array {
		$dir = implode(DIRECTORY_SEPARATOR, [$this->temp->getPathname(), self::NAMESPACE, md5($this->statement)]);
		$filename = sprintf('%s/%d.php', $dir, $column);
		if (isset(self::$cache[$filename][$column])) {
			return self::$cache[$filename][$column];
		}
		if (!is_dir($dir) && !@mkdir($dir, 0777, true)) {
			throw new \RuntimeException('Can not create directory.');
		}
		$this->cache(new \SplFileInfo($filename), self::raw($this->origin->getColumnMeta($column)));
		self::$cache[$filename][$column] = require $filename;
		return self::$cache[$filename][$column];
	}

	private function cache(\SplFileInfo $file, string $data): void
	{
		if (!$file->isFile()) {
			$lock = sprintf('%s.lock', $file->getPathname());
			$handle = fopen($lock, 'c+');
			if ($handle === false || !flock($handle, LOCK_EX)) {
				throw new \RuntimeException(
					\sprintf('Unable to create or acquire exclusive lock on file "%s".', $lock),
				);
			}
			if (!$file->isFile()) {
				$temp = sprintf('%s.temp', $file->getPathname());
				if (@file_put_contents($temp, $data) === false) {
					throw new \RuntimeException(sprintf('Can not write to file "%s".', $temp));
				}
				rename($temp, $file->getPathname()); // atomic replace
				if (function_exists('opcache_invalidate')) {
					opcache_invalidate($file->getPathname(), true);
				}
			}
			flock($handle, LOCK_UN);
			fclose($handle);
			@unlink($lock); // intentionally @ - file may become locked on Windows
		}
	}

	/**
	 * @param mixed[] $data
	 */
	private static function raw(array $data): string
	{
		return sprintf('<?php return %s;', var_export($data, true));
	}
}
