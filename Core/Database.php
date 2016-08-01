<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

interface Database {
	const INTEGRITY_CONSTRAINT = '23000';

	public function fetch(string $query, array $parameters = []);
	public function fetchAll(string $query, array $parameters = []);
	public function fetchColumn(string $query, array $parameters = []);
	public function query(string $query, array $parameters = []);
	public function exec(string $query);
}