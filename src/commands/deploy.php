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

		Console::loading_output("Retrieving remote data...");
		$deployed = $client->get('apps/deploy');

		$root_directory = Project::root(Project::DIRECTORY_NAME);
		$js_converted_modules = array();

		// modules
		$local_modules = array();
		$module_types = array('observers', 'routes', 'templates', 'channels');
		foreach($module_types as $module_type) {
			foreach(Utils::glob($root_directory . '/' . $module_type . '/*') as $module) {
				if (!is_file($module)) {
					continue;
				}

				$extension = pathinfo($module , PATHINFO_EXTENSION);
				if ($extension == "js") {
					$javascript_module = $module;
					$module = preg_replace('/\.js$/', '.php', $module);;
					array_push($js_converted_modules, $module); // keep track for removal

					// module may already exists due error on previous upload.
					// ask user to prevent overwriting some manually added .php file
					if (file_exists($module)) {
						Console::output("Module '".basename($module)."' already exists.\nDo you want to overwrite with converted '".basename($javascript_module)."' version?\n(y/n)");
						$handle = popen("read; echo \$REPLY", "r");
						$input = strtolower(trim(fgets($handle, 100)));
						if ($input!=="y") {
							throw new Exception("Deploy aborted.");
						}
					}

					exec("js2php $javascript_module > $module");
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
				if ($local_updated_at > $updated_at) {
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

		Console::loading_output("Deploying...");
		$schedule_data = Utils::parse_yaml($root_directory . '/schedule.yaml');
		$schedule = ($schedule_data && isset($schedule_data['schedule'])) ? $schedule_data['schedule'] : array();

		$stats = $client->post('apps/deploy', array(
			'modules' => $module_sync,
			'schema' => Utils::parse_yaml($root_directory . '/schema.yaml'),
			'schedule' => $schedule,
			'config' => Utils::parse_yaml($root_directory . '/config.yaml'),
			'security' => Utils::parse_yaml($root_directory . '/security.yaml'),
			'packages' => Utils::parse_yaml($root_directory . '/packages.yaml')
		));

		// remove auto-generated PHP files
		if (count($js_converted_modules) > 0) {
			foreach($js_converted_modules as $module) {
				unlink($module);
			}
		}

		if (isset($stats->error)) {
			Console::error($stats->error);
		}

		if (isset($stats->schedule)) { Console::output("Schedule updated."); }
		if (isset($stats->schema) && $stats->schema > 0) { Console::output($stats->schema . " collection(s) migrated."); }

		if (isset($stats->modules)) {
			if ($stats->modules->removed > 0) { Console::output($stats->modules->removed . " module(s) removed."); }
			if ($stats->modules->updated > 0) { Console::output($stats->modules->updated . " module(s) updated."); }
			if ($stats->modules->uploaded > 0) { Console::output($stats->modules->uploaded . " module(s) uploaded."); }
		}

		if (isset($stats->packages) && !empty($stats->packages)) {
			Console::output("\nPackages:\n\t" . preg_replace("/\\n/", "\n\t", $stats->packages));
		}

		Console::success("Successfully deployed.");
	}
);
