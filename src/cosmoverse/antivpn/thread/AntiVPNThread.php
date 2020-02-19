<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\thread;

use ClassLoader;
use Closure;
use cosmoverse\antivpn\api\AntiVPNRequest;
use cosmoverse\antivpn\thread\request\AntiVPNRequestCallback;
use cosmoverse\antivpn\thread\request\AntiVPNRequestHolder;
use cosmoverse\antivpn\thread\result\AntiVPNResultHolder;
use cosmoverse\antivpn\thread\result\AntiVPNResultTypeFailure;
use cosmoverse\antivpn\thread\result\AntiVPNResultTypeSuccess;
use Exception;
use pocketmine\Server;
use pocketmine\Thread;
use poggit\libasynql\libasynql;
use Threaded;

final class AntiVPNThread extends Thread{

	/** @var int */
	private static $callback_ids = 0;

	/** @var AntiVPNRequestCallback[] */
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

		if(class_exists(libasynql::class) && !libasynql::isPackaged()){
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

	public function isBusy() : bool{
		return count($this->incoming) > 0 || count($this->outgoing) > 0;
	}

	public function getBusyScore() : int{
		return count($this->incoming) + ($this->working ? INT32_MAX : 0);
	}

	public function request(AntiVPNRequest $request, Closure $on_success, Closure $on_failure) : void{
		self::$callbacks[$callback_id = ++self::$callback_ids] = new AntiVPNRequestCallback($on_success, $on_failure);
		$this->incoming[] = igbinary_serialize(new AntiVPNRequestHolder($request, $callback_id));
		$this->synchronized(function() : void{
			$this->notify();
		});
	}

	public function run() : void{
		$this->running = true;
		$this->registerClassLoader();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		while($this->running){
			while(($incoming = $this->incoming->shift()) !== null){
				$this->working = true;

				/** @var AntiVPNRequestHolder $incoming */
				$incoming = igbinary_unserialize($incoming);
				$request = $incoming->request;

				curl_setopt($ch, CURLOPT_URL, $this->url . $request->api_path);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
				$result = curl_exec($ch);

				if($result !== false){
					try{
						$json = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
						if($json["success"]){
							$result = new AntiVPNResultTypeSuccess($request->createResult($json));
						}else{
							$result = new AntiVPNResultTypeFailure(new AntiVPNException($json["error"]));
						}
					}catch(Exception $e){
						$result = new AntiVPNResultTypeFailure(new AntiVPNException($e->getMessage()));
					}
				}else{
					$result = new AntiVPNResultTypeFailure(new AntiVPNException("Failed to connect with AntiVPN"));
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
			$holder->type->notify($cb);
		}
	}

	public function setGarbage(){
		// wha
	}
}