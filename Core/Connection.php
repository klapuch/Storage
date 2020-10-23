<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

interface Connection {
	public function prepare(string $statement): \PDOStatement;

	public function exec(string $statement): void;
}
