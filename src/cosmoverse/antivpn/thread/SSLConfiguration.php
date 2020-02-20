<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\thread;

final class SSLConfiguration{

	public static function empty() : SSLConfiguration{
		return new self("");
	}

	public static function recommended() : SSLConfiguration{
		return self::fromData(file_get_contents(__DIR__ . "/resources/cacert.pem"));
	}

	public static function fromURL(string $url) : SSLConfiguration{
		return self::fromData(file_get_contents($url));
	}

	public static function fromData(string $data) : SSLConfiguration{
		$file = tmpfile();
		fwrite($file, $data);
		return new self(stream_get_meta_data($file)["uri"]);
	}

	/** @var string */
	private $cainfo_path;

	public function __construct(string $cainfo_path){
		$this->cainfo_path = $cainfo_path;
	}

	public function getCAInfoPath() : string{
		return $this->cainfo_path;
	}
}