<?php

return array(
	'arg0'    => 'console',
	'command' => 'console [evaluate_file.js]',
	'description' => 'Start interactive console, or run filename.',
	'run' => function($args) use ($commands) {

		$config_path = Client\Project::getConfigFile();
		if (!file_exists($config_path)) {
			throw new Exception("Missing file: '" . $config_path . "'.\n");
		}

		// flag for server-side console
		$options = ($args['server']) ? " --server" : "";
		$options .= ' --environment ' . Client\Project::getEnvironment();

		$descriptors = array(
			array('file', 'php://stdin', 'r'),
			array('file', 'php://stdout', 'w'),
			array('file', 'php://stderr', 'w')
		);

		$process = proc_open(
			'node ' . __DIR__ . '/../../console/bootstrap.js ' . $config_path . ' ' . $args[1] . ' ' . $options,
			$descriptors,
			$pipes
		);

		// keep `hook` process open, to keep STDIN/STDOUT reference
		// while `console` is running.
		while (is_resource($process)) {
			usleep(50);
		}

	}
);
