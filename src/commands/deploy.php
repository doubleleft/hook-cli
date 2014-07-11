<?php
use Client\Client as Client;
use Client\Project as Project;

return array(
	'arg0'    => 'deploy',
	'command' => 'deploy',
	'description' => 'Deploy ext directory.',
	'run' => function($args) use ($commands) {

		$client = new Client();
		$deployed = $client->get('apps/deployed');

		var_dump($deployed);


	}
);


