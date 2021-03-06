<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\thread;

use InvalidArgumentException;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\ClosureTask;

final class AntiVPNThreadPool{

	public static function from(Plugin $plugin, int $capacity, string $url, SSLConfiguration $ssl_configuration) : AntiVPNThreadPool{
		$threads = [];
		for($i = 0; $i < $capacity; $i++){
			$threads[] = new AntiVPNThread($url, $ssl_configuration);
		}
		return new AntiVPNThreadPool($plugin, $threads, $ssl_configuration);
	}

	/** @var AntiVPNThread[] */
	private $threads = [];

	/** @var SSLConfiguration */
	private $ssl_configuration;

	/**
	 * @param Plugin $plugin
	 * @param AntiVPNThread[] $threads
	 * @param SSLConfiguration $ssl_configuration
	 */
	public function __construct(Plugin $plugin, array $threads, SSLConfiguration $ssl_configuration){
		if(count($threads) === 0){
			throw new InvalidArgumentException("Empty array passed");
		}

		$this->ssl_configuration = $ssl_configuration;

		foreach($threads as $thread){
			$this->addThread($thread);
		}

		$plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(int $currentTick) : void{
			foreach($this->threads as $thread){
				$thread->collect();
			}
		}), 1);
	}

	public function addThread(AntiVPNThread $thread) : void{
		$this->threads[] = $thread;
		$thread->start(PTHREADS_INHERIT_INI | PTHREADS_INHERIT_CONSTANTS);
	}

	public function pickLeastBusyThread() : AntiVPNThread{
		$best = null;
		$best_score = INF;
		foreach($this->threads as $thread){
			$score = $thread->getBusyScore();
			if($score < $best_score){
				$best_score = $score;
				$best = $thread;
				if($score === 0){
					break;
				}
			}
		}
		return $best;
	}

	public function waitAll() : void{
		foreach($this->threads as $thread){
			while($thread->getBusyScore() > 0){
				$thread->collect();
			}
		}
	}

	public function stop() : void{
		foreach($this->threads as $thread){
			$thread->stop();
			$thread->join();
		}
		$this->ssl_configuration->close();
	}
}