<?php
use Client\Client as Client;
use Client\Project as Project;
use Client\Console as Console;

return array(
	'arg0'    => 'cache:clear',
	'command' => 'cache:clear',
	'description' => 'Clear application cache.',
	'run' => function($args) use ($commands) {
		$client = new Client();
		$response = $client->delete("apps/cache");

		if ($response->success) {
			Console::output("Cache cleared successfully.");
		} else {
			throw new Exception("Some error ocurred when clearing the cache.");
		}

	}
);

