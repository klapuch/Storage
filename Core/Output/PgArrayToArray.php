<?php
declare(strict_types = 1);

namespace Klapuch\Storage\Output;

use Klapuch\Storage;

final class PgArrayToArray implements Conversion {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var string */
	private $original;

	/** @var string */
	private $type;

	/** @var \Klapuch\Storage\Output\Conversion */
	private $delegation;

	public function __construct(
		Storage\Connection $connection,
		string $original,
		string $type,
		Conversion $delegation
	) {
		$this->connection = $connection;
		$this->original = $original;
		$this->type = $type;
		$this->delegation = $delegation;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		if (preg_match('~(?J)(^(?P<type>\w+)\[\])|(^_(?P<type>\w+))$~', $this->type, $match)) {
			return json_decode(
				(new Storage\NativeQuery(
					$this->connection,
					sprintf('SELECT array_to_json(?::%s[])', $match['type']),
					[$this->original]
				))->field(),
				true
			);
		}
		return $this->delegation->value();
	}
}