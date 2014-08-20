<?php
use Client\Project as Project;

return array(
	'arg0'    => 'generate:template',
	'command' => 'generate:template <template-name>',
	'description' => 'Generate HTML template.',
	'run' => function($args) use ($commands) {

		if (strlen($args[1]) == 0) {
			Client\Console::error("You must specify a template name.");
			die();
		}

		$template_name = basename($args[1], '.html');

		$dest = Project::root(Project::DIRECTORY_NAME) . '/templates/';
		$dest_file = $dest . $template_name . '.html';
		@mkdir($dest, 0777, true);

		$template = file_get_contents(__DIR__ . '/../../templates/template.html');
		$template = preg_replace('/{template_name}/', ucfirst($template_name), $template);
		file_put_contents($dest_file, $template);

		echo "Template created at '{$dest_file}'." . PHP_EOL;

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

