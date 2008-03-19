#!/usr/local/bin/php
<?php
/*
	disks_manage_smart.php
	Copyright � 2006-2008 Volker Theile (votdev@gmx.de)
	All rights reserved.

	part of FreeNAS (http://www.freenas.org)
	Copyright (C) 2005-2008 Olivier Cochard-Labb� <olivier@freenas.org>.
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

$pgtitle = array(gettext("Disks"),gettext("Management"),gettext("S.M.A.R.T."));

if (!is_array($config['smartd']['selftest']))
	$config['smartd']['selftest'] = array();

$a_type = array( "S" => "Short Self-Test", "L" => "Long Self-Test", "C" => "Conveyance Self-Test", "O" => "Offline Immediate Test");
$a_selftest = &$config['smartd']['selftest'];

$pconfig['enable'] = isset($config['smartd']['enable']);

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (!$input_errors) {
		$config['smartd']['enable'] = $_POST['enable'] ? true : false;

		write_config();

		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_update_service("smartd");
			config_unlock();
		}

		$savemsg = get_std_save_message($retval);

		if ($retval == 0) {
			if (file_exists($d_smartconfdirty_path))
				unlink($d_smartconfdirty_path);
		}
	}
}

if ($_GET['act'] == "del") {
	if ($a_selftest[$_GET['id']]) {
		unset($a_selftest[$_GET['id']]);

		write_config();
		touch($d_smartconfdirty_path);

		header("Location: disks_manage_smart.php");
		exit;
	}
}
?>
<?php include("fbegin.inc");?>
<script language="JavaScript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
    <td class="tabnavtbl">
      <ul id="tabnav">
      	<li class="tabinact"><a href="disks_manage.php"><?=gettext("Management");?></a></li>
				<li class="tabact"><a href="disks_manage_smart.php" title="<?=gettext("Reload page");?>"><?=gettext("S.M.A.R.T.");?></a></li>
				<li class="tabinact"><a href="disks_manage_iscsi.php"><?=gettext("iSCSI Initiator");?></a></li>
      </ul>
    </td>
  </tr>
  <tr> 
    <td class="tabcont">
      <form action="disks_manage_smart.php" method="post">
        <?php if ($savemsg) print_info_box($savemsg);?>
        <?php if (file_exists($d_smartconfdirty_path)):?><p>
        <?php print_info_box_np(gettext("The configuration has been modified.<br>You must apply the changes in order for them to take effect."));?><br>
        <input name="apply" type="submit" class="formbtn" id="apply" value="<?=gettext("Apply changes");?>"></p>
        <?php endif;?>
        <table width="100%" border="0" cellpadding="6" cellspacing="0">
        	<tr>
				    <td colspan="2" valign="top" class="optsect_t">
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td class="optsect_s"><strong><?=gettext("Self-Monitoring, Analysis and Reporting Technology");?></strong></td>
									<td align="right" class="optsect_s">
										<input name="enable" type="checkbox" value="yes" <?php if ($pconfig['enable']) echo "checked"; ?> onClick="enable_change(false)"><strong><?=gettext("Enable");?></strong>
									</td>
								</tr>
							</table>
						</td>
				  </tr>
				  <tr>
			    	<td width="22%" valign="top" class="vncell"><?=gettext("Scheduled self-tests");?></td>
						<td width="78%" class="vtable">
				      <table width="100%" border="0" cellpadding="0" cellspacing="0">
				        <tr>
									<td width="20%" class="listhdrr"><?=gettext("Disk");?></td>
									<td width="30%" class="listhdrr"><?=gettext("Type");?></td>
									<td width="40%" class="listhdrr"><?=gettext("Description");?></td>
									<td width="10%" class="list"></td>
				        </tr>
							  <?php $i = 0; foreach($a_selftest as $selftest):?>
				        <tr>
				          <td class="listlr"><?=htmlspecialchars($selftest['devicespecialfile']);?>&nbsp;</td>
									<td class="listr"><?=htmlspecialchars(gettext($a_type[$selftest['type']]));?>&nbsp;</td>
									<td class="listr"><?=htmlspecialchars($selftest['desc']);?>&nbsp;</td>
				          <td valign="middle" nowrap class="list">
				          	<a href="disks_manage_smart_edit.php?id=<?=$i;?>"><img src="e.gif" title="<?=gettext("Edit self-test");?>" width="17" height="17" border="0"></a>
				            <a href="disks_manage_smart.php?act=del&id=<?=$i;?>" onclick="return confirm('<?=gettext("Do you really want to delete this scheduled self-test?");?>')"><img src="x.gif" title="<?=gettext("Delete self-test");?>" width="17" height="17" border="0"></a>
				          </td>
				        </tr>
				        <?php $i++; endforeach;?>
				        <tr>
				          <td class="list" colspan="3"></td>
				          <td class="list"><a href="disks_manage_smart_edit.php"><img src="plus.gif" title="<?=gettext("Add self-test");?>" width="17" height="17" border="0"></a></td>
						    </tr>
							</table>
							<span class="vexpl"><?=gettext("Add additional scheduled self-test.");?></span>
						</td>
					</tr>
				  <tr>
				    <td width="22%" valign="top">&nbsp;</td>
				    <td width="78%">
				      <input name="Submit" type="submit" class="formbtn" value="<?=gettext("Save and Restart");?>" onClick="enable_change(true)">
				    </td>
				  </tr>
			  </table>
      </form>
	  </td>
  </tr>
</table>
<?php include("fend.inc"); ?>