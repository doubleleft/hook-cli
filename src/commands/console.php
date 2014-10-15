<?php

return array(
	'arg0'    => 'console',
	'command' => 'console [evaluate_file.js]',
	'description' => 'Start interactive console, or run filename.',
	'run' => function($args) use ($commands) {

		$config_path = Client\Project::getConfigFile();
		if (!file_exists($config_path)) {
			throw new Exception("No ". Client\Project::CONFIG_FILE ." file found at project root.\n");
		}

		$descriptors = array(
			array('file', '/dev/tty', 'r'),
			array('file', '/dev/tty', 'w'),
			array('file', '/dev/tty', 'w')
		);

		$process = proc_open(
			'node ' . __DIR__ . '/../../console/bootstrap.js ' . $config_path . ' ' . $args[1],
			$descriptors,
			$pipes
		);
	}
);
