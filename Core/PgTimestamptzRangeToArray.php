<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgTimestamptzRangeToArray implements Conversion {
	private $database;
	private $original;
	private $type;
	private $delegation;

	public function __construct(\PDO $database, string $original, string $type, Conversion $delegation) {
		$this->database = $database;
		$this->original = $original;
		$this->type = $type;
		$this->delegation = $delegation;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		if (strcasecmp($this->type, 'tstzrange') === 0) {
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
				'TEXT[]',
				new NoConversion()
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
		return $this->delegation->value();
	}
}