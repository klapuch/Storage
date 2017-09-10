<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class PostgresArray implements Type {
	private $database;
	private $original;
	private $type;

	public function __construct(\PDO $database, string $original, string $type) {
		$this->database = $database;
		$this->original = $original;
		$this->type = $type;
	}

	/**
	 * @return mixed
	 */
	public function cast() {
		return json_decode(
			(new ParameterizedQuery(
				$this->database,
				sprintf('SELECT array_to_json(?::%s[])', $this->type),
				[$this->original]
			))->field(),
			true
		);
	}
}