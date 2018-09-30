<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

use Predis;

/**
 * PDO with meta info
 */
class CachedSchema implements Schema {
	private const NAMESPACE = 'postgres:type:meta:';
	private static $cache = [];

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \Predis\ClientInterface */
	private $redis;

	public function __construct(Connection $connection, Predis\ClientInterface $redis) {
		$this->connection = $connection;
		$this->redis = $redis;
	}

	public function columns(string $table): array {
		if (isset(static::$cache[$table])) {
			return static::$cache[$table];
		}
		$key = self::NAMESPACE . $table;
		if (!$this->redis->exists($key)) {
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
					SELECT
					column_name AS attribute_name,
					CASE WHEN data_type = 'USER-DEFINED' THEN udt_name ELSE data_type END,
					ordinal_position
					FROM information_schema.columns
					WHERE table_name = lower(:type)
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
				ON native_types.data_type = types.data_type"
			);
			$statement->execute(['type' => $table]);
			$this->redis->set($key, (new StringData())->serialize($statement->fetchAll()));
			$this->redis->persist($key);
		}
		return static::$cache[$table] = (new StringData())->unserialize($this->redis->get($key));
	}
}