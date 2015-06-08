<?php

/*
 * HTTP requesting class.
 */

class HttpRequest
{
	protected $targetUrl 			= '';
	protected $scheme 				= '';
	protected $host 				= '';
	protected $port					= 80;
	protected $path 				= '/';
	protected $queryString 			= '';
	protected $fragment 			= '';
	protected $timeout 				= 5;
	protected $errno 				= '';
	protected $errstr 				= '';
	protected $maxRedirection 		= 3;
	protected $connectTime 			= 'close';
	protected $requestMethod 		= '';
	protected $acceptType			= 'text/html, application/xhtml+xml, */*';
	protected $acceptEncoding 		= 'gzip, deflate';
	protected $language 			= 'zh-CN';
	protected $userAgent 			= 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)';
	protected $contentType 			= 'application/x-www-form-urlencoded';
	protected $boundary 			= '==========';
	protected $postMessage 			= array();
	protected $referer 				= '';
	protected $isPSock 				= false;
	protected $uploadFiles 			= array();
	protected $requestCookies 		= array();
	protected $reponseCookies 		= array();
	protected $requestHeader 		= array();
	protected $reponseHeader 		= array();
	protected $requestContents 		= '';
	protected $reponseContents 		= '';
	protected $rawReponseContents 	= null;
	protected $connection 			= null;

	const HTTP_GET  = 'GET';
	const HTTP_POST = 'POST';
	const HTTP_PUT	= 'PUT';
	const HTTP_VERSION = 'HTTP/1.1';


	public function __construct($url = null, $requestMethod = self::HTTP_GET, $isPsock = false) {
		if ($url <> null) {
			$this->connect($url, $requestMethod, $isPsock);
		}
	}


	public function connect($url, $requestMethod = self::HTTP_GET, $isPsock = false) {
		$func = $isPsock ? 'pfsockopen' : 'fsockopen';
		$this->requestMethod = $requestMethod;
		$this->isPSock = $isPsock;

		$this->fixUrl($url);
		$this->parseUrl();
		$this->connection = call_user_func($func, ($this->scheme == 'https' ? 'ssl://' : 'tcp://') . $this->host,
				$this->port, $this->errno, $this->errstr, $this->timeout
		);

		if (!is_resource($this->connection)) {
			throw new Exception("Network connect failed!");
		}
		return true;
	}


	public function parseUrl() {
		$fix_url = $this->targetUrl;
		$urlInfo = @parse_url($fix_url);

		$this->scheme		= isset($urlInfo['scheme']) ? $urlInfo['scheme'] : 'http';
		$this->host			= $urlInfo['host'];
		$this->port			= isset($urlInfo['port']) ? $urlInfo['port'] : 80;
		$this->path			= isset($urlInfo['path']) ? $urlInfo['path'] : '/';
		$this->fragment		= isset($urlInfo['fragment']) ? '#'.$urlInfo['fragment'] : '';
		$this->queryString	= isset($urlInfo['query']) ? '?'.$urlInfo['query'] : '';
	}


	public function addCookie($name = null, $value = null) {
		if (is_array($name) && $value === null) {
			$this->requestCookies[] = implode(';', $name);
		}else{
			$this->requestCookies[] = "$name=$value";
		}
		
	}

	public function addFile($name = null, $content = null) {
		if (is_array($name) && $value === null) {
			$this->uploadFiles = array_merge($this->uploadFiles, $name);
		}else{
			$this->uploadFiles = array_merge($this->uploadFiles, array($name => $content));
		}
	}

	public function addPostField($name = null, $value = null) {
		if (is_array($name) && $value === null){
			$this->postMessage = array_merge($this->postMessage, $name);
		}else if (is_array($name) && is_array($value)) {
			$this->postMessage = array_merge($this->postMessage, array_combine($name, $value));
		}else{
			$this->postMessage = array_merge($this->postMessage, array($name => $value));
		}
	}

	public function buildHeader() {
		$text = $this->requestMethod. " "
				. $this->path. $this->queryString. $this->fragment. " "
				. self::HTTP_VERSION
				. "\r\n";
		$header  = array(
			'Host' 					=> $this->host. ':'. $this->port,
			'Connection' 			=> $this->connectTime,
			'Accept' 				=> $this->acceptType,
			'Accept-Language'		=> $this->language,
			'Accept-Encoding' 		=> $this->acceptEncoding,
			'User-Agent' 			=> $this->userAgent,
			'Referer'				=> !empty($this->referer) ? $this->referer : $this->targetUrl
		);
		if (count($this->requestCookies) > 0) {
			$header['Cookie'] = implode(';', $this->requestCookies);
		}

		if ($this->requestMethod == self::HTTP_POST) {
			$header['Content-Type'] = $this->contentType;
			$header['Content-Length'] = $this->contentLength;
		}

		foreach($header as $key => $value) {
			$text .= "$key: $value\r\n";
		}
		$this->requestHeader = $text. "\r\n";
	}


	public function buildContents() {
		$contents = '';
		$haveFile = false;
		$descption = '';

		if (count($this->uploadFiles) > 0) {
			$haveFile = true;
			$this->contentType = 'multipart/form-data; boundary='. $this->boundary;
			foreach($this->uploadFiles as $key => $value) {
				$contents .= "--" . $this->boundary
						  . "\r\n"
						  . "Content-Disposition: form-data; name=\"$key\"; filename=\"$key\""
						  . "\r\n"
						  . "Content-Type: text/plain"
						  . "\r\n\r\n"
						  . $value
						  . "\r\n";
			}
		}
		if (count($this->postMessage) > 0){
			if ($haveFile) {
				foreach($this->postMessage as $key => $value) {
					$contents .= "--" . $this->boundary
							  . "\r\n"
							  . "Content-Disposition: form-data; name=\"$key\""
							  . "\r\n\r\n"
							  . $this->encodeUrl($value)
							  . "\r\n";
				}
			}else{
				$contents .= $this->encodeUrl(http_build_query($this->postMessage));
			}
		}
		$contents .= $haveFile ? "--" . $this->boundary . "--" : '';
		$this->requestContents = $contents;
		$this->contentLength = strlen($contents);
	}


	protected function buildMessage() {
		$this->buildContents();
		$this->buildHeader();
	}


	public function encodeUrl($url) {
		$entities = array(
			'%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D',
			'%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D'
		);
		$replacements = array(
			'!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+",
			"$", ",", "/", "?", "%", "#", "[", "]"
		);
		return str_replace($entities, $replacements, urlencode($url));
	}


	public function send() {
		$this->buildMessage();
		if (fwrite($this->connection, $this->requestHeader. $this->requestContents) === false) {
			throw new Exception("Network exception in sending.");
		}
		$this->recive();
	}

	public function recive() {
		$contents = '';
		while(!feof($this->connection)) {
			$contents .= fread($this->connection, 11024*1024);
		}
		fclose($this->connection);
		unset($this->connection);
		$this->rawReponseContents = $contents;

		if (false !== ($pos = strpos($contents, "\r\n\r\n"))) {
			$this->reponseHeader = $this->parseHeader(substr($contents, 0, $newpos = $pos + strlen("\r\n\r\n")));
			$this->reponseContents = substr($contents, $newpos);
			$this->reponseCookies = isset($this->reponseHeader['COOKIES']) ?
					$this->reponseHeader['COOKIES'] : array();
		}else{
			throw new Exception("The response contents is null!");
		}

		$header = $this->reponseHeader;
		if ($header['HTTP_STATUS'] != 200 && !empty($header['LOCATION'])) {
			$this->fixUrl($header['LOCATION']);
			$this->redirection();
			return;
		}
	}


	protected function parseHeader($head) {
		$info = array();
		$head = trim($head);

		if (empty($head)) {
			throw new Exception("The header is empty!");
		}

		$head = explode("\r\n", $head);
		$temp = explode(" ", $head[0]);
		$info['HTTP_VERSION'] = $temp[0];
		$info['HTTP_STATUS']  = $temp[1];
		$info['HTTP_MESSAGE'] = $temp[2];
		array_shift($head);

		foreach($head as $value) {
			$line = explode(':', $value);
			$info[strtoupper(trim($line[0]))] = trim($line[1]);
		}
		return $info;
	}


	public function fixUrl($url) {
		$regex_url	 = '!^https?://[^/]+/?.*$!i';
		$regex_domin = '!^(?: [a-z]+ (?:\.\[a-z]+)* \.(?: cn | com | net | mobi | me | us | gov | uk | jp ) |
							  \d {1,3} \. \d {1,3} \. \d {1,3} \. \d {1,3} | localhost
						  ) ( /? .* ) ? $!ix';

		if (preg_match($regex_url, $url)) {
			$this->targetUrl = $url;
		}elseif (preg_match($regex_domin, $url)) {
			$this->targetUrl = 'http://'. $url;
		}else{
			$this->targetUrl = $this->scheme . $this->host . $this->port;
			switch( $url{0} ) {
				case '/':
					$this->targetUrl .= $url;
					break;
				case '?':
					$this->targetUrl .= $this->path . $url;
					break;
				default:
					$this->targetUrl .= substr($this->path, 0, -1) == '/' ? $this->path . $url : dirname($this->path) . $url;
			}
		}
	}


	public function redirection() {
		if ($this->maxRedirection < 0)
			throw new Exception("Over the max redirection limit!");

		$this->connect($this->targetUrl, $this->requestMethod, $this->isPsock);
		$this->send();
		$this->maxRedirection--;
	}

	public function getReponseHeader() {
		return $this->reponseHeader;
	}

	public function getReponseContents() {
		return $this->reponseContents;
	}

	public function getRawReponseContents() {
		return $this->rawReponseContents;
	}

	public function getRequestContents() {
		return $this->requestHeader. $this->requestContents;
	}
}


