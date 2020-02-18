<?php

declare(strict_types=1);

namespace cosmicpe\antivpn;

use Closure;
use cosmicpe\antivpn\thread\AntiVPNException;
use cosmicpe\antivpn\thread\AntiVPNResult;
use cosmicpe\antivpn\thread\AntiVPNThreadPool;
use pocketmine\plugin\Plugin;

class AntiVPN{

	protected const URL = "https://antivpn.cosmicpe.me";

	/** @var string */
	protected $api_key;

	/** @var AntiVPNThreadPool */
	protected $pool;

	public function __construct(Plugin $plugin, string $api_key, int $thread_count){
		$this->api_key = $api_key;
		$this->pool = $this->createThreadPool($plugin, $thread_count);
	}

	protected function createThreadPool(Plugin $plugin, int $thread_count) : AntiVPNThreadPool{
		return AntiVPNThreadPool::from($plugin, $thread_count, self::URL);
	}

	/**
	 * @param string $ip
	 * @param Closure<AntiVPNResult|AntiVPNException> $callback
	 */
	public function check(string $ip, Closure $callback) : void{
		$this->pool->pickLeastBusyThread()->request($ip, $this->api_key, $callback);
	}

	public function close() : void{
		$this->pool->stop();
	}
}