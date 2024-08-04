<?php

/**
 * Retrieves a snippet of code around a specific line number from a given file.
 *
 * This function reads the contents of the specified file and extracts a portion
 * of the code around a given line number. It returns an array where the keys
 * are the line numbers and the values are the corresponding lines of code.
 *
 * @param string $file The path to the file from which to retrieve the code snippet.
 * @param int $line The line number around which to extract the code snippet.
 * @param int $linesBefore The number of lines to include before the specified line. Default is 5.
 * @param int $linesAfter The number of lines to include after the specified line. Default is 5.
 * @return array<string, array<string>> An associative array, [rawCode] => [The full file content in string] and [parsedCode] => [where the keys are line numbers and the values are lines of code.]
 * @throws Exception If the file cannot be read or does not exist.
 */
function getCodeSnippet(
	string $file,
	int $line,
	int $linesBefore = 5,
	int $linesAfter = 5
): array {
	if (!file_exists($file) || !is_readable($file)) {
		throw new Exception("Cannot read file: $file");
	}

	$startLine = max(1, $line - $linesBefore);
	$endLine = $line + $linesAfter;

	$code = file($file);
	$snippet = array_slice(
		$code,
		$startLine - 1,
		$endLine - $startLine + 1,
		true
	);

	return [
		'rawCode' => htmlspecialchars(file_get_contents($file)),
		'parsedCode' => $snippet
	];
}
