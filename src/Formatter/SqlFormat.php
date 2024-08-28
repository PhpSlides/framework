<?php

namespace PhpSlides\Formatter;

abstract class SqlFormat
{
	protected function format(array $constraint)
	{
		$column = $this->column_types;
		$definition = "{$column['COLUMN_NAME']} ";

		if ($column['TYPE']) {
			$definition .= $column['TYPE'];

			if ($column['LENGTH']) {
				$definition .= "({$column['LENGTH']})";
			}
		}

		if ($column['UNSIGNED'] == 'TRUE') {
			$definition .= ' UNSIGNED';
		}

		if ($column['ZEROFILL'] == 'TRUE') {
			$definition .= ' ZEROFILL';
		}

		if ($column['CHARACTER']) {
			$definition .= " CHARACTER SET {$column['CHARACTER']}";
		}

		if ($column['COLLATION']) {
			$definition .= " COLLATE {$column['COLLATION']}";
		}

		if ($column['NULL'] == 'FALSE') {
			$definition .= ' NOT NULL';
		} elseif ($column['NULL'] == 'TRUE') {
			$definition .= ' NULL';
		}

		if ($column['DEFAULT'] !== null) {
			$types = ['NULL', '0', 'TRUE', 'FALSE', 'CURRENT_TIMESTAMP'];

			if (in_array($column['DEFAULT'], $types)) {
				$definition .= " DEFAULT {$column['DEFAULT']}";
			} else {
				$definition .= " DEFAULT '{$column['DEFAULT']}'";
			}
		}

		if ($column['AUTO_INCREMENT'] == 'TRUE') {
			$definition .= ' AUTO_INCREMENT';
		}

		if ($column['UNIQUE'] == 'TRUE') {
			$constraint['UNIQUE'][] = $column['COLUMN_NAME'];
		}

		if ($column['INDEX'] == 'TRUE') {
			$constraint['INDEX'][] = $column['COLUMN_NAME'];
		}

		if ($column['PRIMARY'] == 'TRUE') {
			$constraint['PRIMARY'][] = $column['COLUMN_NAME'];
		}

		if ($column['CHECK']) {
			$definition .= " CHECK ({$column['CHECK']})";
		}

		if ($column['FOREIGN'] == true) {
			$constraint['FOREIGN'][] = $column['COLUMN_NAME'];
		}

		if ($column['REFERENCES']) {
			$constraint['REFERENCES'][$column['COLUMN_NAME']] =
				$column['REFERENCES'];
		}

		if ($column['DELETE']) {
			$constraint['DELETE'][$column['COLUMN_NAME']] = $column['DELETE'];
		}

		if ($column['UPDATE']) {
			if ($column['FOREIGN']) {
				$constraint['UPDATE'][$column['COLUMN_NAME']] = $column['UPDATE'];
			} else {
				$definition .= " ON UPDATE {$column['UPDATE']}";
			}
		}

		if ($column['COMMENT']) {
			$definition .= " COMMENT '{$column['COMMENT']}'";
		}

		if ($column['VISIBLE'] !== null) {
			$definition .= ' ' . ($column['VISIBLE'] ? 'VISIBLE' : 'INVISIBLE');
		}

		if ($column['STORAGE']) {
			$definition .= " STORAGE {$column['STORAGE']}";
		}

		if ($column['GENERATED']) {
			$definition .= " GENERATED ALWAYS AS ({$column['GENERATED']})";
		}

		if ($column['VIRTUAL']) {
			$definition .= ' VIRTUAL';
		}

		if ($column['PERSISTENT']) {
			$definition .= ' PERSISTENT';
		}

		if ($column['OTHERS']) {
			$constraint['OTHERS'][] = $column['OTHERS'];
		}

		return [trim($definition), $constraint];
	}
}
