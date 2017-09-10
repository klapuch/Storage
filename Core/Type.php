<?php
declare(strict_types = 1);
namespace Klapuch\Storage;

interface Type {
	/**
	 * Cast type to the PHP one
	 * @return mixed
	 */
	public function cast();
}