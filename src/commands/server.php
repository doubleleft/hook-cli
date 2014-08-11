<?php
use Client\Project as Project;

return array(
	'arg0'    => 'server',
	'command' => 'server [<address=localhost:4665>]',
	'description' => 'Run development server. Must be at server root.',
	'run' => function($args) {
		$descriptors = array(
			array('file', '/dev/tty', 'r'),
			array('file', '/dev/tty', 'w'),
			array('file', '/dev/tty', 'w')
		);

		$bind = $args[1] ?: 'localhost:4665';
		$process = proc_open("php -S {$bind} " . Project::root() . '/index.php', $descriptors, $pipes);
	}
);
