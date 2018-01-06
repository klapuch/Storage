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
	 * @return array
	 */
	public function row(): array;

	/**
	 * Multiple rows
	 * @return array
	 */
	public function rows(): array;

	/**
	 * Execute the query
	 * @return \PDOStatement
	 */
	public function execute(): \PDOStatement;
}