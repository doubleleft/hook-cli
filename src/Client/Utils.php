<?php namespace Client;

use Exception;

class Utils {

	public static function glob($pattern, $flags = 0) {
		$files = glob($pattern, $flags);
		foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
			$files = array_merge($files, self::glob($dir.'/'.basename($pattern), $flags));
		}
		return $files;
	}

	public static function check_php_syntax($file_path) {
		// Check for syntax problems before uploading it.
		$lint_output = null;
		$lint_return_code = null;
		exec('php -l ' . $file_path, $lint_output, $lint_return_code);
		if ($lint_return_code !== 0) {
			throw new Exception(join("\n\n" , $lint_output));
		}
	}

	public static function parse_yaml($file_path) {
		$yaml_parser = new \Symfony\Component\Yaml\Parser();
		$data = array();

		if (file_exists($file_path)) {
			try {
				$parsed = $yaml_parser->parse(file_get_contents($file_path));
				if (is_array($parsed)) {
					$data = $parsed;
				}
			} catch (Symfony\Component\Yaml\Exception\ParseException $e) {
				throw new Exception("Parse error on '" . basename($file_path) . "': " . $e->getMessage());
			}
		}

		return $data;
	}

	public static function array_set(&$array, $keys, $value) {
		$keys = preg_split("/\./", $keys);
		$current = &$array;
		foreach($keys as $key) {
			$current = &$current[$key];
		}
		$current = $value;
	}

	public static function mime_type($filename) {
		$mime_types = array(
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',

			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		);

		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if (array_key_exists($ext, $mime_types)) {
			return $mime_types[$ext];
		} elseif (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		} else {
			return 'application/octet-stream';
		}
	}

}
