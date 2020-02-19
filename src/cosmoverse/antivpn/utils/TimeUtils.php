<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\utils;

final class TimeUtils{

	public static function formatTimeDiff(int $time) : string{
		// snippet from pocketmine's StatusCommand.php

		$seconds = floor($time % 60);
		$minutes = null;
		$hours = null;
		$days = null;

		if($time >= 60){
			$minutes = floor(($time % 3600) / 60);
			if($time >= 3600){
				$hours = floor(($time % (3600 * 24)) / 3600);
				if($time >= 3600 * 24){
					$days = floor($time / (3600 * 24));
				}
			}
		}

		return ($minutes !== null ?
				($hours !== null ?
					($days !== null ?
						"$days days "
						: "") . "$hours hours "
					: "") . "$minutes minutes "
				: "") . "$seconds seconds";
	}
}
