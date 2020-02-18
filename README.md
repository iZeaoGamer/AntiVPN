# CosmicAntiVPN
<p align=center><img src="https://antivpn.cosmicpe.me/images/logo.png"></img></p>

A virion that integrates with [CosmicPE Anti-VPN](https://antivpn.cosmicpe.me) service. To use this virion, you'll require an api key which you can obtain from the site upon registration.

## Developer Documentation
### Initialization
Construct the `AntiVPN` class and reuse it for every ip-check request. DO NOT and I repeat DO NOT keep recreating this class for each ip-check.
```php
/** @var Plugin $plugin */
$api_key = "2EDD176BC11D6FA05255B20BF6F59EB7312C39FF3374ABE866F2C6C494A6ED2E"; // obtain yours from the site
$thread_count = 2;
$api = new AntiVPN($plugin, $api_key, $thread_count);
```

### Checking whether IP is behind a VPN
```php
/** @var AntiVPNResult|AntiVPNException $result */
$api->check("192.168.1.1", function($result) : void{
	/** @var Logger $logger */
	if($result instanceof AntiVPNResult){
		if($result->isBehindVPN()){
			$logger->info($result->getIp() . " is behind a VPN hosted by " . $result->getMetadata()->getIsp());
		}else{
			$loger->info($result->getIp() . " is not behind a VPN");
		}
	}else{
		assert($result instanceof AntiVPNException);
		$logger->exception($result);
	}
});
```

## Examples
Refer to the [wiki](https://github.com/Cosmoverse/CosmicAntiVPN/wiki/Examples) for examples.
