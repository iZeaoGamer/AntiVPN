<?php

declare(strict_types=1);

namespace cosmicpe\antivpn\thread;

use ClassLoader;
use Closure;
use Exception;
use pocketmine\Server;
use pocketmine\Thread;
use poggit\libasynql\libasynql;
use Threaded;

final class AntiVPNThread extends Thread{

	/** @var int */
	private static $callback_ids = 0;

	/** @var Closure[] */
	private static $callbacks = [];

	/** @var bool */
	private $running = false;

	/** @var bool */
	private $working = false;

	/** @var string */
	private $url;

	/** @var Threaded<string> */
	private $incoming;

	/** @var Threaded<string> */
	private $outgoing;

	public function __construct(string $url){
		$this->url = $url;
		$this->incoming = new Threaded();
		$this->outgoing = new Threaded();

		if(!libasynql::isPackaged()){
			/** @noinspection PhpUndefinedMethodInspection */
			/** @noinspection NullPointerExceptionInspection */
			/** @var ClassLoader $cl */
			$cl = Server::getInstance()->getPluginManager()->getPlugin("DEVirion")->getVirionClassLoader();
			$this->setClassLoader($cl);
		}
	}

	public function sleep() : void{
		$this->synchronized(function() : void{
			if($this->running){
				$this->wait();
			}
		});
	}

	public function getBusyScore() : int{
		return count($this->incoming) + ($this->working ? INT32_MAX : 0);
	}

	public function request(string $ip, string $key, Closure $callback) : void{
		self::$callbacks[$callback_id = ++self::$callback_ids] = $callback;
		$this->incoming[] = igbinary_serialize(new AntiVPNRequest($ip, $key, $callback_id));
		$this->synchronized(function() : void{
			$this->notify();
		});
	}

	public function run() : void{
		$this->running = true;
		$this->registerClassLoader();

		$ch = curl_init($this->url . "/api/ip");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		while($this->running){
			while(($incoming = $this->incoming->shift()) !== null){
				$this->working = true;

				/** @var AntiVPNRequest $incoming */
				$incoming = igbinary_unserialize($incoming);

				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($incoming));
				$result = curl_exec($ch);

				try{
					$json = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
					if($json["success"]){
						$result = new AntiVPNResult($json["ip"], $json["is_vpn"], new AntiVPNResultMetadata($json["metadata"]["isp"], $json["metadata"]["indexed"]));
					}else{
						$result = new AntiVPNException($json["error"]);
					}
				}catch(Exception $e){
					$result = new AntiVPNException($e->getMessage());
				}

				$this->outgoing[] = igbinary_serialize(new AntiVPNResultHolder($incoming->callback_id, $result));
				$this->working = false;
			}

			$this->sleep();
		}
		curl_close($ch);
	}

	public function stop() : void{
		$this->running = false;
		$this->synchronized(function() : void{
			$this->notify();
		});
	}

	public function collect() : void{
		while(($holder = $this->outgoing->shift()) !== null){
			/** @var AntiVPNResultHolder $holder */
			$holder = igbinary_unserialize($holder);

			$cb = self::$callbacks[$holder->callback_id];
			unset(self::$callbacks[$holder->callback_id]);
			$cb($holder->result);
		}
	}

	public function setGarbage(){
		// wha
	}
}