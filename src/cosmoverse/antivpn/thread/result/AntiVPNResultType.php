<?php

declare(strict_types=1);

namespace cosmoverse\antivpn\thread\result;

use cosmoverse\antivpn\thread\request\AntiVPNRequestCallback;

interface AntiVPNResultType{

	public function notify(AntiVPNRequestCallback $callback) : void;
}