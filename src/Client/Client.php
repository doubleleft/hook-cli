<?php

namespace Client;

use GuzzleHttp\Client as HTTPClient;
use GuzzleHttp\Exception\RequestException;
use Exception;

class Client {
	public static $endpoint;
	public static $debug = false;

	public static function setEndpoint($endpoint) {
		static::$endpoint = $endpoint;
	}

	public static function getEndpoint() {
		return static::$endpoint;
	}

	public static function setDebug($debug) {
		static::$debug = $debug;
	}

	public function get($segments) {
		return $this->request('get', $segments);
	}

	public function delete($segments) {
		return $this->request('delete', $segments);
	}

	public function post($segments, $data = array()) {
		return $this->request('post', $segments, $data);
	}

	public function request($method, $segments, $data = array()) {
		$client = new HTTPClient();
		$response = null;
		try {
			return $client->{$method}(self::$endpoint . $segments, array(
				'headers' => $this->getHeaders(),
				'body' => $data
			))->json(array(
				'object' => true
			));
		} catch (Exception $e) {
			$this->guzzleException($e);
		}
	}

	protected function getHeaders() {
		$config = Project::getConfig();
		$headers = array(
			'Content-Type' => 'application/json',
		);

		$public_key = $_SERVER['HOME'] . '/.ssh/id_rsa.pub';
		if (file_exists($public_key)) {
			$headers['X-Public-Key'] = urlencode(file_get_contents($public_key));
		}

		if (!empty($config) && isset($config['app_id']) && isset($config['key'])) {
			$headers['X-App-Id'] = $config['app_id'];
			$headers['X-App-Key'] = $config['key'];
		}

		$headers['User-Agent'] = 'hook-cli';
		return $headers;
	}

	protected function guzzleException($e) {
		$response = $e->getResponse();
		$body = $response->getBody();

		$error = "bad response.\n\n";
		$data = json_decode($body);

		if (static::$debug && (!$data || !isset($data->trace))) {
			$error .= "Response:\n" . $body;
		}

		if (isset($data->error)) {
			$url = parse_url(self::$endpoint);
			$status = $response->getStatusCode();
			$error .= "[{$url['host']}] " . $data->error . " (status {$status})";

			if (static::$debug && $data->trace) {
				$error .= "\n\nStack trace:\n" . $data->trace;
			}


		} else {
			$error .= "Please check if it is accessible: " . static::getEndpoint();
		}

		throw new Exception($error);
	}
}
