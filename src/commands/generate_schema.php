<?php

return array(
	'arg0'    => 'generate:schema',
	'command' => 'generate:schema',
	'description' => 'Generate schema definition config.',
	'run' => function($args) use ($commands) {
		$dest = Client\Project::root() . 'dl-ext/schema.yaml';
		@mkdir(dirname($dest), 0777, true);

		// TODO: get current schema from database
		$client = new Client\Client();
		$schema = $client->get('apps/schema');
		foreach($schema as $table => $definitions) {
			var_dump($table); // , $definitions
		}
		die();

		file_put_contents($dest, file_get_contents(__DIR__ . '/../templates/schema.yaml'));

		echo "Template created at '{$dest}'." . PHP_EOL;

		if ($editor = getenv('EDITOR')) {
			$descriptors = array(
				array('file', '/dev/tty', 'r'),
				array('file', '/dev/tty', 'w'),
				array('file', '/dev/tty', 'w')
			);
			$process = proc_open("{$editor} {$dest_file}", $descriptors, $pipes);
		}
	}
);


