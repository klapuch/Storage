<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class FakeDatabase implements Database {
	public function fetch(string $query, array $parameters = []): array {
		return ['fetch'];
	}

	public function fetchAll(string $query, array $parameters = []): array {
		return ['fetchAll'];
	}

	public function fetchColumn(string $query, array $parameters = []) {
		return 'fetchColumn';
	}

	public function query(
		string $query,
		array $parameters = []
	): \PDOStatement {
			return new \PDOStatement();
	}

	public function exec(string $query) {

	}
}
