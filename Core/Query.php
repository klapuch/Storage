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
	 * @param int $style
	 * @return array
	 */
	public function row(int $style = \PDO::FETCH_ASSOC): array;

	/**
	 * Multiple rows
	 * @param int $style
	 * @return array
	 */
	public function rows(int $style = \PDO::FETCH_ASSOC): array;

	/**
	 * Execute the query
	 * @return \PDOStatement
	 */
	public function execute(): \PDOStatement;
}