<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\thread\result;

final class AntiVPNResultHolder{

	/** @var int */
	public $callback_id;

	/** @var AntiVPNResultType */
	public $type;

	public function __construct(int $callback_id, AntiVPNResultType $type){
		$this->callback_id = $callback_id;
		$this->type = $type;
	}
}