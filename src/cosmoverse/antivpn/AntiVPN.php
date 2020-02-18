<?php

declare(strict_types=1);

namespace cosmoverse\antivpn;

use Closure;
use cosmoverse\antivpn\thread\AntiVPNException;
use cosmoverse\antivpn\thread\AntiVPNResult;
use cosmoverse\antivpn\thread\AntiVPNThreadPool;
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
	 * @param Closure<AntiVPNResult> $on_success
	 * @param Closure<AntiVPNException> $on_failure
	 */
	public function check(string $ip, Closure $on_success, Closure $on_failure) : void{
		$this->pool->pickLeastBusyThread()->request($ip, $this->api_key, $on_success, $on_failure);
	}

	public function close() : void{
		$this->pool->stop();
	}
}