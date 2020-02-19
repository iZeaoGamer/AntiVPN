<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\thread\request;

use cosmoverse\antivpn\api\AntiVPNRequest;

/**
 * @internal
 */
final class AntiVPNRequestHolder{

	/** @var AntiVPNRequest */
	public $request;

	/** @var int */
	public $callback_id;

	public function __construct(AntiVPNRequest $request, int $callback_id){
		$this->request = $request;
		$this->callback_id = $callback_id;
	}
}