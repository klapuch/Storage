<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

use Tracy;

final class MonitoredDatabase implements Database {
	private $origin;

	public function __construct(Database $origin) {
	    $this->origin = $origin;
	}

	public function fetch(string $query, array $parameters = []): array {
		$this->monitor($query);
		return $this->origin->fetch($query, $parameters);
	}

	public function fetchAll(string $query, array $parameters = []): array {
		$this->monitor($query);
		return $this->origin->fetchAll($query, $parameters);
	}

	public function fetchColumn(string $query, array $parameters = []) {
		$this->monitor($query);
		return $this->origin->fetchColumn($query, $parameters);
	}

	public function query(string $query, array $parameters = []): \PDOStatement {
		$this->monitor($query);
		return $this->origin->query($query, $parameters);
	}

	public function exec(string $query) {
		$this->monitor($query);
		return $this->origin->exec($query);
	}

	/**
	 * Monitor the query
	 * @param string $query
	 * @return void
	 */
	private function monitor(string $query) {
		Tracy\Debugger::barDump(
			$query,
			$this->operation($query),
			[Tracy\Dumper::TRUNCATE => 5000]
		);
	}

	/**
	 * Operation in SQL query - should be one of INSERT, SELECT, UPDATE, DELETE
	 * @param string $query
	 * @return string
	 */
	private function operation(string $query): string {
		return strtoupper(substr($query, 0, strpos($query, ' ')));
	}
}