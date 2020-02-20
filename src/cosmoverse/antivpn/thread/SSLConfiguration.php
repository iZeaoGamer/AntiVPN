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
		$resource = tmpfile();
		fwrite($resource, $data);
		return new self(stream_get_meta_data($resource)["uri"], $resource);
	}

	/** @var string */
	private $cainfo_path;

	/** @var resource|null */
	private $resource;

	public function __construct(string $cainfo_path, $resource = null){
		$this->cainfo_path = $cainfo_path;
		$this->resource = $resource;
	}

	public function getCAInfoPath() : string{
		return $this->cainfo_path;
	}

	public function close() : void{
		if($this->resource !== null){
			$this->resource = null;
			fclose($this->resource);
		}
	}
}