<?php
use Client\Utils as Utils;
use Client\Console as Console;
use Client\Project as Project;

return array(
	'arg0'    => 'config:set',
	'command' => 'config:set <name=value> [<name=value> ...]',
	'description' => 'Set a configuration to app.',
	'run' => function($args) use ($commands) {
		$config_file = Project::root(Project::DIRECTORY_NAME) . '/config.yaml';
		$configs = array();

		if (file_exists($config_file)) {
			$parser = new Symfony\Component\Yaml\Parser();
			$configs = $parser->parse(file_get_contents($config_file));
		}

		$configs_to_add = array();
		foreach($args as $arg) {
			if (!is_null($arg) && preg_match('/=/', $arg)) {
				$config = preg_split('/=/', $arg);
				$name = $config[0];
				$value = $config[1];

				//
				// Read or extract certificate file
				// --------------------------------
				//
				if (file_exists($value)) {
					Console::output("Reading certificate file...");

					$ext = pathinfo($value, PATHINFO_EXTENSION);
					if ($ext == 'p12') {
						$results = array();
						$worked = openssl_pkcs12_read(file_get_contents($value), $results, null);
						if ($worked) {
							$value = $results['cert'] . $results['pkey'];
						} else {
							Console::error(openssl_error_string());
						}
					} else if ($ext == 'pem') {
						$value = file_get_contents($value);
					}
				}

				array_push($configs_to_add, array(
					'name' => $name,
					'value' => $value
				));
			}
		}

		foreach($configs_to_add as $config) {
			Utils::array_set($configs, $config['name'], $config['value']);
		}

		$dumper = new Symfony\Component\Yaml\Dumper();
		file_put_contents($config_file, str_replace("  ", " ", $dumper->dump($configs, 10)));

		Console::output("Written successfully at: '{$config_file}'");
	}
);
