#!/usr/local/bin/php
<?php 
/*
	system_routes_edit.php
	part of FreeNAS (http://www.freenas.org)
	Copyright (C) 2005-2007 Olivier Cochard-Labbe <olivier@freenas.org>.
	All rights reserved.
	
	Based on m0n0wall (http://m0n0.ch/wall)
	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>.
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
require("guiconfig.inc");

$id = $_GET['id'];
if (isset($_POST['id']))
	$id = $_POST['id'];

$pgtitle = array(gettext("System"),gettext("Static routes"),isset($id)?gettext("Edit"):gettext("Add"));

if (!is_array($config['staticroutes']['route']))
	$config['staticroutes']['route'] = array();

staticroutes_sort();
$a_routes = &$config['staticroutes']['route'];

if (isset($id) && $a_routes[$id]) {
	$pconfig['interface'] = $a_routes[$id]['interface'];
	list($pconfig['network'],$pconfig['network_subnet']) = 
		explode('/', $a_routes[$id]['network']);
	$pconfig['gateway'] = $a_routes[$id]['gateway'];
	$pconfig['descr'] = $a_routes[$id]['descr'];
}

if ($_POST) {

	unset($input_errors);
	$pconfig = $_POST;

	/* input validation */
	$reqdfields = explode(" ", "interface network network_subnet gateway");
	$reqdfieldsn = array(gettext("Interface"),gettext("Destination network"),gettext("Destination network bit count"),gettext("Gateway"));
	
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);
	
	if (($_POST['network'] && !is_ipaddr($_POST['network']))) {
		$input_errors[] = gettext("A valid destination network must be specified.");
	}
	
	if (($_POST['network'] && is_ipv4addr($_POST['network'])) && $_POST['network_subnet'])  {
		if (filter_var($_POST['network_subnet'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 32))) == false)
			$input_errors[] = gettext("A valid IPv4 network bit count must be specified.");
	}
	
	if (($_POST['network'] && is_ipv6addr($_POST['network'])) && $_POST['network_subnet'])  {
		if (filter_var($_POST['network_subnet'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 128))) == false)
			$input_errors[] = gettext("A valid IPv6 prefix must be specified.");
	}
	
	if (($_POST['gateway'] && !is_ipaddr($_POST['gateway']))) {
		$input_errors[] = gettext("A valid gateway IP address must be specified.");
	}
	
	if ($_POST['gateway'] && $_POST['network']) {
		if (is_ipv4addr($_POST['gateway']) && !is_ipv4addr($_POST['network'])) {
			$input_errors[] = gettext("You must the same IP type for network and gateway.");
		} else if (is_ipv6addr($_POST['gateway']) && !is_ipv6addr($_POST['network'])) {
			$input_errors[] = gettext("IP type mistmatch for network and gateway.");
		}
	}
	
	/* check for overlaps */
	/* gen_subnet work for IPv4 only... This function permit to fix user input error for network number*/
	if (is_ipv4addr($_POST['network'])) {
		$osn = gen_subnet($_POST['network'], $_POST['network_subnet']) . "/" . $_POST['network_subnet'];
	} else {
		$osn = $_POST['network'] . "/" . $_POST['network_subnet'] ;
	}
	
	foreach ($a_routes as $route) {
		if (isset($id) && ($a_routes[$id]) && ($a_routes[$id] === $route))
			continue;

		if ($route['network'] == $osn) {
			$input_errors[] = gettext("A route to this destination network already exists.");
			break;
		}
	}

	if (!$input_errors) {
		$route = array();
		$route['interface'] = $_POST['interface'];
		$route['network'] = $osn;
		$route['gateway'] = $_POST['gateway'];
		$route['descr'] = $_POST['descr'];

		if (isset($id) && $a_routes[$id])
			$a_routes[$id] = $route;
		else
			$a_routes[] = $route;
		
		touch($d_staticroutesdirty_path);
		
		write_config();
		
		header("Location: system_routes.php");
		exit;
	}
}
?>
<?php include("fbegin.inc"); ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
		<td class="tabnavtbl">
  		<ul id="tabnav">
				<li class="tabact"><a href="system_routes.php" style="color:black" title="<?=gettext("Reload page");?>"><?=gettext("Static routes");?></a></li>
  		</ul>
  	</td>
	</tr>
  <tr>
    <td class="tabcont">
      <form action="system_routes_edit.php" method="post" name="iform" id="iform">
      	<?php if ($input_errors) print_input_errors($input_errors); ?>
        <table width="100%" border="0" cellpadding="6" cellspacing="0">
          <tr> 
            <td width="22%" valign="top" class="vncellreq"><?=gettext("Interface");?></td>
            <td width="78%" class="vtable">
							<select name="interface" class="formfld">
                <?php $interfaces = array('lan' => 'LAN');
							  for ($i = 1; isset($config['interfaces']['opt' . $i]); $i++) {
							  	$interfaces['opt' . $i] = $config['interfaces']['opt' . $i]['descr'];
							  }
							  foreach ($interfaces as $iface => $ifacename): ?>
                <option value="<?=$iface;?>" <?php if ($iface == $pconfig['interface']) echo "selected"; ?>> 
                <?=htmlspecialchars($ifacename);?>
                </option>
                <?php endforeach; ?>
            	</select> <br>
              <span class="vexpl"><?=gettext("Choose which interface this route applies to.");?></span>
						</td>
          </tr>
          <tr>
            <td width="22%" valign="top" class="vncellreq"><?=gettext("Destination network");?></td>
            <td width="78%" class="vtable"> 
							<?=$mandfldhtml;?><input name="network" type="text" class="formfld" id="network" size="20" value="<?=htmlspecialchars($pconfig['network']);?>"> 
							/
							<input name="network_subnet" type="text" class="formfld" id="network_subnet" size="2" value="<?=htmlspecialchars($pconfig['network_subnet']);?>">
							<br><span class="vexpl"><?=gettext("Destination network for this static route");?></span>
						</td>
          </tr>
					<tr>
            <td width="22%" valign="top" class="vncellreq"><?=gettext("Gateway");?></td>
            <td width="78%" class="vtable"> 
              <?=$mandfldhtml;?><input name="gateway" type="text" class="formfld" id="gateway" size="40" value="<?=htmlspecialchars($pconfig['gateway']);?>">
              <br> <span class="vexpl"><?=gettext("Gateway to be used to reach the destination network");?></span></td>
          </tr>
					<tr>
            <td width="22%" valign="top" class="vncell"><?=gettext("Description");?></td>
            <td width="78%" class="vtable"> 
              <input name="descr" type="text" class="formfld" id="descr" size="40" value="<?=htmlspecialchars($pconfig['descr']);?>">
              <br> <span class="vexpl"><?=gettext("You may enter a description here for your reference (not parsed).");?></span></td>
          </tr>
          <tr>
            <td width="22%" valign="top">&nbsp;</td>
            <td width="78%"> 
              <input name="Submit" type="submit" class="formbtn" value="<?=((isset($id) && $a_routes[$id]))?gettext("Save"):gettext("Add")?>">
              <?php if (isset($id) && $a_routes[$id]): ?>
              <input name="id" type="hidden" value="<?=$id;?>">
              <?php endif; ?>
            </td>
          </tr>
        </table>
			</form>
		</td>
	</tr>
</table>
<?php include("fend.inc"); ?>
