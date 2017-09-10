<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class TypedQuery implements Query {
	private $database;
	private $origin;
	private $casts;

	public function __construct(\PDO $database, Query $origin, array $casts) {
		$this->database = $database;
		$this->origin = $origin;
		$this->casts = $casts;
	}

	public function field(): void {
	}

	public function row(): array {
		if ($this->unsupported($this->casts)) {
			throw new \UnexpectedValueException(
				sprintf(
					'Following types are not supported: "%s"',
					implode(', ', $this->unsupported($this->casts))
				)
			);
		}
		$rows = $this->origin->row();
		array_walk(
			$this->casts,
			function(string $type, string $column) use (&$rows): void {
				if (strcasecmp($type, 'hstore') === 0) {
					$rows[$column] = (new PostgresHStore($this->database, $rows[$column]))->cast();
				} elseif (preg_match('~^(\w+)\[\]$~', $type, $match)) {
					$rows[$column] = (new PostgresArray($this->database, $rows[$column], $match[1]))->cast();
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