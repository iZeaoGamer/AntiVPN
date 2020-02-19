<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\api\client;

use cosmoverse\antivpn\api\AntiVPNResult;

class AntiVPNClientResult implements AntiVPNResult{

	/** @var int */
	private $id;

	/** @var string */
	private $email;

	/** @var string */
	private $name;

	/** @var int */
	private $joined;

	/** @var string */
	private $state;

	/** @var AntiVPNClientMembershipResult */
	private $membership;

	/** @var AntiVPNClientAPIHitsResult */
	private $api_hits;

	public function __construct(int $id, string $email, string $name, int $joined, string $state, int $premium, AntiVPNClientAPIHitsResult $api_hits){
		$this->id = $id;
		$this->email = $email;
		$this->name = $name;
		$this->joined = $joined;
		$this->state = $state;
		$this->membership = new AntiVPNClientMembershipResult($premium);
		$this->api_hits = $api_hits;
	}

	public function getId() : int{
		return $this->id;
	}

	public function getEmail() : string{
		return $this->email;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getJoined() : int{
		return $this->joined;
	}

	public function getState() : string{
		return $this->state;
	}

	public function getMembership() : AntiVPNClientMembershipResult{
		return $this->membership;
	}

	public function getApiHits() : AntiVPNClientAPIHitsResult{
		return $this->api_hits;
	}

	public function __toString() : string{
		return "\"" . $this->name . "\" (#" . $this->id . ") [api_hits: " . $this->api_hits . ", membership: " . $this->membership . "]";
	}
}