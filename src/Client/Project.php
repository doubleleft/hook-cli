<?php namespace Client;

class Project {
	private static $config_file;
	private static $temp_config;
	private static $environment = 'development';

	const DIRECTORY_NAME  = 'hook-ext';
	const CREDENTIALS_DIR = 'hook-ext/credentials';
	const CONFIG_FILE     = 'hook-ext/credentials/{environment}/cli.json';

	// TODO: remove deprecated warning.
	// this was introduced on 0.2.2
	const DEPRECATED_CONFIG_FILE = '.hook-config';

	public static function getConfigFile() {
		$deprecated_config_file = self::root() . self::DEPRECATED_CONFIG_FILE;
		$credentials_cli_path = self::getCredentialsPath() . 'cli.json';

		if (file_exists($deprecated_config_file)) {
			Console::error("You are using deprecated `.hook-config` file." . PHP_EOL . "Do you want to upgrade it? (y/n)");
			$answer = Console::readline();
			if ($answer == "y") {
				self::createCredentialsDirectory();
				rename($deprecated_config_file, $credentials_cli_path);
			} else {
				static::$temp_config = json_decode(file_get_contents($deprecated_config_file), true);
				return $deprecated_config_file;
			}
		}

		return $credentials_cli_path;
	}

	public static function setEnvironment($environment) {
		static::$environment = $environment;
	}

	public static function getEnvironment() {
		return static::$environment;
	}

	public static function getConfig() {
		// return temporary app config
		if (self::$temp_config !== null) {
			return self::$temp_config;
		}

		$config_file = self::getConfigFile();
		return (!file_exists($config_file)) ? array() : json_decode(file_get_contents($config_file), true);
	}

	public static function root($concat='') {
		$scm_list = array(self::DIRECTORY_NAME, '.git', '_darcs', '.hg', '.bzr', '.svn');
		$path = getcwd();

		while ($path !== '/') {
			$path .=  '/';

			foreach($scm_list as $scm) {
				if (file_exists($path . $scm)) {
					return $path . $concat;
				}
			}

			// try parent directory...
			$path = dirname($path);
		}

		return getcwd() . '/' . $concat;
	}

	public static function getCredentialsPath() {
		return self::root(self::CREDENTIALS_DIR) . '/' . self::getEnvironment() . '/';
	}

	public static function createCredentialsDirectory() {
		$credentials_path = self::getCredentialsPath();
		if (!file_exists($credentials_path)) {
			@mkdir($credentials_path, 0777, true);
		}
	}

}
