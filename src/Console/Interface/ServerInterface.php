<?php declare(strict_types=1);

namespace PhpSlides\Console\Interface;

/**
 * Interface ServerInterface
 *
 * This interface defines the methods required for creating PhpSlides server.
 */
interface ServerInterface
{
	/**
	 * Check if the port is currently in use.
	 *
	 * @return bool True if the port is in use, false otherwise.
	 */
	public function isPortInUse(): bool;

	/**
	 * Start the PHP server.
	 *
	 * @return bool True if the server started successfully, false otherwise.
	 */
	public function startServer(): bool;

	/**
	 * Stop the PHP server.
	 */
	public function stopServer(): void;

	/**
	 * Display the current status of the server.
	 */
	public function serverStatus(): void;

	/**
	 * Display available server commands.
	 */
	public function showCommands(): void;

	/**
	 * Handle input commands from stdin.
	 *
	 * @param resource $stdin The stdin resource.
	 */
	public function handleInputCommands($stdin): void;
}
