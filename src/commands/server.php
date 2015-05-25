<?php
use Client\Client as Client;
use Client\Project as Project;

return array(
	'arg0'    => 'server',
	'command' => 'server [<address=localhost:4665>]',
	'description' => 'Run development server. Must be at server root.',
	'run' => function($args) {
		$root = getcwd();

		$is_hook_root = false;
		$composer_file = getcwd().'/composer.json';

		if (file_exists($composer_file)) {
			$composer_json = json_decode(file_get_contents($composer_file), true);
			$is_hook_root = ($composer_json['name'] == 'doubleleft/hook' || $composer_json['name'] == 'doubleleft/hook-framework');
		}

		if (!$is_hook_root) {
			throw new Exception("Not on hook directory ({$root}).\nPlease cd into your local hook dir before running the 'server' command.");
		}

		$descriptors = array(
			array('file', '/dev/tty', 'r'),
			array('file', '/dev/tty', 'w'),
			array('file', '/dev/tty', 'w')
		);

		$bind = $args[1] ?: '0.0.0.0:4665';
		$process = proc_open("php -S {$bind} -t " . $root . '/public', $descriptors, $pipes);

		// keep `hook` process open, to keep STDIN/STDOUT reference
		// while `server` is running.
		while (is_resource($process)) {
			usleep(50);
		}
	}
);
