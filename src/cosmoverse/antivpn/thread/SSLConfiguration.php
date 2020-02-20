<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\thread;

final class SSLConfiguration{

	public static function empty() : SSLConfiguration{
		return new self("");
	}

	public static function recommended() : SSLConfiguration{
		static $instance = null;
		return $instance ?? $instance = new self("resources/cacert.pem");
	}

	public static function downloadedFrom(string $url) : SSLConfiguration{
		$file = tmpfile();
		fwrite($file, file_get_contents($url));
		$path = stream_get_meta_data($file)["uri"];
		return new self($path);
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