<?php
use Client\Console as Console;
use Client\Client as Client;
use Client\Project as Project;
use Client\Utils as Utils;
use Carbon\Carbon as Carbon;

return array(
	'arg0'    => 'deploy',
	'command' => 'deploy',
	'description' => 'Deploy ext directory.',
	'run' => function($args) use ($commands) {

		$client = new Client();
		$deployed = $client->get('apps/deploy');

		$root_directory = Project::root(Project::DIRECTORY_NAME);

		// modules
		$local_modules = array();
		$module_types = array('observers', 'routes', 'templates', 'channels');
		foreach($module_types as $module_type) {
			foreach(Utils::glob($root_directory . '/' . $module_type . '/*') as $module) {
				if (!is_file($module)) {
					continue;
				}

				if (!isset($local_modules[ $module_type ])) {
					$local_modules[ $module_type ] = array();
				}
				$local_modules[ $module_type ][ basename($module) ] = filemtime($module);
			}
		}

		// modules to upload/remove/update
		$module_sync = array(
			'upload' => array(),
			'remove' => array(),
			'update' => array()
		);

		// search for deleted / updated local modules
		foreach($deployed->modules as $type => $module) {
			$name = key($module);
			$updated_at = current($module);

			$local_exists = isset($local_modules[ $type ]) && isset($local_modules[ $type ][ $name ]);
			$local_updated_at = ($local_exists) ? $local_modules[ $type ][ $name ] : null;

			if ($local_exists) {
				// module already exists, is our version newer?
				if ($local_updated_at != $updated_at) {
					$module_file = $root_directory . '/' . $type . '/' . $name;
					$module_contents = file_get_contents($module_file);
					Utils::check_php_syntax($module_file);
					$module_sync['update'][$type][$name] = array($module_contents, $local_updated_at);
				}
				// if module wasn't flagged for update, just skip it.
				unset($local_modules[$type][$name]);
			} else {
				// module don't exist locally. mark for removal
				$module_sync['remove'][$type][] = $name;
				unset($local_modules[$type][$name]);
			}
		}

		// remaining local modules will be uploaded
		foreach($local_modules as $type => $modules) {
			if (empty($modules)) { continue; }

			foreach($modules as $name => $updated_at) {
				$module_file = $root_directory . '/' . $type . '/' . $name;
				$module_contents = file_get_contents($module_file);
				Utils::check_php_syntax($module_file);
				$module_sync['upload'][$type][$name] = array($module_contents, $updated_at);
			}
		}

		$yaml_parser = new Symfony\Component\Yaml\Parser();

		// get schedule
		$schedule = array();
		$schedule_file = $root_directory . '/schedule.yaml';
		if (file_exists($schedule_file)) {
			try {
				$schedule = $yaml_parser->parse(file_get_contents($schedule_file));
			} catch (Symfony\Component\Yaml\Exception\ParseException $e) {
				Console::error("Parse error on 'schedule.yaml': " . $e->getMessage());
				die();
			}
		}

		// get schema
		$schema = array();
		$schema_file = $root_directory . '/schema.yaml';
		if (file_exists($schema_file)) {
			try {
				$schema = $yaml_parser->parse(file_get_contents($schema_file));
			} catch (Symfony\Component\Yaml\Exception\ParseException $e) {
				Console::error("Parse error on 'schema.yaml': " . $e->getMessage());
				die();
			}
		}

		// get config
		$config = array();
		$config_file = $root_directory . '/config.yaml';
		if (file_exists($config_file)) {
			try {
				$config = $yaml_parser->parse(file_get_contents($config_file));
			} catch (Symfony\Component\Yaml\Exception\ParseException $e) {
				Console::error("Parse error on 'config.yaml': " . $e->getMessage());
				die();
			}
		}

		// get security
		$security = array();
		$security_file = $root_directory . '/security.yaml';
		if (file_exists($security_file)) {
			try {
				$security = $yaml_parser->parse(file_get_contents($security_file));
			} catch (Symfony\Component\Yaml\Exception\ParseException $e) {
				Console::error("Parse error on 'security.yaml': " . $e->getMessage());
				die();
			}
		}

		$stats = $client->post('apps/deploy', array(
			'modules' => $module_sync,
			'schema' => $schema,
			'schedule' => $schedule,
			'config' => $config,
			'security' => $security
		));

		if (isset($stats->error)) {
			Console::error("Can't deploy: ". $stats->error);
			die();
		}

		if ($stats->schedule) { Console::output("schedule updated."); }
		if ($stats->schema > 0) { Console::output($stats->schema . " collection(s) migrated."); }

		if ($stats->modules->removed > 0) { Console::output($stats->modules->removed . " module(s) removed."); }
		if ($stats->modules->updated > 0) { Console::output($stats->modules->updated . " module(s) updated."); }
		if ($stats->modules->uploaded > 0) { Console::output($stats->modules->uploaded . " module(s) uploaded."); }
	}
);
