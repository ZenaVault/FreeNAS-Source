#!/usr/local/bin/php
<?php
/*
	interfaces_lan.php
	part of FreeNAS (http://www.freenas.org)
	Based on m0n0wall (http://m0n0.ch/wall)

	Copyright (C) 2005-2008 Olivier Cochard-Labbe <olivier@freenas.org>.
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

$pgtitle = array(gettext("Interfaces"), gettext("LAN"));

$lancfg = &$config['interfaces']['lan'];
$optcfg = &$config['interfaces']['lan']; // Required for WLAN.

if (strcmp($lancfg['ipaddr'],"dhcp") == 0) {
	$pconfig['type'] = "DHCP";
	$pconfig['ipaddr'] = get_ipaddr($lancfg['if']);
	$pconfig['subnet'] = 24;
} else {
	$pconfig['type'] = "Static";
	$pconfig['ipaddr'] = $lancfg['ipaddr'];
	$pconfig['subnet'] = $lancfg['subnet'];
}

$pconfig['ipv6_enable'] = isset($lancfg['ipv6_enable']);

if (strcmp($lancfg['ipv6addr'],"auto") == 0) {
	$pconfig['ipv6type'] = "Auto";
	$pconfig['ipv6addr'] = get_ipv6addr($lancfg['if']);
} else {
	$pconfig['ipv6type'] = "Static";
	$pconfig['ipv6addr'] = $lancfg['ipv6addr'];
	$pconfig['ipv6subnet'] = $lancfg['ipv6subnet'];
}

$pconfig['gateway'] = get_defaultgateway();
$pconfig['ipv6gateway'] = get_ipv6defaultgateway();

$pconfig['mtu'] = $lancfg['mtu'];
$pconfig['media'] = $lancfg['media'];
$pconfig['mediaopt'] = $lancfg['mediaopt'];
$pconfig['polling'] = isset($lancfg['polling']);
$pconfig['extraoptions'] = $lancfg['extraoptions'];

/* Wireless interface? */
if (isset($lancfg['wireless'])) {
	require("interfaces_wlan.inc");
	wireless_config_init();
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	$reqdfields = array();
	$reqdfieldsn = array();
	$reqdfieldst = array();

	if ($_POST['type'] === "Static") {
		$reqdfields = explode(" ", "ipaddr subnet");
		$reqdfieldsn = array(gettext("IP address"),gettext("Subnet bit count"));

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);

		if (($_POST['ipaddr'] && !is_ipv4addr($_POST['ipaddr'])))
			$input_errors[] = gettext("A valid IPv4 address must be specified.");
		if ($_POST['subnet'] && !filter_var($_POST['subnet'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 32))))
			$input_errors[] = gettext("A valid network bit count (1-32) must be specified.");
	}

	if ($_POST['ipv6type'] === "Static") {
		$reqdfields = array_merge($reqdfields,explode(" ", "ipv6addr ipv6subnet"));
		$reqdfieldsn = array_merge($reqdfieldsn,array(gettext("IPv6 address"),gettext("Prefix")));

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);

		if (($_POST['ipv6addr'] && !is_ipv6addr($_POST['ipv6addr'])))
			$input_errors[] = gettext("A valid IPv6 address must be specified.");
		if ($_POST['ipv6subnet'] && !filter_var($_POST['ipv6subnet'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 128))))
			$input_errors[] = gettext("A valid prefix (1-128) must be specified.");
		if (($_POST['ipv6gatewayr'] && !is_ipv6addr($_POST['ipv6gateway'])))
			$input_errors[] = gettext("A valid IPv6 Gateway address must be specified.");
	}

	/* Wireless interface? */
	if (isset($lancfg['wireless'])) {
		$wi_input_errors = wireless_config_post();
		if ($wi_input_errors) {
			$input_errors = array_merge($input_errors, $wi_input_errors);
		}
	}

	if (!$input_errors) {
		if(strcmp($_POST['type'],"Static") == 0) {
			$lancfg['ipaddr'] = $_POST['ipaddr'];
			$lancfg['subnet'] = $_POST['subnet'];
			$lancfg['gateway'] = $_POST['gateway'];
		} else if (strcmp($_POST['type'],"DHCP") == 0) {
			$lancfg['ipaddr'] = "dhcp";
		}

		$lancfg['ipv6_enable'] = $_POST['ipv6_enable'] ? true : false;

		if(strcmp($_POST['ipv6type'],"Static") == 0) {
			$lancfg['ipv6addr'] = $_POST['ipv6addr'];
			$lancfg['ipv6subnet'] = $_POST['ipv6subnet'];
			$lancfg['ipv6gateway'] = $_POST['ipv6gateway'];
		} else if (strcmp($_POST['ipv6type'],"Auto") == 0) {
			$lancfg['ipv6addr'] = "auto";
		}

		$lancfg['mtu'] = $_POST['mtu'];
		$lancfg['media'] = $_POST['media'];
		$lancfg['mediaopt'] = $_POST['mediaopt'];
		$lancfg['polling'] = $_POST['polling'] ? true : false;
		$lancfg['extraoptions'] = $_POST['extraoptions'];

		write_config();
		touch($d_sysrebootreqd_path);
	}
}
?>
<?php include("fbegin.inc");?>
<script language="JavaScript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.ipv6_enable.checked || enable_change);

	if (enable_change.name == "ipv6_enable") {
		endis = !enable_change.checked;

		document.iform.ipv6type.disabled = endis;
		document.iform.ipv6addr.disabled = endis;
		document.iform.ipv6subnet.disabled = endis;
		document.iform.ipv6gateway.disabled = endis;
	} else {
		document.iform.ipv6type.disabled = endis;
		document.iform.ipv6addr.disabled = endis;
		document.iform.ipv6subnet.disabled = endis;
		document.iform.ipv6gateway.disabled = endis;
	}
}

