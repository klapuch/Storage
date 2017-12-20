<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

final class PgPointToArray implements Conversion {
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
		return array_map(
			'floatval',
			(new NativeQuery(
				$this->database,
				'SELECT (:point::POINT)[0] AS x, (:point::POINT)[1] AS y',
				['point' => $this->original]
			))->row()
		);
	}
}