<?php
/*
	services.inc
	Copyright (c) 2008-2009 Volker Theile (votdev@gmx.de)
	All rights reserved.

	part of FreeNAS (http://freenas.org)
	Copyright (C) 2005-2009 Olivier Cochard-Labbe <olivier@freenas.org>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/
require_once("config.inc");

// Check if the given port is already used by another service.
// @param port		The port to be checked.
// @param except	The key to be ignored.
// @result true if the port is already used by another service, otherwise false.
function services_is_port_used($port, $except = "") {
	global $config;

	$a_port = array(
		"sysgui" => $config['system']['webgui']['port'],
		"daap" => $config['daap']['port'],
		"websrv" => $config['websrv']['port'],
		"upnp" => $config['upnp']['port'],
		"bittorrent" => $config['bittorrent']['port']
	);

	// Special handling for webgui port.
	if (!isset($config['system']['webgui']['port'])
            || $config['system']['webgui']['port'] == '') {
		if ($config['system']['webgui']['protocol'] == 'https') {
			$a_port['sysgui'] = 443;
		} else {
			$a_port['sysgui'] = 80;
		}
	}

	if (0 < strlen($except))
		unset($a_port[$except]);

	return in_array($port, $a_port);
}
?>
