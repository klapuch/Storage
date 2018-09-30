<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgArrayToArray implements Conversion {
	private $connection;
	private $original;
	private $type;
	private $delegation;

	public function __construct(
		Connection $connection,
		string $original,
		string $type,
		Conversion $delegation
	) {
		$this->connection = $connection;
		$this->original = $original;
		$this->type = $type;
		$this->delegation = $delegation;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		if (preg_match('~(?J)(^(?P<type>\w+)\[\])|(^_(?P<type>\w+))$~', $this->type, $match)) {
			return $this->withComplexTypes(
				json_decode(
					(new NativeQuery(
						$this->connection,
						sprintf('SELECT array_to_json(?::%s[])', $match['type']),
						[$this->original]
					))->field(),
					true
				),
				$this->type
			);
		}
		return $this->delegation->value();
	}

	private function withComplexTypes(array $conversions, string $type): array {
		return array_reduce(
			array_filter($conversions, 'is_array'),
			function(array $complete, $conversion) use ($type): array {
				$types = array_column($this->meta($type), 'data_type', 'attribute_name');
				$missing = array_filter($conversion, 'is_string');
				$complete[] = array_combine(
					array_keys($missing),
					array_map(
						function(string $value, string $type) {
							return (new PgConversions(
								$this->connection,
								$value,
								$type
							))->value();
						},
						array_intersect_key($missing, $types),
						array_intersect_key($types, $missing)
					)
				) + $conversion;
				return $complete;
			},
			[]
		) + $conversions;
	}

	private function meta(string $type): array {
		$columns = $this->connection->schema()->columns($type);
		if ($columns === [] && substr($type, 0, 1) === '_')
			return $this->connection->schema()->columns(substr($type, 1));
		return $columns;
	}
}