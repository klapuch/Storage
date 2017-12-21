<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class PgIntRangeToArray implements Conversion {
	private $database;
	private $original;

	public function __construct(\PDO $database, string $original) {
		$this->database = $database;
		$this->original = $original;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		$ranges = (new PgArrayToArray(
			$this->database,
			(new NativeQuery(
				$this->database,
				"SELECT
					ARRAY[
						(SELECT lower(:range::int4range)::text),
						(SELECT upper(:range::int4range)::text),
						hstore(ARRAY['true','false'], ARRAY['[','(']) -> (SELECT lower_inc(:range::int4range)::text),
						hstore(ARRAY['true','false'], ARRAY[']',')']) -> (SELECT upper_inc(:range::int4range)::text)
					]",
				['range' => $this->original]
			))->field(),
			'TEXT'
		))->value();
		return array_map('intval', array_filter($ranges, 'is_numeric')) + $ranges;
	}
}