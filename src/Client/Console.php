<?php

namespace Client;

class Console {

	/**
	 * Output some message on terminal.
	 * @param mixed $message
	 */
	public static function output($message) {
		echo $message . PHP_EOL;
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
