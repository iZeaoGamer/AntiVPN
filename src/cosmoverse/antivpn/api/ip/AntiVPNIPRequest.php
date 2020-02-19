<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\api\ip;

use cosmoverse\antivpn\api\AntiVPNRequest;
use cosmoverse\antivpn\api\AntiVPNResult;

class AntiVPNIPRequest extends AntiVPNRequest{

	/** @var string */
	private $ip;

	public function __construct(string $key, string $ip){
		parent::__construct("/api/ip", $key);
		$this->ip = $ip;
	}

	public function createResult(array $data) : AntiVPNResult{
		return new AntiVPNIPResult($data["ip"], $data["is_vpn"], new AntiVPNIPResultMetadata($data["metadata"]["isp"], $data["metadata"]["indexed"]));
	}

	public function jsonSerialize() : array{
		$data = parent::jsonSerialize();
		$data["ip"] = $this->ip;
		return $data;
	}
}