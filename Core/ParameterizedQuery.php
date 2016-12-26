<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

/**
 * Parameterized query for PDO
 */
final class ParameterizedQuery implements Query {
	private const NAME_APPROACH = 'string',
		PLACEHOLDER_APPROACH = 'integer',
		UNKNOWN_APPROACH = 'null';
	private const PLACEHOLDER = '?',
		NAME_PREFIX = ':';
	private const UNIQUE_CONSTRAINT = 23505;
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

	public function field() {
		return $this->execute()->fetchColumn();
	}

	public function row(): array {
		return $this->execute()->fetch() ?: [];
	}

	public function rows(): array {
		return $this->execute()->fetchAll() ?: [];
	}

	public function execute(): \PDOStatement {
		try {
			$statement = $this->database->prepare($this->statement);
			$statement->execute($this->parameters());
			return $statement;
		} catch(\PDOException $ex) {
			if($ex->getCode() == self::UNIQUE_CONSTRAINT) { // == intentionally
				throw new UniqueConstraint(
					$ex->getMessage(),
					self::UNIQUE_CONSTRAINT,
					$ex
				);
			}
			throw $ex;
		}
	}

	private function statement(): string {
		return preg_replace('~\s+~', ' ', $this->statement);
	}

	private function parameters(): array {
		if($this->mismatched($this->parameters)) {
			throw new \UnexpectedValueException(
				'Parameters must be either named or bare placeholders'
			);
		} elseif(!$this->used($this->statement(), $this->adjustment($this->parameters))) {
			throw new \UnexpectedValueException(
				'Not all parameters are used'
			);
		}
		return $this->adjustment($this->parameters);
	}

	/**
	 * Are the parameters mismatched?
	 * @param array $parameters
	 * @return bool
	 */
	private function mismatched(array $parameters): bool {
		return count(array_unique(array_map('gettype', array_keys($parameters)))) > 1;
	}

	/**
	 * Are the parameters used inside statement?
	 * @param array $parameters
	 * @param string $statement
	 * @return bool
	 */
	private function used(string $statement, array $parameters): bool {
		$statementParameters = $this->statementParameters($statement, $parameters);
		$matched = $this->matched($statementParameters, $parameters);
		if($matched && $this->approach($parameters) === self::NAME_APPROACH) {
			return count($parameters) === count(
				array_intersect($statementParameters, array_keys($parameters))
			);
		}
		return $matched;
	}

	/**
	 * Parameters extracted from the statement
	 * @param string $statement
	 * @param array $parameters
	 * @return array
	 */
	private function statementParameters(string $statement, array $parameters): array {
		$approaches = [
			self::UNKNOWN_APPROACH => ['placeholders', 'names'],
			self::PLACEHOLDER_APPROACH => ['placeholders'],
			self::NAME_APPROACH => ['names'],
		];
		return array_reduce(
			$approaches[$this->approach($parameters)],
			function(array $parameters, string $type) use($statement): array {
				return array_merge(
					call_user_func_array([$this, $type], [$statement]),
					$parameters
				);
			},
			[]
		);
	}

	/**
	 * Do the statement parameters match with parameters?
	 * @param array $statementParameters
	 * @param array $parameters
	 * @return bool
	 */
	private function matched(array $statementParameters, array $parameters): bool {
		return count($parameters) === count($statementParameters);
	}

	/**
	 * All the placeholders extracted from the statement
	 * @param string $statement
	 * @return array
	 */
	private function placeholders(string $statement): array {
		return array_fill(
			0,
			substr_count($statement, self::PLACEHOLDER),
			self::PLACEHOLDER
		);
	}

	/**
	 * All the names extracted from the statement
	 * TODO: Use pure regular
	 * @param string $statement
	 * @return array
	 */
	private function names(string $statement): array {
		return array_unique(
			preg_replace(
				sprintf('~[^\w\d%s]~', self::NAME_PREFIX),
				'',
				preg_replace(
					'~\s.*$~',
					'',
					array_filter(
						preg_grep(
							sprintf('~%s[\w\d]+~', self::NAME_PREFIX),
							array_unique(explode(' ', $statement))
						),
						function(string $keyword): bool {
							return strpos($keyword, '::') === false;
						}
					)
				)
			)
		);
	}

	/**
	 * What approach is used for parameterized query?
	 * @param array $parameters
	 * @return string
	 */
	private function approach(array $parameters): string {
		return strtolower(gettype(key($parameters)));
	}

	/**
	 * Adjusted parameters
	 * @param array $parameters
	 * @return array
	 */
	private function adjustment(array $parameters): array {
		if($this->approach($parameters) === self::PLACEHOLDER_APPROACH)
			return array_values($parameters);
		return array_reduce(
			array_keys(
				array_filter(
					$parameters,
					function(string $name): bool {
						return substr($name, 0, 1) !== self::NAME_PREFIX;
					},
					ARRAY_FILTER_USE_KEY
				)
			),
			function(array $names, string $name) use($parameters): array {
				unset($names[$name]);
				$names[self::NAME_PREFIX . $name] = $parameters[$name];
				return $names;
			},
			$parameters
		);
	}
}