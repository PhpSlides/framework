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
	if (empty($file) || empty($line)) {
	   $message = 'Unable to read & parse code!';
	   
		return [
			'rawCode' => $message,
			'parsedCode' => [$message]
		];
	}

	if (!file_exists($file) || !is_readable($file)) {
		$path = $GLOBALS['__gen_file_path'];

		if (isset($path) && file_exists($path)) {
			/**
			 * This is coming from view file
			 */
			$code = file($path);
			$content = htmlspecialchars(file_get_contents($path), ENT_NOQUOTES);
			unset($GLOBALS['__gen_file_path']);
			unlink($path);
		} else {
			throw new Exception("Cannot read file: $file");
		}
	} else {
		$code = file($file);
		$content = htmlspecialchars(file_get_contents($file), ENT_NOQUOTES);
	}

	$startLine = max(1, $line - $linesBefore);
	$endLine = $line + $linesAfter;

	$snippet = array_slice(
		$code,
		$startLine - 1,
		$endLine - $startLine + 1,
		true
	);

	return [
		'rawCode' => $content,
		'parsedCode' => $snippet
	];
}
