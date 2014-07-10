<?php
use Client\Client as Client;
use Client\Project as Project;
use Client\Console as Console;

return array(
	'arg0'    => 'schedule:upload',
	'command' => 'schedule:upload',
	'description' => 'Upload application schedule.',
	'run' => function($args) {
		$module_types = array('observers', 'routes', 'templates');

		$client = new Client();
		$schedule_file = Project::root(Project::DIRECTORY_NAME) . '/schedule.yaml';

		$uploaded = null;
		if (file_exists($schedule_file)) {
			$yaml = new Symfony\Component\Yaml\Parser();
			$schedule_data = $yaml->parse(file_get_contents($schedule_file));

			echo "Uploading: '{$schedule_file}'" . PHP_EOL;
			$uploaded = $client->post('apps/tasks', $schedule_data);
			if ($uploaded->success) {
				Console::output('Crontab installed successfully.');
			} else {
				Console::error("Error to install crontab.");
			}
		} else {
			Console::error("File not found: " . $schedule_file);
			Console::output('To generate it run: ' . PHP_EOL . "\tdl-api generate:schedule");
		}

		return $uploaded;
	}
);

