<?php

declare(strict_types=1);

namespace cosmicpe\antivpn\thread;

use JsonSerializable;

/**
 * @internal
 */
final class AntiVPNRequest implements JsonSerializable{

	/** @var string */
	public $ip;

	/** @var string */
	public $key;

	/** @var int */
	public $callback_id;

	public function __construct(string $ip, string $key, int $callback_id){
		$this->ip = $ip;
		$this->key = $key;
		$this->callback_id = $callback_id;
	}

	public function jsonSerialize() : array{
		return [
			"key" => $this->key,
			"ip" => $this->ip
		];
	}
}