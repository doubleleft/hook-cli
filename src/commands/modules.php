<?php
use Client\Client as Client;
use Client\Project as Project;
use Client\Console as Console;

return array(
	'arg0'    => 'modules',
	'command' => 'modules',
	'description' => 'List all application modules',
	'run' => function($args) {

		$client = new Client();
		$modules = $client->get("apps/modules");


		if (!$args['json']) {
			if ($modules) {
				echo "Modules: " . PHP_EOL;
				foreach ($modules as $module) {
					echo "\t'{$module->name}' ({$module->type}) - LoC: " . substr_count($module->code, "\n") . PHP_EOL;
				}
			} else {
				$project = Project::getConfig();
				echo "No modules found for: '{$project['name']}'." . PHP_EOL;
			}
		}

		return $modules;
	}
);
