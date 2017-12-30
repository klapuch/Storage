<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class PgConversions implements Conversion {
	private $database;
	private $original;
	private $type;

	public function __construct(MetaPDO $database, ?string $original, string $type) {
		$this->database = $database;
		$this->original = $original;
		$this->type = $type;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		if ($this->original === null || strcasecmp('text', $this->type) === 0)
			return $this->original;
		elseif (strcasecmp($this->type, 'hstore') === 0)
			return (new PgHStoreToArray($this->database, $this->original))->value();
		elseif (strcasecmp($this->type, 'int4range') === 0)
			return (new PgIntRangeToArray($this->original))->value();
		elseif (strcasecmp($this->type, 'tstzrange') === 0)
			return (new PgTimestamptzRangeToArray($this->database, $this->original))->value();
		elseif (strcasecmp($this->type, 'point') === 0)
			return (new PgPointToArray($this->original))->value();
		elseif (preg_match('~^(?P<type>\w+)\[\]$~', $this->type, $match))
			return (new PgArrayToArray($this->database, $this->original, $match['type']))->value();
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
		return (bool) $this->database->meta($type);
	}
}