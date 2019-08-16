<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * PDO with meta info
 */
class CachedSchema implements Schema {
	/** @var mixed[] */
	private static $cache = [];

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \SplFileInfo */
	private $file;

	public function __construct(Connection $connection, \SplFileInfo $file) {
		$this->connection = $connection;
		$this->file = $file;
	}


	public function columns(string $type): array
	{
		if (!isset(static::$cache[$type])) {
			$schema = require $this->file->getPathname();
			static::$cache[$type] = $schema[strtolower($type)] ?? [];
		}
		return static::$cache[$type];
	}

	public function generate(): void {
		$statement = $this->connection->prepare(
			"SELECT table_name, jsonb_agg(
				jsonb_build_object(
					'attribute_name', attribute_name,
					'data_type', types.data_type,
					'ordinal_position', ordinal_position,
					'native_type', COALESCE(native_type, types.data_type)
				)
			)
			FROM (
				SELECT attribute_name,
				CASE WHEN data_type = 'USER-DEFINED' THEN attribute_udt_name ELSE data_type END,
				ordinal_position,
				udt_name AS table_name
				FROM information_schema.attributes
				UNION ALL
				SELECT column_name,
				CASE WHEN data_type = 'USER-DEFINED' THEN udt_name ELSE data_type END,
				ordinal_position,
				table_name
				FROM information_schema.columns
				ORDER BY ordinal_position
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
			GROUP BY table_name"
		);
		$statement->execute();
		if (@file_put_contents($this->file->getPathname(), static::phpFile($statement->fetchAll())) === false) {
			throw new \RuntimeException('Can not write to file.');
		}
	}

	private static function phpFile(array $schema): string {
		$items = array_map(static function (array $column): string {
			return sprintf("'%s' => %s", $column['table_name'], var_export(json_decode($column['jsonb_agg'], true), true));
		}, $schema);
		return sprintf('<?php return array(%s);', implode(',', $items));
	}
}