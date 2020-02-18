<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\thread;

use Closure;

/**
 * @internal
 */
final class AntiVPNResultCallback{

	/** @var Closure<AntiVPNResult> */
	public $on_success;

	/** @var Closure<AntiVPNException> */
	public $on_failure;

	/**
	 * @param Closure<AntiVPNResult> $on_success
	 * @param Closure<AntiVPNException> $on_failure
	 */
	public function __construct(Closure $on_success, Closure $on_failure){
		$this->on_success = $on_success;
		$this->on_failure = $on_failure;
	}
}