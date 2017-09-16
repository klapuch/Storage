<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class TypedQuery implements Query {
	private const TYPES = [
		'INTEGER',
		'BOOL',
		'TEXT',
		'HSTORE',
		'XML',
		'JSON',
		'TIMESTAMP',
		'TIMESTAMPTZ',
	];
	private $database;
	private $origin;
	private $conversions;

	public function __construct(\PDO $database, Query $origin, array $conversions) {
		$this->database = $database;
		$this->origin = $origin;
		$this->conversions = $conversions;
	}

	/**
	 * @return mixed
	 */
	public function field() {
		return $this->conversion($this->execute()->fetchColumn(), current($this->conversions));
	}

	public function row(): array {
		return $this->conversions($this->execute()->fetch(), $this->conversions);
	}

	public function rows(): array {
		return array_reduce(
			$this->execute()->fetchAll(),
			function(array $rows, array $row): array {
				$rows[] = $this->conversions($row, $this->conversions);
				return $rows;
			},
			[]
		);
	}

	public function execute(): \PDOStatement {
		if ($this->unsupported($this->conversions)) {
			throw new \UnexpectedValueException(
				sprintf(
					'Following types are not supported: "%s"',
					implode(', ', $this->unsupported($this->conversions))
				)
			);
		}
		return $this->origin->execute();
	}

	/**
	 * Rows converted by conversion lookup table
	 * @param array $rows
	 * @param array $conversions
	 * @return array
	 */
	private function conversions(array $rows, array $conversions): array {
		array_walk(
			$conversions,
			function(string $type, string $column) use (&$rows): void {
				$rows[$column] = $this->conversion($rows[$column], $type);
			}
		);
		return $rows;
	}

	/**
	 * Value converted to proper PHP type
	 * @param mixed $value
	 * @param string $type
	 * @return mixed
	 */
	private function conversion($value, string $type) {
		if (strcasecmp($type, 'HSTORE') === 0)
			return (new PgHStoreToArray($this->database, $value))->value();
		elseif (preg_match('~^(\w+)\[\]$~', $type, $match))
			return (new PgArrayToArray($this->database, $value, $match[1]))->value();
		elseif ($this->compound($type))
			return (new PgRowToArray($this->database, $value, $type))->value();
		return $value;
	}

	/**
	 * All the unsupported types given to conversion
	 * @param array $casts
	 * @return array
	 */
	private function unsupported(array $casts): array {
		return array_udiff(
			$casts,
			$this->types(
				array_map('strtoupper', $casts),
				array_merge(
					self::TYPES,
					array_map(
						function(string $type): string {
							return sprintf('%s[]', $type);
						},
						self::TYPES
					)
				)
			),
			'strcasecmp'
		);
	}

	/**
	 * All the types for conversion
	 * @param array $casts
	 * @param array $types
	 * @return array
	 */
	private function types(array $casts, array $types): array {
		return array_filter(
			$casts,
			function(string $type) use ($types): bool {
				return in_array($type, $types) || $this->compound(rtrim($type, '[]'));
			}
		);
	}

	/**
	 * Is the given type compound?
	 * @param string $type
	 * @return bool
	 */
	private function compound(string $type): bool {
		return (bool) (new ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM information_schema.user_defined_types
			WHERE user_defined_type_name = lower(?)',
			[$type]
		))->field();
	}
}