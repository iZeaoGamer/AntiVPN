<?php

declare(strict_types=1);

namespace cosmoverse\antivpn;

use Closure;
use cosmoverse\antivpn\api\AntiVPNRequest;
use cosmoverse\antivpn\api\AntiVPNResult;
use cosmoverse\antivpn\api\ip\AntiVPNIPRequest;
use cosmoverse\antivpn\thread\AntiVPNException;
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

	public function checkIp(string $ip, Closure $on_success, Closure $on_failure) : void{
		$this->request(new AntiVPNIPRequest($this->api_key, $ip), $on_success, $on_failure);
	}

	/**
	 * @param AntiVPNRequest $request
	 * @param Closure<AntiVPNResult> $on_success
	 * @param Closure<AntiVPNException> $on_failure
	 */
	public function request(AntiVPNRequest $request, Closure $on_success, Closure $on_failure) : void{
		$this->pool->pickLeastBusyThread()->request($request, $on_success, $on_failure);
	}

	public function close() : void{
		$this->pool->stop();
	}
}