<?php
use Client\Client as Client;
use Client\Project as Project;
use Client\Console as Console;

return array(
	'arg0'    => 'config',
	'command' => 'config',
	'description' => 'List all app configurations',
	'run' => function($args) {
		$client = new Client();
		$configs = $client->get("apps/configs");

		$project = Project::getConfig();

		if (!$args['json']) {
			foreach($project as $key => $value) {
				Console::output($key . ': ' . $value);
			}
			Console::output(str_repeat('-', 37));
			if ($configs) {
				foreach($configs as $config) {
					preg_match('/([^$|\n]+)/', $config->value, $value);
					Console::output($config->name . ': ' . $value[1]);
				}
			} else {
				Console::output("No configurations found for: '{$project['name']}'.");
			}
		}

		return $configs;
	}
);


