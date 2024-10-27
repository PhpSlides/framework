<?php

/**
 *
 * @param array $trace
 * @return array
 */
function filterTrace(array $trace)
{
	/**
	 * This Filter and removes all file path that is coming from the vendor folders
	 */
	$majorFilter = array_filter($trace, function ($item) {
		$ss = strpos($item['file'] ?? '', '/vendor/') === false;
		$sss = strpos($item['file'] ?? '', '\vendor\\') === false;

		return $ss && $sss === true;
	});

	/**
	 * This filters and add only file path from the vendor folders
	 */
	$minorFilter = array_filter($trace, function ($item) {
		$ss = strpos($item['file'] ?? '', '/vendor/') !== false;
		$sss = strpos($item['file'] ?? '', '\vendor\\') !== false;

		return $ss || $sss === true;
	});

	/**
	 * Create a new array and merge them together
	 * Major filters first
	 * Then the Minor filters follows
	 */
	$majorFilterValue = array_values($majorFilter);
	$minorFilterValue = array_values($minorFilter);
	$newFilter = array_merge($majorFilterValue, $minorFilterValue);

	/**
	 * Replace generated views files to the corresponding view
	 */
	$newFilter = array_map(function ($item) {
		if (array_key_exists('file', $item)) {
			$item['file'] = str_replace('.g.php', '.php', $item['file']);
			$item['file'] = str_replace('.g.psl', '.psl', $item['file']);
			$item['file'] = str_replace('.g.view.php', '.view.php', $item['file']);
		}

		return $item;
	}, $newFilter);

	return $newFilter;
}
