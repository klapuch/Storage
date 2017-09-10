<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

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

	public function row(): array {
		return current($this->set);
	}

	public function rows(): array {
		return $this->set;
	}

	public function execute(): \PDOStatement {
	}
}