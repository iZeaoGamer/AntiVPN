<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\api\client;

class AntiVPNClientAPIHitsResult{

	/** @var int */
	private $hits;

	/** @var bool */
	private $reached_limit;

	public function __construct(int $hits, bool $reached_limit){
		$this->hits = $hits;
		$this->reached_limit = $reached_limit;
	}

	public function getHits() : int{
		return $this->hits;
	}

	public function hasReachedLimit() : bool{
		return $this->reached_limit;
	}

	public function __toString() : string{
		return "[hits: " . $this->hits . ", reached_limit: " . var_export($this->reached_limit, true) . "]";
	}
}