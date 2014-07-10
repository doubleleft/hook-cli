<?php
use Client\Client as Client;
use Client\Project as Project;
use Client\Console as Console;

return array(
	'arg0'    => 'schema:upload',
	'command' => 'schema:upload',
	'description' => 'Upload schema definition.',
	'run' => function($args) {
		$schema_file = Project::root(Project::DIRECTORY_NAME) . '/schema.yaml';
		$parser = new Symfony\Component\Yaml\Parser();

		$client = new Client();
		$response = $client->post('apps/schema', $parser->parse(file_get_contents($schema_file)));

		if (isset($response->error)) {
			Console::error($response->error);
		} else {
			Console::output("Done.");
		}

	}
);


