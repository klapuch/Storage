<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * String data representing serialized/unserialized structure
 */
class StringData {
	private static $loaded = FALSE;

	public function __construct() {
		static::$loaded = extension_loaded('igbinary');
	}

	/**
	 * @param mixed $data
	 * @return string
	 */
	public function serialize($data): string {
		if (static::$loaded) {
			return igbinary_serialize($data);
		}
		return serialize($data);
	}

	/**
	 * @param string $data
	 * @return mixed
	 */
	public function unserialize(string $data) {
		if (static::$loaded) {
			return igbinary_unserialize($data);
		}
		return unserialize($data);
	}
}