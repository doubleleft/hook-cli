<?php
use Client\Client as Client;
use Client\Project as Project;
use Client\Console as Console;

return array(
	'arg0'    => 'generate:schema',
	'command' => 'generate:schema',
	'description' => 'Generate schema definition config.',
	'run' => function($args) use ($commands) {
		$dest = Project::root(Project::DIRECTORY_NAME) . '/schema.yaml';
		@mkdir(dirname($dest), 0777, true);

		$client = new Client();
		$cached_schema = $client->get('apps/schema');

		$yaml = new Symfony\Component\Yaml\Dumper();

		if (file_exists($dest)) {
			Console::output("You already have a schema.yaml file.\n" .
				"Your changes will be lost.\n" .
				"Overwrite? [y/n]");
			$overwrite = (Console::readline()) == 'y';
			if (!$overwrite) {
				Console::output("Aborted.");
				die();
			}
		}

		// format schema dump before save
		$schema_dump = str_replace('  ', ' ', $yaml->dump(json_decode(json_encode($cached_schema), true), 3));
		$schema_dump = preg_replace('/^([a-z0-9_]+):\n/m', "\n\$0", $schema_dump);
		file_put_contents($dest, $schema_dump . file_get_contents(__DIR__ . '/../../templates/schema.yaml'));

		echo "Schema dumped at '{$dest}'." . PHP_EOL;

		if ($editor = getenv('EDITOR')) {
			$descriptors = array(
				array('file', '/dev/tty', 'r'),
				array('file', '/dev/tty', 'w'),
				array('file', '/dev/tty', 'w')
			);
			$process = proc_open("{$editor} {$dest}", $descriptors, $pipes);
		}
	}
);


