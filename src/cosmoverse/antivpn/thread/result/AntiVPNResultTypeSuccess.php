<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\thread\result;

use cosmoverse\antivpn\api\AntiVPNResult;
use cosmoverse\antivpn\thread\request\AntiVPNRequestCallback;

final class AntiVPNResultTypeSuccess implements AntiVPNResultType{

	/** @var AntiVPNResult */
	private $result;

	public function __construct(AntiVPNResult $result){
		$this->result = $result;
	}

	public function notify(AntiVPNRequestCallback $callback) : void{
		($callback->on_success)($this->result);
	}
}