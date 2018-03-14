<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

use Klapuch\Sql;

/**
 * Query built from SQL clause with parameters
 */
final class BuiltQuery implements Query {
	private $database;
	private $clause;

	public function __construct(MetaPDO $database, Sql\Clause $clause) {
		$this->database = $database;
		$this->clause = $clause;
	}

	/**
	 * @return mixed
	 */
	public function field() {
		return (new TypedQuery(
			$this->database,
			$this->clause->sql(),
			$this->clause->parameters()->binds()
		))->field();
	}

	public function row(int $style = \PDO::FETCH_ASSOC): array {
		return (new TypedQuery(
			$this->database,
			$this->clause->sql(),
			$this->clause->parameters()->binds()
		))->row($style);
	}

	public function rows(int $style = \PDO::FETCH_ASSOC): array {
		return (new TypedQuery(
			$this->database,
			$this->clause->sql(),
			$this->clause->parameters()->binds()
		))->rows($style);
	}

	public function execute(): \PDOStatement {
		return (new TypedQuery(
			$this->database,
			$this->clause->sql(),
			$this->clause->parameters()->binds()
		))->execute();
	}
}