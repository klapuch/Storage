<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

use PDO;

final class FakeQuery implements Query {
	private $set;

	public function __construct(array $set) {
		$this->set = $set;
	}

	/**
	 * @return mixed
	 */
	public function field() {
		return current($this->set);
	}

	public function row(int $style = \PDO::FETCH_ASSOC): array {
		return current($this->set);
	}

	public function rows(int $style = \PDO::FETCH_ASSOC): array {
		return $this->set;
	}

	public function execute(): \PDOStatement {
		return new class($this->set) extends \PDOStatement {
			private $set;

			public function __construct(array $set) {
				$this->set = $set;
			}

			/**
			 * @return array
			 */
			public function fetch(
				$fetchStyle = null,
				$cursorOrientation = PDO::FETCH_ORI_NEXT,
				$cursorOffset = 0
			): array {
				return current($this->set);
			}

			/**
			 * @return array
			 */
			public function fetchAll(
				$fetchStyle = null,
				$fetchArgument = null,
				$ctorArgs = []
			): array {
				return $this->set;
			}

			/**
			 * @return mixed
			 */
			public function fetchColumn($columnNumber = 0) {
				return current($this->set);
			}
		};
	}
}