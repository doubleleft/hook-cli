<?php
use Client\Project as Project;
use Client\Client as Client;

return array(
	'arg0'    => 'app:new',
	'command' => 'app:new <application-name>',
	'description' => 'Create a new application.',
	'run' => function($args) {
		if (!$args[1]) {
			throw new Exception("'application-name' is required.");
		}

		$client = new Client();
		$app = $client->post('apps', array(
			'app' => array('name' => $args[1])
		));

		// Generate security file
		$dest = Project::root(Project::DIRECTORY_NAME) . '/';
		@mkdir($dest, 0777, true);
		@mkdir($dest . 'config/', 0777, true);
		@mkdir($dest . 'credentials/', 0777, true);

		$default_config_files = array(
			'security.yaml',
			'packages.yaml',
			'schedule.yaml',
			'schema.yaml',
			'config/config.yaml',
			'config/config.environment.yaml'
		);
		foreach($default_config_files as $config_file) {
			$dest_file = $dest . $config_file;
			$template = file_get_contents(__DIR__ . '/../../templates/'. $config_file);

			// replace environment on file
			if (preg_match('/\.environment\./', $dest_file)) {
				$dest_file = preg_replace('/\.(environment)\./', '.' . Project::getEnvironment() . '.', $dest_file);
				$template = preg_replace('/{{environment}}/', Project::getEnvironment(), $template);
			}

			if (!file_exists($dest_file)) {
				file_put_contents($dest_file, $template);
			}
		}

		if (!$args['json']) {
			Project::createCredentialsDirectory();

			echo "Application: {$app->name}" . PHP_EOL;
			echo "Keys:" . PHP_EOL;
			foreach($app->keys as $key) {
				$credentials = array(
					'app_id' => $key->app_id,
					'key' => $key->key,
					'type' => $key->type,
					'endpoint' => Client::getEndpoint()
				);

				echo json_encode($credentials, JSON_PRETTY_PRINT) . PHP_EOL;

				file_put_contents(Project::getCredentialsPath() . $key->type . '.json', json_encode($credentials));
			}
		}

		return $app->keys;

	}
);
