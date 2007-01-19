#!/usr/local/bin/php
<?php
/*
	services_nfs.php
	part of FreeNAS (http://www.freenas.org)
	Copyright (C) 2005-2007 Olivier Cochard <olivier@freenas.org>.
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

$pgtitle = array(gettext("Services"),gettext("NFS"));

if (!is_array($config['nfs'])) {
	$config['nfs'] = array();
}

$pconfig['enable'] = isset($config['nfs']['enable']);
$pconfig['mapall'] = $config['nfs']['mapall'];

list($pconfig['network'],$pconfig['network_subnet']) =
		explode('/', $config['nfs']['nfsnetwork']);

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	/* input validation */
	$reqdfields = array();
	$reqdfieldsn = array();

  if ($_POST['enable']) {
    $reqdfields = array_merge($reqdfields, explode(" ", "network network_subnet"));
    $reqdfieldsn = array_merge($reqdfieldsn, array(gettext("Authorised network"),gettext("Subnet bit count")));
  }

	do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);

	if (($_POST['network'] && !is_ipaddr($_POST['network']))) {
		$input_errors[] = gettext("A valid network must be specified.");
	}
	if (($_POST['network_subnet'] && !is_numeric($_POST['network_subnet']))) {
		$input_errors[] = gettext("A valid network bit count must be specified.");
	}

	$osn = gen_subnet($_POST['network'], $_POST['network_subnet']) . "/" . $_POST['network_subnet'];

	if (!$input_errors) {
		$config['nfs']['enable'] = $_POST['enable'] ? true : false;
		$config['nfs']['mapall'] = $_POST['mapall'];
		$config['nfs']['nfsnetwork'] = $osn;

		write_config();

		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			/* nuke the cache file */
			config_lock();
			services_nfs_configure();
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
	}
}
?>
<?php include("fbegin.inc"); ?>
<script language="JavaScript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
  document.iform.mapall.disabled = endis;
  document.iform.network.disabled = endis;
  document.iform.network_subnet.disabled = endis;
}
//-->
</script>
<?php if ($input_errors) print_input_errors($input_errors); ?>
<?php if ($savemsg) print_info_box($savemsg); ?>
<form action="services_nfs.php" method="post" name="iform" id="iform">
  <table width="100%" border="0" cellpadding="6" cellspacing="0">
    <tr>
      <td colspan="2" valign="top" class="optsect_t">
        <table border="0" cellspacing="0" cellpadding="0" width="100%">
          <tr>
            <td class="optsect_s"><strong><?=gettext("NFS Server"); ?></strong></td>
            <td align="right" class="optsect_s">
              <input name="enable" type="checkbox" value="yes" <?php if ($pconfig['enable']) echo "checked"; ?> onClick="enable_change(false)"> <strong><?=gettext("Enable") ;?></strong>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td width="22%" valign="top" class="vncellreq"><?=gettext("Map all users to root"); ?></td>
      <td width="78%" class="vtable">
        <select name="mapall" class="formfld" id="mapall">
        <?php $types = array(gettext("Yes"),gettext("No"));?>
        <?php $vals = explode(" ", "yes no");?>
        <?php $j = 0; for ($j = 0; $j < count($vals); $j++): ?>
          <option value="<?=$vals[$j];?>" <?php if ($vals[$j] == $pconfig['mapall']) echo "selected";?>>
          <?=htmlspecialchars($types[$j]);?>
          </option>
        <?php endfor; ?>
        </select><br>
        <?=gettext("All users will have the root privilege.") ;?>
      </td>
    </tr>
    <tr>
      <td width="22%" valign="top" class="vncellreq"><?=gettext("Authorised network") ; ?></td>
      <td width="78%" class="vtable">
        <?=$mandfldhtml;?><input name="network" type="text" class="formfld" id="network" size="20" value="<?=htmlspecialchars($pconfig['network']);?>"> / 
        <select name="network_subnet" class="formfld" id="network_subnet">
          <?php for ($i = 32; $i >= 1; $i--): ?>
          <option value="<?=$i;?>" <?php if ($i == $pconfig['network_subnet']) echo "selected"; ?>>
          <?=$i;?>
          </option>
          <?php endfor; ?>
        </select><br>
        <span class="vexpl"><?=gettext("Network that is authorised to access to NFS share") ;?></span>
      </td>
    </tr>
    <tr>
      <td width="22%" valign="top">&nbsp;</td>
      <td width="78%">
        <input name="Submit" type="submit" class="formbtn" value="<?=gettext("Save");?>" onClick="enable_change(true)">
      </td>
    </tr>
    <tr>
      <td width="22%" valign="top">&nbsp;</td>
      <td width="78%"><span class="vexpl"><span class="red"><strong><?=gettext("Warning");?>:</strong></span><br><?=gettext("The name of the exported directories are : /mnt/sharename");?></span></td>
    </tr>
  </table>
</form>
<script language="JavaScript">
<!--
enable_change(false);
//-->
</script>
<?php include("fend.inc"); ?>
