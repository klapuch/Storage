<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

/**
 * String data representing serialized/unserialized structure
 */
class StringData {
	/**
	 * @param mixed $data
	 * @return string
	 */
	public function serialize($data): string {
		if (extension_loaded('igbinary')) {
			return igbinary_serialize($data);
		}
		return serialize($data);
	}

	/**
	 * @param string $data
	 * @return mixed
	 */
	public function unserialize(string $data) {
		if (extension_loaded('igbinary')) {
			return igbinary_unserialize($data);
		}
		return unserialize($data);
	}
}