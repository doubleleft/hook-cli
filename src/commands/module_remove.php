<?php
use Client\Client as Client;

return array(
	'arg0'    => 'module:remove',
	'command' => 'module:remove <module-name>',
	'description' => 'Remove a module from application',
	'run' => function($args) {
		$module = $args['1'];

		if (!$module) {
			throw new Exception("Error: 'module-name' is required.");
		}

		$client = new Client();
		$response = $client->delete('apps/modules', array(
			'module' => array('name' => $module)
		));

		if ($response->success) {
			echo "Module '{$module}' removed successfully." . PHP_EOL;
		} else {
			echo "Module '{$module}' not found." . PHP_EOL;
		}

	}
);
