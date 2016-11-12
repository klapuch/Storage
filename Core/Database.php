<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

interface Database {
	public const UNIQUE_CONSTRAINT = '23505';

	/**
	 * Fetch single row
	 * @param string $query
	 * @param array $parameters
	 * @return array
	 */
	public function fetch(string $query, array $parameters = []): array;

	/**
	 * Fetch all rows
	 * @param string $query
	 * @param array $parameters
	 * @return array
	 */
	public function fetchAll(string $query, array $parameters = []): array;

	/**
	 * Fetch single column value
	 * @param string $query
	 * @param array $parameters
	 * @return mixed
	 */
	public function fetchColumn(string $query, array $parameters = []);

	/**
	 * Execute safe query
	 * @param string $query
	 * @param array $parameters
	 * @throws UniqueConstraint
	 * @return \PDOStatement
	 */
	public function query(string $query, array $parameters = []): \PDOStatement;

	/**
	 * Execute dangerous query
	 * @param string $query
	 * @return void
	 */
	public function exec(string $query): void;
}