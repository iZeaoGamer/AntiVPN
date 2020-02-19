<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\thread\result;

use cosmoverse\antivpn\thread\AntiVPNException;
use cosmoverse\antivpn\thread\request\AntiVPNRequestCallback;

final class AntiVPNResultTypeFailure implements AntiVPNResultType{

	/** @var AntiVPNException */
	private $exception;

	public function __construct(AntiVPNException $exception){
		$this->exception = $exception;
	}

	public function notify(AntiVPNRequestCallback $callback) : void{
		($callback->on_failure)($this->exception);
	}
}