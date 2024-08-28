<?php

namespace PhpSlides\Parser;

use DB;
use PhpSlides\Forge\Forge;

class ORMParser extends Forge
{
	public function parse(string $class)
	{
		$name = explode('\\', $class);
		$db_name = static::format($name[1]);
		$name = static::format(end($name));
		DB::useDB($db_name);

		return $name;
	}
}
