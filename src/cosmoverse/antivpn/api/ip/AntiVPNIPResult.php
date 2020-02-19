<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\api\ip;

use cosmoverse\antivpn\api\AntiVPNResult;

class AntiVPNIPResult implements AntiVPNResult{

	/** @var string */
	private $ip;

	/** @var bool */
	private $is_vpn;

	/** @@var AntiVPNIPResultMetadata */
	private $metadata;

	public function __construct(string $ip, bool $is_vpn, AntiVPNIPResultMetadata $metadata){
		$this->ip = $ip;
		$this->is_vpn = $is_vpn;
		$this->metadata = $metadata;
	}

	public function getIp() : string{
		return $this->ip;
	}

	public function isVpn() : bool{
		return $this->is_vpn;
	}

	public function getMetadata() : AntiVPNIPResultMetadata{
		return $this->metadata;
	}
}