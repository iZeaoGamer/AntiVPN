<?php

declare(strict_types=1);

namespace cosmicpe\antivpn\thread;

final class AntiVPNResult{

	/** @var string */
	private $ip;

	/** @var bool */
	private $is_vpn;

	/** @var AntiVPNResultMetadata */
	private $metadata;

	public function __construct(string $ip, bool $is_vpn, AntiVPNResultMetadata $metadata){
		$this->ip = $ip;
		$this->is_vpn = $is_vpn;
		$this->metadata = $metadata;
	}

	public function getIp() : string{
		return $this->ip;
	}

	public function isBehindVPN() : bool{
		return $this->is_vpn;
	}

	public function getMetadata() : AntiVPNResultMetadata{
		return $this->metadata;
	}
}