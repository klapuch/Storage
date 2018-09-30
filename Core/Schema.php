<?php
declare(strict_types = 1);

namespace Klapuch\Storage;

interface Schema {
	/**
	 * @return mixed[]
	 */
	public function columns(string $type): array;
}