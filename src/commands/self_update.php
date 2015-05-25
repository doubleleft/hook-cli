<?php
use Client\Utils as Utils;
use Client\Console as Console;
use Client\Project as Project;

return array(
	'arg0'    => 'self-update',
	'command' => 'self-update',
	'description' => 'Get the last hook-cli version.',
	'run' => function($args) use ($commands) {
		chdir(__DIR__ . '/../../');

		$descriptors = array(
			array('file', '/dev/tty', 'r'),
			array('file', '/dev/tty', 'w'),
			array('file', '/dev/tty', 'w')
		);

		$process = proc_open('git pull', $descriptors, $pipes);
	}
);

