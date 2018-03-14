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
		return (new PgNullable(
			$this->original,
			new PgText(
				$this->original,
				$this->type,
				new PgHStoreToArray(
					$this->database,
					$this->original,
					$this->type,
					new PgIntRangeToArray(
						$this->original,
						$this->type,
						new PgTimestamptzRangeToArray(
							$this->database,
							$this->original,
							$this->type,
							new PgPointToArray(
								$this->original,
								$this->type,
								new PgArrayToArray(
									$this->database,
									$this->original,
									$this->type,
									new PgRowToTypedArray(
										$this->original,
										$this->type,
										$this->database,
										new PgNative($this->original)
									)
								)
							)
						)
					)
				)
			)
		))->value();
	}
}