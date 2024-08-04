<?php declare(strict_types=1);

namespace Phpslides\Interface;

interface SlidesException
{
   	/**
   	 * Get a detailed error message including file and line number.
   	 *
   	 * @return string A detailed error message.
   	 */
   	public function getDetailedMessage(): string;

   	/**
   	 * Filter the stack trace to remove paths from vendor directories.
   	 *
   	 * @return array The filtered stack trace.
   	 */
   	public function filterStackTrace(): array;

   	/**
   	 * Get the file path from the filtered stack trace.
   	 *
   	 * @return string The file path.
   	 */
   	public function getFilteredFile(): string;

   	/**
   	 * Get the line number from the filtered stack trace.
   	 *
   	 * @return int The line number.
   	 */
   	public function getFilteredLine(): int;

   	/**
   	 * Get a code snippet surrounding the error line.
   	 *
   	 * @param int $linesBefore The number of lines before the error line to include.
   	 * @param int $linesAfter The number of lines after the error line to include.
   	 * @return array The code snippet.
   	 */
   	public function getCodeSnippet(
   		$linesBefore = 10,
   		$linesAfter = 10
   	): array;
   }

