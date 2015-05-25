<?php
use Client\Project as Project;

return array(
	'arg0'    => 'generate:observer',
	'command' => 'generate:observer <collection-name>',
	'description' => 'Generate observer class for collection events.',
	'run' => function($args) use ($commands) {

		if (!$args[1]) {
			throw new Exception("You must specify a collection name.");
		}

		$collection = str_plural(strtolower($args[1]));
		$extension = ($args['javascript']) ? '.js' : '.php';

		$dest = Project::root(Project::DIRECTORY_NAME) . '/observers/';
		$dest_file = $dest . $collection . $extension;
		@mkdir($dest, 0777, true);

		$template = file_get_contents(__DIR__ . '/../../templates/observer' . $extension);
		$template = preg_replace('/{name}/', ucfirst($collection), $template);
		$template = preg_replace('/{collection}/', $collection, $template);
		file_put_contents($dest_file, $template);

		echo "Observer created at '{$dest_file}'." . PHP_EOL;

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
