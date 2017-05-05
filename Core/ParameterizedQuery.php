<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

/**
 * Parameterized query for PDO
 */
final class ParameterizedQuery implements Query {
	private const POSITIONAL_APPROACH = 'integer';
	private const UNIQUE_CONSTRAINT = '23505';
	private const INVALID_PARAMETER_COUNT = ['HY093', '08P01'];
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
		} catch (\PDOException $ex) {
			throw $this->amend($ex);
		}
	}

	/**
	 * All the needed and checked parameters
	 * @throws \UnexpectedValueException
	 * @return array
	 */
	private function parameters(): array {
		if ($this->mixed($this->parameters))
			throw new \UnexpectedValueException('Parameters can not be mixed');
		return $this->adjustment($this->parameters);
	}

	/**
	 * Are the parameters mixed?
	 * @param array $parameters
	 * @return bool
	 */
	private function mixed(array $parameters): bool {
		return count(array_unique(array_map('gettype', array_keys($parameters)))) > 1;
	}

	/**
	 * Adjusted parameters
	 * @param array $parameters
	 * @return array
	 */
	private function adjustment(array $parameters): array {
		if ($this->approach($parameters) === self::POSITIONAL_APPROACH)
			return array_values($parameters);
		return $parameters;
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
	 * Amend the given exception to the more comprehensibility format
	 * @param \Throwable $exception
	 * @return \Throwable
	 */
	private function amend(\Throwable $exception): \Throwable {
		if ($exception->getCode() === self::UNIQUE_CONSTRAINT) {
			return new UniqueConstraint(
				$exception->getMessage(),
				(int) $exception->getCode(),
				$exception
			);
		} elseif (in_array($exception->getCode(), self::INVALID_PARAMETER_COUNT, true)) {
			return new \UnexpectedValueException(
				'Not all parameters are used',
				(int) $exception->getCode(),
				$exception
			);
		}
		return $exception;
	}
}