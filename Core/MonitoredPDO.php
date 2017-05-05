<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

use Klapuch\Log;

/**
 * Monitored PDO
 */
final class MonitoredPDO extends \PDO {
	private $origin;
	private $logs;

	public function __construct(\PDO $origin, Log\Logs $logs) {
	    $this->origin = $origin;
		$this->logs = $logs;
	}

	public function prepare($statement, $options = []): \PDOStatement {
		$this->monitor($statement);
		return $this->origin->prepare($statement, $options);
	}

	public function query($statement): \PDOStatement {
		$this->monitor($statement);
		return $this->origin->query($statement);
	}

	/**
	 * Monitor the query
	 * @param string $query
	 * @return void
	 */
	private function monitor(string $query): void {
		$this->logs->put(
			new Log\PrettyLog(
				new \Exception($query),
				new Log\PrettySeverity(
					new Log\JustifiedSeverity(Log\Severity::INFO)
				)
			)
		);
	}
}