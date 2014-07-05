<?php

return array(
	'arg0'    => 'schema:upload',
	'command' => 'schema:upload',
	'description' => 'Upload schema definition.',
	'run' => function($args) {
		$schema_file = Client\Project::root() . 'dl-ext/schema.yaml';
		$parser = new Symfony\Component\Yaml\Parser();

		$client = new Client\Client();
		$response = $client->post('apps/schema', $parser->parse(file_get_contents($schema_file)));

		if (isset($response->error)) {
			Client\Console::error($response->error);
		} else {
			Client\Console::output("Done.");
		}

	}
);


