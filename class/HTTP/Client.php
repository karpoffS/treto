<?php
namespace HTTP;

/**
 * Class Client
 * @package HTTP
 */
class Client {
	/**
	 * Query url
	 * @var string
	 */
	private $url;

	/**
	 * Timeout value
	 * @var int
	 */
	private $timeout = 10;

	/**
	 * HTTP headers
	 * @var array
	 */
	private $headers = [];

	/**
	 * GET query string
	 * @var string
	 */
	private $get;

	/**
	 * POST query string
	 * @var string
	 */
	private $post;

	/**
	 * HTTP Method
	 * @var string
	 */
	private $method;

	/**
	 * New line delimiter
	 * @var string
	 */
	private $nl = "\r\n";

	/**
	 * Return headers ?
	 * @var bool
	 */
	private $options = 0;

	public $responseCode;

	const OPT_RETURN_HEADERS = 1;

	/**
	 * @param string $url
	 * @param int $options
	 * @param $timeout
	 */
	public function __construct($url = null, $options = 0, $timeout = null) {
		if ($url) $this->url = $url;
		if ($options) $this->options = $options;

		if ($timeout) {
			$this->timeout = $timeout;
		}
	}

	/**
	 * @param string $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * @param string $options
	 */
	public function setOptions($options) {
		$this->options = $options;
	}

	/**
	 * @param string $timeout
	 */
	public function setTimeout($timeout) {
		$this->timeout = $timeout;
	}

	/**
	 * GET query
	 * @param $params
	 * @return string
	 */
	public function get($params = []) {
		$this->method = 'GET';

		if ($params) {
			$this->get = is_scalar($params)
				? trim($params, '&')
				: http_build_query($params, PHP_QUERY_RFC3986);
		}

		return $this->query();
	}

	/**
	 * POST query
	 * @param $params
	 * @return string
	 */
	public function post($params = []) {
		$this->method = 'POST';

		if ($params) {
			$this->post = is_scalar($params)
				? trim($params, '&')
				: http_build_query($params, PHP_QUERY_RFC1738);
		}

		return $this->query();
	}

	/**
	 * PUT query
	 * @param $params
	 * @return string
	 */
	public function put($params = []) {
		$this->method = 'PUT';

		if ($params) {
			$this->post = is_scalar($params)
				? trim($params, '&')
				: http_build_query($params, PHP_QUERY_RFC1738);
		}

		return $this->query();
	}

	/**
	 * DELETE query
	 * @param $params
	 * @return string
	 */
	public function delete($params = []) {
		$this->method = 'DELETE';

		if ($params) {
			$this->post = is_scalar($params)
				? trim($params, '&')
				: http_build_query($params, PHP_QUERY_RFC1738);
		}

		return $this->query();
	}

	/**
	 * Create http query
	 * @return string
	 * @throws \Exception
	 */
	private function query() {

		$url_parts = parse_url($this->url);

		if (!$url_parts) {
			throw new \Exception("Error parse url \"{$this->url}\"");
		}

		$scheme = isset($url_parts['scheme']) ? $url_parts['scheme'] : 'http';

		$host = isset($url_parts['host']) ? $url_parts['host'] : false;

		if (!$host) {
			throw new \Exception("Bad host \"{$this->url}\"");
		}

		$port = isset($url_parts['port'])
			? $url_parts['port']
			: ($scheme === 'https' ? 443 : 80);

		$path = isset($url_parts['path']) ? $url_parts['path'] : '/';

		$query = isset($url_parts['query']) ? '?'.$url_parts['query'] : '';
		$query = trim($query, '&');

		if ($this->get) {
			$query .= ($query ? '&' : '?').$this->get;
		}

		$fp = fsockopen(($scheme === 'https' ? 'ssl://' : '').$host, $port, $errno, $errstr, $this->timeout);
		if (!$fp) {
			throw new \Exception($errstr, $errno);
		}

		$headers = [];
		$headers[] = "{$this->method} {$path}{$query} HTTP/1.0";
		$headers['Host'] = $host;

		foreach ($this->headers as $key => $value) {
			if (is_numeric($key)) {
				$headers[] = $value;
			}
			else {
				$headers[$key] = $value;
			}
		}

		// Add post headers
		if ($this->post) {
			$headers['Content-Type'] = 'application/x-www-form-urlencoded';
			$headers['Content-Length'] = strlen($this->post);
		}

		$headers['Connection'] = 'Close';

		fwrite($fp, $this->buildHttpQuery($headers, $this->post));

		$out = '';
		while (!feof($fp)) {
			$out .= fgets($fp, 128);
		}

		fclose($fp);

		// извлекаем код ответа сервера
		$first_string =  substr($out, 0, strpos($out, $this->nl));
		$this->responseCode = intval(explode(" ", $first_string)[1]);
		
		if (!($this->options & self::OPT_RETURN_HEADERS)) {
			$outs = explode($this->nl.$this->nl, $out, 2);

			if (count($outs) == 2) {
				$out = $outs[1];
			}
		}

		return $out;
	}

	/**
	 * Make http headers from array
	 * @param array|\ArrayAccess $headers
	 * @param string [$post]
	 * @return string
	 */
	private function buildHttpQuery($headers, $post = null) {
		$in = '';
		foreach ($headers as $key => $value) {
			if (!is_numeric($key)) {
				$in .= $key.': ';
			}

			$in .= $value.$this->nl;
		}

		$in .= $this->nl;

		if ($post) {
			$in .= $post;
		}

		return $in;
	}

	/**
	 * Add unstandart headers
	 * @param $header
	 * @throws \Exception
	 */
	public function addHeaders($header) {
		if (is_array($header)) {
			$this->headers = array_merge($this->headers, $header);
		}
		else {
			throw new \Exception('Not array for set headers!');
		}
	}
}
