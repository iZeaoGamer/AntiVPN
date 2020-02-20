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
use Logger;
use pocketmine\Server;
use pocketmine\Thread;
use pocketmine\utils\MainLogger;
use poggit\libasynql\libasynql;
use Threaded;

final class AntiVPNThread extends Thread{

	private const MAX_RETRIES = 3;

	/** @var int */
	private static $callback_ids = 0;

	/** @var AntiVPNRequestCallback[] */
	private static $callbacks = [];

	/** @var int */
	private $busy_score = 0;

	/** @var bool */
	private $running = false;

	/** @var string */
	private $url;

	/** @var Logger */
	private $logger;

	/** @var Threaded<string> */
	private $incoming;

	/** @var Threaded<string> */
	private $outgoing;

	public function __construct(string $url){
		$this->url = $url;
		$this->logger = MainLogger::getLogger();
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

	public function getBusyScore() : int{
		return $this->busy_score;
	}

	public function request(AntiVPNRequest $request, Closure $on_success, Closure $on_failure) : void{
		self::$callbacks[$callback_id = ++self::$callback_ids] = new AntiVPNRequestCallback($on_success, $on_failure);
		$this->incoming[] = igbinary_serialize(new AntiVPNRequestHolder($request, $callback_id));
		++$this->busy_score;
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
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		while($this->running){
			while(($incoming = $this->incoming->shift()) !== null){
				/** @var AntiVPNRequestHolder $incoming */
				$incoming = igbinary_unserialize($incoming);
				$request = $incoming->request;

				curl_setopt($ch, CURLOPT_URL, $this->url . $request->api_path);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));

				$retries = 0;
				while(($result = curl_exec($ch)) === false){
					if(++$retries === self::MAX_RETRIES){
						break;
					}
					$this->logger->debug("Failed to connect with AntiVPN, retrying (" . $retries . ")");
				}

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

				if(!$this->running){
					break;
				}
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
			--$this->busy_score;
		}
	}

	public function setGarbage(){
		// wha
	}
}
