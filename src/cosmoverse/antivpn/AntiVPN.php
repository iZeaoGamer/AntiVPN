<?php

declare(strict_types=1);

namespace cosmoverse\antivpn;

use Closure;
use cosmoverse\antivpn\api\AntiVPNRequest;
use cosmoverse\antivpn\api\AntiVPNResult;
use cosmoverse\antivpn\api\client\AntiVPNClientRequest;
use cosmoverse\antivpn\api\client\AntiVPNClientResult;
use cosmoverse\antivpn\api\ip\AntiVPNIPRequest;
use cosmoverse\antivpn\api\ip\AntiVPNIPResult;
use cosmoverse\antivpn\thread\AntiVPNException;
use cosmoverse\antivpn\thread\AntiVPNThreadPool;
use cosmoverse\antivpn\thread\SSLConfiguration;
use pocketmine\plugin\Plugin;

class AntiVPN{

	protected const URL = "https://antivpn.cosmicpe.me";

	/** @var string */
	protected $api_key;

	/** @var AntiVPNThreadPool */
	protected $pool;

	/**
	 * Leave $ssl_configuration empty to use the recommended
	 * configuration.
	 *
	 * @param Plugin $plugin
	 * @param string $api_key
	 * @param int $thread_count
	 * @param SSLConfiguration|null $ssl_configuration
	 */
	public function __construct(Plugin $plugin, string $api_key, int $thread_count, ?SSLConfiguration $ssl_configuration = null){
		$this->api_key = $api_key;
		$this->pool = $this->createThreadPool($plugin, $thread_count, $ssl_configuration);
	}

	protected function createThreadPool(Plugin $plugin, int $thread_count, ?SSLConfiguration $ssl_configuration = null) : AntiVPNThreadPool{
		return AntiVPNThreadPool::from($plugin, $thread_count, self::URL, $ssl_configuration ?? SSLConfiguration::recommended());
	}

	/**
	 * @param string $ip
	 * @param Closure<AntiVPNIPResult> $on_success
	 * @param Closure<AntiVPNException> $on_failure
	 */
	public function checkIp(string $ip, Closure $on_success, Closure $on_failure) : void{
		$this->request(new AntiVPNIPRequest($this->api_key, $ip), $on_success, $on_failure);
	}

	/**
	 * @param Closure<AntiVPNClientResult> $on_success
	 * @param Closure<AntiVPNException> $on_failure
	 */
	public function getClientData(Closure $on_success, Closure $on_failure) : void{
		$this->request(new AntiVPNClientRequest($this->api_key), $on_success, $on_failure);
	}

	/**
	 * @param AntiVPNRequest $request
	 * @param Closure<AntiVPNResult> $on_success
	 * @param Closure<AntiVPNException> $on_failure
	 */
	public function request(AntiVPNRequest $request, Closure $on_success, Closure $on_failure) : void{
		$this->pool->pickLeastBusyThread()->request($request, $on_success, $on_failure);
	}

	public function waitAll() : void{
		$this->pool->waitAll();
	}

	public function close() : void{
		$this->pool->stop();
	}
}