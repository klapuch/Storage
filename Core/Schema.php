<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

interface Schema {
	public function generate(): void;

	/**
	 * @param string $type
	 * @return mixed[]
	 */
	public function columns(string $type);
}