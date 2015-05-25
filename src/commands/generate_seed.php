<?php
use Client\Project as Project;

return array(
	'arg0'    => 'generate:seed',
	'command' => 'generate:seed <collection-name>',
	'description' => 'Generate seed template.',
	'run' => function($args) use ($commands) {

		if (strlen($args[1]) == 0) {
			throw new Exception("You must specify a collection name for seeding.");
		}

		$collection = str_plural(strtolower($args[1]));

		$dest = Project::root(Project::DIRECTORY_NAME) . '/seeds/';
		$dest_file = $dest . $collection . '.yaml';
		@mkdir($dest, 0777, true);

		$template = file_get_contents(__DIR__ . '/../../templates/seed.yaml');
		$template = preg_replace('/{name}/', $collection, $template);
		file_put_contents($dest_file, $template);

		echo "Seed created at '{$dest_file}'." . PHP_EOL;

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


