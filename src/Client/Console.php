<?php

namespace Client;

class Console {
	protected static $temp_output_length = 0;

	/**
	 * Output some message on terminal.
	 * @param mixed $message
	 */
	public static function output($message) {
		echo $message . PHP_EOL;
	}

	public static function loading_output($message) {
		$message_length = strlen($message);

		if ($message_length > static::$temp_output_length) {
			static::$temp_output_length = $message_length;
		}

		echo $message . str_repeat(" ", 20) . str_repeat("\r", static::$temp_output_length);
	}

	/**
	 * Display red colored error message on terminal.
	 * @param mixed $message
	 */
	public static function error($message) {
		echo "\033[1;31m{$message}\033[0;39m" . PHP_EOL;
	}

	/**
	 * Read user input line
	 * @return string
	 */
	public static function readline() {
		return strtolower(trim(fgets(STDIN)));
	}

}
