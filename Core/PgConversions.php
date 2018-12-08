<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

final class PgConversions implements Conversion {
	private $connection;
	private $original;
	private $type;

	public function __construct(
		Connection $connection,
		?string $original,
		string $type
	) {
		$this->connection = $connection;
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
				new PgJsonToArray(
					$this->original,
					$this->type,
					new PgHStoreToArray(
						$this->connection,
						$this->original,
						$this->type,
						new PgIntRangeToArray(
							$this->original,
							$this->type,
							new PgTimestamptzRangeToArray(
								$this->connection,
								$this->original,
								$this->type,
								new PgPointToArray(
									$this->original,
									$this->type,
									new PgArrayToArray(
										$this->connection,
										$this->original,
										$this->type,
										new PgRowToTypedArray(
											$this->connection,
											$this->original,
											$this->type,
											new PgNative($this->original)
										)
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