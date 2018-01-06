<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * Query used as application layer
 */
final class ApplicationQuery implements Query {
	private $origin;

	public function __construct(Query $origin) {
		$this->origin = $origin;
	}

	/**
	 * @return mixed
	 */
	public function field() {
		try {
			return $this->origin->field();
		} catch (\PDOException $ex) {
			throw $this->amend($ex);
		}
	}

	public function row(): array {
		try {
			return $this->origin->row();
		} catch (\PDOException $ex) {
			throw $this->amend($ex);
		}
	}

	public function rows(): array {
		try {
			return $this->origin->rows();
		} catch (\PDOException $ex) {
			throw $this->amend($ex);
		}
	}

	public function execute(): \PDOStatement {
		try {
			return $this->origin->execute();
		} catch (\PDOException $ex) {
			throw $this->amend($ex);
		}
	}

	private function amend(\PDOException $ex): \Throwable {
		if ($ex->errorInfo[0] === 'P0001') {
			preg_match('~ERROR:\s+(?<message>.+)~', $ex->getMessage(), $matches);
			return new \UnexpectedValueException($matches['message'], 0, $ex);
		}
		return $ex;
	}
}