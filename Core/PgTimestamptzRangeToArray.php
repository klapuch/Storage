<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class PgTimestamptzRangeToArray implements Conversion {
	private $database;
	private $original;

	public function __construct(\PDO $database, ?string $original) {
		$this->database = $database;
		$this->original = $original;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		if ($this->original === null)
			return $this->original;
		$ranges = (new PgArrayToArray(
			$this->database,
			(new NativeQuery(
				$this->database,
				"SELECT
					ARRAY[
						(SELECT lower(:range::tstzrange)::text),
						(SELECT upper(:range::tstzrange)::text),
						hstore(ARRAY['true','false'], ARRAY['[','(']) -> (SELECT lower_inc(:range::tstzrange)::text),
						hstore(ARRAY['true','false'], ARRAY[']',')']) -> (SELECT upper_inc(:range::tstzrange)::text)
					]",
				['range' => $this->original]
			))->field(),
			'TEXT'
		))->value();
		return array_map(
			function(string $timestamptz): \DateTimeImmutable {
				return new class($timestamptz) extends \DateTimeImmutable implements \JsonSerializable {
					public function jsonSerialize(): string {
						return (string) $this;
					}

					public function __toString(): string {
						return $this->format('Y-m-d H:i:s.uO');
					}
				};
			},
			[$ranges[0], $ranges[1]]
		) + $ranges;
	}
}