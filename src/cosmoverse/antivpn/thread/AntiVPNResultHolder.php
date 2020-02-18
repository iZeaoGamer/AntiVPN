<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\thread;

use InvalidArgumentException;

final class AntiVPNResultHolder{

	/** @var int */
	public $callback_id;

	/** @var AntiVPNResult|AntiVPNException */
	public $result;

	/**
	 * @param int $callback_id
	 * @param AntiVPNResult|AntiVPNException $result
	 */
	public function __construct(int $callback_id, $result){
		if(!($result instanceof AntiVPNResult) && !($result instanceof AntiVPNException)){
			throw new InvalidArgumentException("Expected result of type " . AntiVPNResult::class . " or " . AntiVPNException::class);
		}

		$this->callback_id = $callback_id;
		$this->result = $result;
	}

	/**
	 * @return AntiVPNException|AntiVPNResult
	 */
	public function getResult(){
		return $this->result;
	}
}