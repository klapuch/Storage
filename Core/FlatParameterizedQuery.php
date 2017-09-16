<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

/**
 * Parameters made flat and passed to PDO as ParameterizedQuery
 * [book => [author => myself]] made book_author with value myself
 * @see \Klapuch\Storage\ParameterizedQuery
 */
final class FlatParameterizedQuery implements Query {
	private $database;
	private $statement;
	private $parameters;

	public function __construct(
		\PDO $database,
		string $statement,
		array $parameters = []
	) {
		$this->database = $database;
		$this->statement = $statement;
		$this->parameters = $parameters;
	}

	/**
	 * @return mixed
	 */
	public function field() {
		return (new ParameterizedQuery(
			$this->database,
			$this->statement,
			$this->flatten($this->parameters)
		))->field();
	}

	public function row(): array {
		return (new ParameterizedQuery(
			$this->database,
			$this->statement,
			$this->flatten($this->parameters)
		))->row();
	}

	public function rows(): array {
		return (new ParameterizedQuery(
			$this->database,
			$this->statement,
			$this->flatten($this->parameters)
		))->rows();
	}

	public function execute(): \PDOStatement {
		return (new ParameterizedQuery(
			$this->database,
			$this->statement,
			$this->flatten($this->parameters)
		))->execute();
	}

	private function flatten($array, $prefix = ''): array {
		return array_reduce(
			array_keys($array),
			function(array $flatten, $key) use ($array, $prefix): array {
				if (is_array($array[$key]))
					$flatten += $this->flatten($array[$key], $prefix . $key . '_');
				else $flatten[$prefix . $key] = $array[$key];
				return $flatten;
			},
			[]
		);
	}
}