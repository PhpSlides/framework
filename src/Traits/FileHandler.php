<?php

namespace PhpSlides\Core\Traits;

use PhpSlides\Exception;
use PhpSlides\Core\Logger\Logger;

trait FileHandler
{
	use Logger;

	/**
	 * Get the MIME content type for a file.
	 *
	 * This method returns the MIME type of a file based on its extension
	 * and the file's contents. If the `fileinfo` extension is not enabled,
	 * an exception is thrown.
	 *
	 * @param string $filename The path to the file whose MIME type is being determined.
	 * @return bool|string The MIME type of the file as a string, or `false` if the file doesn't exist.
	 * @throws Exception If the `fileinfo` extension is not enabled in PHP.
	 */
	public static function file_type (string $filename): bool|string
	{
		if (is_file($filename))
		{
			if (!extension_loaded('fileinfo'))
			{
				throw new Exception(
				 'Fileinfo extension is not enabled. Please enable it in your php.ini configuration.',
				);
			}

			$file_info = finfo_open(FILEINFO_MIME_TYPE);
			$file_type = finfo_file($file_info, $filename);
			finfo_close($file_info);

			$file_ext = explode('.', $filename);
			$file_ext = strtolower(end($file_ext));

			if (
			$file_type === 'text/plain' ||
			$file_type === 'application/x-empty' ||
			$file_type === 'application/octet-stream'
			)
			{
				switch ($file_ext)
				{
					case 'css':
						return 'text/css';
					case 'txt':
						return 'text/plain';
					case 'csv':
						return 'text/csv';
					case 'htm':
						return 'text/htm';
					case 'html':
						return 'text/html';
					case 'php':
						return 'text/html';
					case 'psl':
						return 'text/html';
					case 'xml':
						return 'text/xml';
					case 'js':
						return 'application/javascript';
					case 'pdf':
						return 'application/pdf';
					case 'doc':
						return 'application/msword';
					case 'docx':
						return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
					case 'xls':
						return 'application/vnd.ms-excel';
					case 'xlsx':
						return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
					case 'json':
						return 'application/json';
					case 'md':
						return 'text/markdown';
					case 'ppt':
						return 'application/mspowerpoint';
					case 'pptx':
						return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
					case 'swf':
						return 'application/x-shockwave-flash';
					case 'ai':
						return 'application/postscript';
					case 'odt':
						return 'application/vnd.oasis.opendocument.text';
					default:
						return 'text/plain';
				}
			}
			else
			{
				return $file_type;
			}
		}
		else
		{
			return false;
		}
	}
}
