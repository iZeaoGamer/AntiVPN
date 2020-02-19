<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\api\client;

use cosmoverse\antivpn\utils\TimeUtils;

class AntiVPNClientMembershipResult{

	/** @var int */
	private $time_diff;

	public function __construct(int $time_diff){
		$this->time_diff = $time_diff;
	}

	public function isFree() : bool{
		return $this->time_diff === -1;
	}

	public function isForever() : bool{
		return $this->time_diff === INT32_MAX;
	}

	public function getTimeDiff() : int{
		return $this->time_diff;
	}

	public function __toString() : string{
		return $this->isFree() ? "free" : "unlimited " . ($this->isForever() ? "forever" : "until " . TimeUtils::formatTimeDiff($this->time_diff));
	}
}