/* Calculate default IPv4 netmask bits for network's class. */
function calc_netmask_bits(ipaddr) {
    if (ipaddr.search(/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/) != -1) {
        var adr = ipaddr.split(/\./);
        if (adr[0] > 255 || adr[1] > 255 || adr[2] > 255 || adr[3] > 255)
            return "";
        if (adr[0] == 0 && adr[1] == 0 && adr[2] == 0 && adr[3] == 0)
            return "";

		if (adr[0] <= 127)
			return "8";
		else if (adr[0] <= 191)
			return "16";
		else
			return "24";
    }
    else
      return "";
}

function change_netmask_bits() {
	document.iform.subnet.value = calc_netmask_bits(document.iform.ipaddr.value);
}

function type_change() {
  switch(document.iform.type.selectedIndex) {
		case 0: /* Static */
			document.iform.ipaddr.readOnly = 0;
			document.iform.subnet.disabled = 0;
			document.iform.gateway.readOnly = 0;
			break;

    case 1: /* DHCP */
			document.iform.ipaddr.readOnly = 1;
			document.iform.subnet.disabled = 1;
			document.iform.gateway.readOnly = 1;
			break;
  }
}

function ipv6_type_change() {
  switch(document.iform.ipv6type.selectedIndex) {
		case 0: /* Static */
			document.iform.ipv6addr.readOnly = 0;
			document.iform.ipv6subnet.readOnly = 0;
			document.iform.ipv6gateway.readOnly = 0;
			break;

    case 1: /* Autoconfigure */
			document.iform.ipv6addr.readOnly = 1;
			document.iform.ipv6subnet.readOnly = 1;
			document.iform.ipv6gateway.readOnly = 1;
			break;
  }
}

