<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

use Klapuch\Lock;

/**
 * PDO with meta info
 */
class CachedSchema implements Schema {
	private const NAMESPACE = 'postgres_type_meta';

	/** @var mixed[] */
	private static $cache = [];

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \SplFileInfo */
	private $temp;

	public function __construct(Connection $connection, \SplFileInfo $temp) {
		$this->connection = $connection;
		$this->temp = $temp;
	}

	public function columns(string $type): array {
		if (isset(static::$cache[$type])) {
			return static::$cache[$type];
		}
		$dir = $this->temp->getPathname() . DIRECTORY_SEPARATOR . self::NAMESPACE;
		$filename = sprintf('%s/%s/%s.php', $this->temp->getPathname(), self::NAMESPACE, $type);
		if (!is_file($filename)) {
			(new Lock\Semaphore($filename))->synchronized(function () use ($filename, $type, $dir): void {
				if (!is_file($filename)) {
					@mkdir($dir, 0777); // @ directory may exists
					if (@file_put_contents($filename, sprintf('<?php return %s;', var_export($this->structure($type), true))) === false) {
						throw new \RuntimeException(sprintf('File is "%s" is not writable', $filename));
					}
				}
			});

		}
		self::$cache[$type] = require $filename;
		return self::$cache[$type];
	}

	/**
	 * @return mixed[]
	 */
	private function structure(string $table): array
	{
		$statement = $this->connection->prepare(
			"SELECT attribute_name,
			types.data_type,
			ordinal_position,
			COALESCE(native_type, types.data_type) AS native_type
			FROM (
				SELECT attribute_name,
				CASE WHEN data_type = 'USER-DEFINED' THEN attribute_udt_name ELSE data_type END,
				ordinal_position
				FROM information_schema.attributes
				WHERE udt_name = lower(:type)
				UNION ALL
				SELECT column_name,
				CASE WHEN data_type = 'USER-DEFINED' THEN udt_name ELSE data_type END,
				ordinal_position
				FROM information_schema.columns
				WHERE table_name = lower(:type)
			) types
			LEFT JOIN (
				SELECT data_type, native_type
				FROM (
					VALUES
					('integer', 'integer'),
					('character varying', 'string'),
					('text', 'string'),
					('character', 'string'),
					('numeric', 'double'),
					('bigint', 'integer'),
					('smallint', 'integer'),
					('boolean', 'boolean')
				) AS t (data_type, native_type)
			) native_types
			ON native_types.data_type = types.data_type
			ORDER BY ordinal_position"
		);
		$statement->execute(['type' => $table]);
		return $statement->fetchAll() ?: [];
	}
}