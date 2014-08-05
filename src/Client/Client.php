<?php

namespace Client;

class Client {
	// public static $endpoint = 'http://dl-api.ddll.co/';
	// public static $endpoint = 'http://api.2d.cx';
	// public static $endpoint = 'http://dl-api.dev/api/index.php/';
	public static $endpoint = 'http://hook.dev/index.php/';
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
		return $this->parse($this->request('get', $segments));
	}

	public function delete($segments) {
		return $this->parse($this->request('delete', $segments));
	}

	public function post($segments, $data = array()) {
		return $this->parse($this->request('post', $segments, $data));
	}

	protected function parse($request) {
		try {
			$response = $request->send();
		} catch (\Guzzle\Http\Exception\BadResponseException $e) {
			$response = $e->getResponse();
		}

		$data = json_decode($response->getBody());

		if (isset($data->error)) {
			// TODO: create Output class for coloring features
			$url = parse_url(self::$endpoint);
			$status = $response->getStatusCode();
			Console::error("[{$url['host']}] " . $data->error . " (status {$status})" );
			die();
		}

		return $data;
	}

	public function request($method, $segments, $data = array()) {
		$client = new \Guzzle\Http\Client(self::$endpoint);
		return $client->{$method}($segments, $this->getHeaders(), $data, array(
			'debug' => static::$debug
		));
	}

	protected function getHeaders() {
		$config = Project::getConfig();
		$headers = array(
			'Content-Type' => 'application/json',
			'X-Public-Key' => urlencode(file_get_contents($_SERVER['HOME'] . '/.ssh/id_rsa.pub'))
		);

		if (!empty($config)) {
			$headers['X-App-Id'] = $config['app_id'];
			$headers['X-App-Key'] = $config['key'];
		}

		$headers['User-Agent'] = 'hook-cli';
		return $headers;
	}
}
