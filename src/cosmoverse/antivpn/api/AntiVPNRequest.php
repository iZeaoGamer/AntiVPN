<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\api;

use InvalidArgumentException;
use JsonSerializable;

abstract class AntiVPNRequest implements JsonSerializable{

	/** @var string */
	public $api_path;

	/** @var string */
	private $key;

	public function __construct(string $api_path, string $key){
		$this->api_path = $api_path;
		$this->key = $key;
	}

	/**
	 * @param <string, mixed>[] $data
	 * @return AntiVPNResult
	 * @throws InvalidArgumentException
	 */
	abstract public function createResult(array $data) : AntiVPNResult;

	public function jsonSerialize() : array{
		return [
			"key" => $this->key
		];
	}
}