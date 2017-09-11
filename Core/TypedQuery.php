<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class TypedQuery implements Query {
	private $database;
	private $origin;
	private $conversions;

	public function __construct(\PDO $database, Query $origin, array $conversions) {
		$this->database = $database;
		$this->origin = $origin;
		$this->conversions = $conversions;
	}

	public function field(): void {
	}

	public function row(): array {
		if ($this->unsupported($this->conversions)) {
			throw new \UnexpectedValueException(
				sprintf(
					'Following types are not supported: "%s"',
					implode(', ', $this->unsupported($this->conversions))
				)
			);
		}
		$rows = $this->origin->row();
		array_walk(
			$this->conversions,
			function(string $type, string $column) use (&$rows): void {
				if (strcasecmp($type, 'hstore') === 0) {
					$rows[$column] = (new PgHStoreToArray($this->database, $rows[$column]))->value();
				} elseif (preg_match('~^(\w+)\[\]$~', $type, $match)) {
					$rows[$column] = (new PgArrayToArray($this->database, $rows[$column], $match[1]))->value();
				}
			}
		);
		return $rows;
	}

	public function rows(): array {
	}

	public function execute(): \PDOStatement {
		return $this->origin->execute();
	}

	private function unsupported(array $casts): array {
		return array_diff($casts, $this->types($casts));
	}

	private function types(array $casts): array {
		return array_filter(
			$casts,
			function(string $type): bool {
				return strcasecmp($type, 'hstore') === 0 || strpos($type, '[]') !== false;
			}
		);
	}
}