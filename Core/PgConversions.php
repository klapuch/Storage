<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class PgConversions implements Conversion {
	private $database;
	private $original;
	private $type;

	public function __construct(\PDO $database, ?string $original, string $type) {
		$this->database = $database;
		$this->original = $original;
		$this->type = $type;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		if (strcasecmp($this->type, 'hstore') === 0)
			return (new PgHStoreToArray($this->database, $this->original))->value();
		elseif (strcasecmp($this->type, 'int4range') === 0)
			return (new PgIntRangeToArray($this->database, $this->original))->value();
		elseif (strcasecmp($this->type, 'tstzrange') === 0)
			return (new PgTimestamptzRangeToArray($this->database, $this->original))->value();
		elseif (strcasecmp($this->type, 'point') === 0)
			return (new PgPointToArray($this->database, $this->original))->value();
		elseif (preg_match('~^(\w+)\[\]$~', $this->type, $match))
			return (new PgArrayToArray($this->database, $this->original, $match[1]))->value();
		elseif ($this->compound($this->type)) {
			return (new PgRowToTypedArray(
				new PgRowToArray($this->database, $this->original, $this->type),
				$this->type,
				$this->database
			))->value();
		}
		return $this->original;
	}

	/**
	 * Is the given type compound?
	 * @param string $type
	 * @return bool
	 */
	private function compound(string $type): bool {
		return (bool) (new NativeQuery(
			$this->database,
			'SELECT 1
			FROM information_schema.user_defined_types
			WHERE user_defined_type_name = lower(:type)
			UNION ALL
			SELECT 1
			FROM information_schema.columns
			WHERE table_name = lower(:type)',
			['type' => $type]
		))->field();
	}
}