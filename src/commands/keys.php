<?php
use Client\Client as Client;
use Client\Project as Project;

return array(
	'arg0'    => 'keys',
	'command' => 'keys',
	'description' => 'List all application keys.',
	'run' => function($args) {

		$client = new Client();
		$keys = $client->get("apps/keys");

		$project = Project::getConfig();

		if (!$args['json']) {
			echo "Application name: {$project['name']}" . PHP_EOL;
			echo "Application keys:" . PHP_EOL;
			foreach($keys as $key) {
				echo "{" . PHP_EOL;
				echo "\tapp_id: {$key->app_id}" . PHP_EOL;
				echo "\tkey: " . $key->key . PHP_EOL;
				echo "\ttype: " . $key->type . PHP_EOL;
				echo "}" . PHP_EOL;
			}

			if (count($keys) > 0) {
			}
		}

		return $keys;

	}
);


