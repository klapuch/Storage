<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

interface Query {
	/**
	 * Single field
	 * @return mixed
	 */
	public function field();

	/**
	 * Single row
	 * @return mixed[]
	 */
	public function row(): array;

	/**
	 * Multiple rows
	 * @return mixed[]
	 */
	public function rows(): array;

	/**
	 * Execute the query
	 * @return \PDOStatement
	 */
	public function execute(): \PDOStatement;
}
