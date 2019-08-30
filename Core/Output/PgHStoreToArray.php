<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Output;

use Klapuch\Storage;

final class PgHStoreToArray implements Conversion {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var string */
	private $original;

	/** @var string */
	private $type;

	/** @var \Klapuch\Storage\Output\Conversion */
	private $delegation;

	public function __construct(Storage\Connection $connection, string $original, string $type, Conversion $delegation) {
		$this->connection = $connection;
		$this->original = $original;
		$this->type = $type;
		$this->delegation = $delegation;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		if (strcasecmp($this->type, 'hstore') === 0) {
			$keysValues = (new Storage\NativeQuery(
				$this->connection,
				'SELECT key, value FROM each(?::hstore)',
				[$this->original]
			))->rows();
			return array_combine(array_column($keysValues, 'key'), array_column($keysValues, 'value'));
		}
		return $this->delegation->value();
	}
}