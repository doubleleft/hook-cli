<?php

namespace Client;

use GuzzleHttp\Client as HTTPClient;
use GuzzleHttp\Exception\RequestException;
use Exception;

class Client {
	public static $endpoint = 'http://0.0.0.0:4665/';
	public static $debug = false;

	protected $raiseExceptions = true;

	public static function setEndpoint($endpoint) {
		if (substr($endpoint, -strlen('/')) !== '/') {
			$endpoint .= '/';
		}
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
		if (static::$debug) {
			Console::output("Client:request -> " . $method . " - " . $segments);
			print_r($data);
		}
		$client = new HTTPClient();
		$response = null;
		try {
			return $client->{$method}(self::$endpoint . $segments, array(
				'headers' => $this->getHeaders(),
				'body' => $data,
				'exceptions' => $this->raiseExceptions
			))->json(array(
				'object' => true
			));
		} catch (Exception $e) {
			$this->guzzleException($e);
		}
	}

	public function setRaiseExceptions($bool) {
		$this->raiseExceptions = $bool;
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

		$data = null;
		$body = null;

		if (!$response) {
			$error = "bad endpoint.";
		} else {
			$error = "bad response.";

			$body = $response->getBody();
			$data = json_decode($body);
		}

		$error .= "\n";

		if ($body && static::$debug && (!$data || !isset($data->trace))) {
			$error .= "Response:\n" . $body;
		}

		if (isset($data->error)) {
			$url = parse_url(self::$endpoint);
			$status = $response->getStatusCode();
			$error .= "[{$url['host']}] " . $data->error . " (status {$status})";

			if (static::$debug && isset($data->trace)) {
				$error .= "\n\nStack trace:\n" . $data->trace;
			}

		} else {
			$error .= "Please check if it is accessible: " . static::getEndpoint();
		}

		throw new Exception($error);
	}
}
