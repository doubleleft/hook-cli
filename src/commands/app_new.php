<?php

return array(
	'arg0'    => 'app:new',
	'command' => 'app:new <application-name>',
	'description' => 'Create a new application.',
	'run' => function($args) {

		if (!isset($args[1])) {
			die("'application-name' is required.");
		}

		$client = new Client\Client();
		$app = $client->post('apps', array(
			'app' => array('name' => $args[1])
		));

		Client\Project::setConfig(array(
			'name' => $app->name,
			'app_id' => $app->keys[0]->app_id,
			'key' => $app->keys[0]->key
		));

		// Generate security file
		$dest = Client\Project::root(Client\Project::DIRECTORY_NAME) . '/';
		$dest_file = $dest . 'security.yaml';
		@mkdir($dest, 0777, true);

		$template = file_get_contents(__DIR__ . '/../../templates/security.yaml');
		$template = preg_replace('/{pepper}/', sha1(uniqid(true)), $template);
		file_put_contents($dest_file, $template);

		if (!$args['json']) {
			echo "Application: {$app->name}" . PHP_EOL;
			echo "Keys:" . PHP_EOL;
			foreach($app->keys as $key) {
				echo "{" . PHP_EOL;
				echo "\tapp_id: {$key->app_id}" . PHP_EOL;
				echo "\tkey: {$key->key}". PHP_EOL;
				echo "\ttype: {$key->type}" . PHP_EOL;
				echo "}" . PHP_EOL;
			}
		}

		return $app->keys;

	}
);
