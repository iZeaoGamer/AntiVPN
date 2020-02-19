<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\thread\request;

use Closure;
use cosmoverse\antivpn\api\AntiVPNRequest;
use cosmoverse\antivpn\thread\AntiVPNException;

final class AntiVPNRequestCallback{

	/** @var Closure<AntiVPNRequest> */
	public $on_success;

	/** @var Closure<AntiVPNException> */
	public $on_failure;

	public function __construct(Closure $on_success, Closure $on_failure){
		$this->on_success = $on_success;
		$this->on_failure = $on_failure;
	}
}