function media_change() {
  switch(document.iform.media.value) {
		case "autoselect":
			showElementById('mediaopt_tr','hide');
			break;

		default:
			showElementById('mediaopt_tr','show');
			break;
  }
}
// -->
</script>
<form action="interfaces_lan.php" method="post" name="iform" id="iform">
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	  <tr>
	    <td class="tabcont">
				<?php if ($input_errors) print_input_errors($input_errors);?>
				<?php if (file_exists($d_sysrebootreqd_path)) print_info_box(get_std_save_message(0));?>
			  <table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php html_titleline(gettext("IPv4 Configuration"));?>
					<?php html_combobox("type", gettext("Type"), $pconfig['type'], array("Static" => "Static", "DHCP" => "DHCP"), gettext(""), true, false, "type_change()");?>
					<tr>
					  <td width="22%" valign="top" class="vncellreq"><?=gettext("IP address");?></td>
					  <td width="78%" class="vtable">
					    <input name="ipaddr" type="text" class="formfld" id="ipaddr" size="20" value="<?=htmlspecialchars($pconfig['ipaddr']);?>">
					    /
					    <select name="subnet" class="formfld" id="subnet">
					      <?php for ($i = 32; $i > 0; $i--):?>
					      <option value="<?=$i;?>" <?php if ($i == $pconfig['subnet']) echo "selected";?>><?=$i;?></option>
					      <?php endfor;?>
					    </select>
					    <img name="calcnetmaskbits" src="calc.gif" title="<?=gettext("Calculate netmask bits");?>" width="16" height="17" align="top" border="0" onclick="change_netmask_bits()" style="cursor:pointer">
					  </td>
					</tr>
					<?php html_inputbox("gateway", gettext("Gateway"), $pconfig['gateway'], gettext(""), true, 20);?>
					<?php html_separator();?>
					<?php html_titleline_checkbox("ipv6_enable", gettext("IPv6 Configuration"), $pconfig['ipv6_enable'] ? true : false, gettext("Activate"), "enable_change(this)");?>
					<?php html_combobox("ipv6type", gettext("Type"), $pconfig['ipv6type'], array("Static" => "Static", "Auto" => "Auto"), gettext(""), true, false, "ipv6_type_change()");?>
					<tr>
					  <td width="22%" valign="top" class="vncellreq"><?=gettext("IP address");?></td>
					  <td width="78%" class="vtable">
					    <input name="ipv6addr" type="text" class="formfld" id="ipv6addr" size="30" value="<?=htmlspecialchars($pconfig['ipv6addr']);?>">
							/
							<input name="ipv6subnet" type="text" class="formfld" id="ipv6subnet" size="2" value="<?=htmlspecialchars($pconfig['ipv6subnet']);?>">
					  </td>
					</tr>
					<?php html_inputbox("ipv6gateway", gettext("Gateway"), $pconfig['ipv6gateway'], gettext(""), true, 20);?>
					<?php html_separator();?>
					<?php html_titleline(gettext("Advanced Configuration"));?>
					<?php html_inputbox("mtu", gettext("MTU"), $pconfig['mtu'], gettext("Set the maximum transmission unit of the interface to n, default is interface specific. The MTU is used to limit the size of packets that are transmitted on an interface. Not all interfaces support setting the MTU, and some interfaces have range restrictions."), false, 5);?>
					<?php html_checkbox("polling", gettext("Device polling"), $pconfig['polling'] ? true : false, gettext("Enable device polling"), gettext("Device polling is a technique that lets the system periodically poll network devices for new data instead of relying on interrupts. This can reduce CPU load and therefore increase throughput, at the expense of a slightly higher forwarding delay (the devices are polled 1000 times per second). Not all NICs support polling."), false);?>
					<?php html_combobox("media", gettext("Type"), $pconfig['media'], array("autoselect" => "autoselect", "10baseT/UTP" => "10baseT/UTP", "100baseTX" => "100baseTX", "1000baseTX" => "1000baseTX", "1000baseSX" => "1000baseSX",), gettext(""), false, false, "media_change()");?>
					<?php html_combobox("mediaopt", gettext("Duplex"), $pconfig['mediaopt'], array("half-duplex" => "half-duplex", "full-duplex" => "full-duplex"), gettext(""), false);?>
					<?php html_inputbox("extraoptions", gettext("Extra options"), $pconfig['extraoptions'], gettext("Extra options to ifconfig (usually empty)."), false, 40);?>
					<?php /* Wireless interface? */
					if (isset($lancfg['wireless']))
						wireless_config_print();
					?>
					<tr>
			      <td width="22%" valign="top">&nbsp;</td>
			      <td width="78%">
			        <input name="Submit" type="submit" class="formbtn" value="<?=gettext("Save");?>" onclick="enable_change(true)">
			      </td>
			    </tr>
			    <tr>
			      <td width="22%" valign="top">&nbsp;</td>
			      <td width="78%"><span class="vexpl"><span class="red"><strong><?=gettext("Warning"); ?>:<br>
							</strong></span><?php echo sprintf(gettext("After you click &quot;Save&quot;, you may also have to do one or more of the following steps before you can access %s again: <ul><li>change the IP address of your computer</li><li>access the webGUI with the new IP address</li></ul>"), get_product_name());?></span>
						</td>
			    </tr>
			  </table>
			</td>
		</tr>
	</table>
</form>
<script language="JavaScript">
<!--
type_change();
ipv6_type_change();
media_change();
enable_change(false);
//-->
</script>
<?php include("fend.inc");?>
