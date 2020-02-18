<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\thread;

final class AntiVPNResultMetadata{

	/** @var string */
	private $isp;

	/** @var int */
	private $indexed;

	public function __construct(string $isp, int $indexed){
		$this->isp = $isp;
		$this->indexed = $indexed;
	}

	public function getIsp() : string{
		return $this->isp;
	}

	public function getIndexed() : int{
		return $this->indexed;
	}
}