<?php
use Client\Project as Project;

return array(
	'arg0'    => 'generate:observer',
	'command' => 'generate:observer <collection-name>',
	'description' => 'Generate observer class for collection events.',
	'run' => function($args) use ($commands) {

		if (strlen($args[1]) == 0) {
			Client\Console::error("You must specify a collection name.");
			die();
		}

		$dest = Project::root(Project::DIRECTORY_NAME) . '/observers/';
		$dest_file = $dest . $args[1] . '.php';
		@mkdir($dest, 0777, true);

		$template = file_get_contents(__DIR__ . '/../../templates/observer.php');
		$template = preg_replace('/{name}/', ucfirst($args[1]), $template);
		$template = preg_replace('/{collection}/', $args[1], $template);
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
