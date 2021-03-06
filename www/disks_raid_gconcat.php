#!/usr/local/bin/php
<?php
/*
	disks_raid_gconcat.php
	Modified for XHTML by Daisuke Aoyama (aoyama@peach.ne.jp)
	Copyright (C) 2010 Daisuke Aoyama <aoyama@peach.ne.jp>.
	All rights reserved.

	part of FreeNAS (http://www.freenas.org)
	Copyright (C) 2005-2010 Olivier Cochard-Labbe <olivier@freenas.org>.
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
require("auth.inc");
require("guiconfig.inc");

$pgtitle = array(gettext("Disks"), gettext("Software RAID"), gettext("JBOD"), gettext("Management"));

if ($_POST) {
	$pconfig = $_POST;

	if ($_POST['apply']) {
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			// Process notifications
			$retval = updatenotify_process("raid_gconcat", "gconcat_process_updatenotification");
		}
		$savemsg = get_std_save_message($retval);
		if ($retval == 0) {
			updatenotify_delete("raid_gconcat");
		}
		header("Location: disks_raid_gconcat.php");
		exit;
	}
}

if (!is_array($config['gconcat']['vdisk']))
	$config['gconcat']['vdisk'] = array();

array_sort_key($config['gconcat']['vdisk'], "name");
$a_raid = &$config['gconcat']['vdisk'];

if ($_GET['act'] === "del") {
	unset($errormsg);
	if ($a_raid[$_GET['id']]) {
		// Check if disk is mounted.
		if (0 == disks_ismounted_ex($a_raid[$_GET['id']]['devicespecialfile'], "devicespecialfile")) {
			updatenotify_set("raid_gconcat", UPDATENOTIFY_MODE_DIRTY, $a_raid[$_GET['id']]['uuid']);
			header("Location: disks_raid_gconcat.php");
			exit;
		} else {
			$errormsg = sprintf( gettext("The RAID volume is currently mounted! Remove the <a href='%s'>mount point</a> first before proceeding."), "disks_mount.php");
		}
	}
}

function gconcat_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;

	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
			$retval |= rc_exec_service("geom load concat");
			$retval |= disks_raid_gconcat_configure($data);
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			$retval |= rc_exec_service("geom start concat");
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$retval |= disks_raid_gconcat_delete($data);
			if (is_array($config['gconcat']['vdisk'])) {
				$index = array_search_ex($data, $config['gconcat']['vdisk'], "uuid");
				if (false !== $index) {
					unset($config['gconcat']['vdisk'][$index]);
					write_config();
				}
			}
			break;
	}

	return $retval;
}
?>
<?php include("fbegin.inc");?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr><td class="tabnavtbl">
  <ul id="tabnav">
	<li class="tabact"><a href="disks_raid_gconcat.php" title="<?=gettext("Reload page");?>" ><span><?=gettext("JBOD");?></span></a></li>
	<li class="tabinact"><a href="disks_raid_gstripe.php"><span><?=gettext("RAID 0"); ?></span></a></li>
	<li class="tabinact"><a href="disks_raid_gmirror.php"><span><?=gettext("RAID 1"); ?></span></a></li>
	<li class="tabinact"><a href="disks_raid_graid5.php"><span><?=gettext("RAID 5"); ?></span></a></li>
	<li class="tabinact"><a href="disks_raid_gvinum.php"><span><?=gettext("RAID 0/1/5");?></span></a></li>
  </ul>
  </td></tr>
  <tr><td class="tabnavtbl">
  <ul id="tabnav2">
	<li class="tabact"><a href="disks_raid_gconcat.php" title="<?=gettext("Reload page");?>" ><span><?=gettext("Management");?></span></a></li>
	<li class="tabinact"><a href="disks_raid_gconcat_tools.php"><span><?=gettext("Tools"); ?></span></a></li>
	<li class="tabinact"><a href="disks_raid_gconcat_info.php"><span><?=gettext("Information"); ?></span></a></li>
  </ul>
  </td></tr>
  <tr>
    <td class="tabcont">
			<form action="disks_raid_gconcat.php" method="post">
				<?php if ($errormsg) print_error_box($errormsg); ?>
				<?php if ($savemsg) print_info_box($savemsg); ?>
				<?php if (updatenotify_exists_mode("raid_gconcat", UPDATENOTIFY_MODE_DIRTY)) print_warning_box(gettext("Warning: You are going to delete a RAID volume. All data will get lost and can not be recovered."));?>
				<?php if (updatenotify_exists("raid_gconcat")) print_config_change_box();?>
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="25%" class="listhdrlr"><?=gettext("Volume Name");?></td>
            <td width="25%" class="listhdrr"><?=gettext("Type");?></td>
            <td width="20%" class="listhdrr"><?=gettext("Size");?></td>
            <td width="20%" class="listhdrr"><?=gettext("Status");?></td>
            <td width="10%" class="list"></td>
					</tr>
					<?php $raidstatus = get_gconcat_disks_list();?>
					<?php $i = 0; foreach ($a_raid as $raid):?>
					<?php
					$size = gettext("Unknown");
      		$status = gettext("Stopped");
      		if (is_array($raidstatus) && array_key_exists($raid['name'], $raidstatus)) {
        		$size = $raidstatus[$raid['name']]['size'];
        		$status = $raidstatus[$raid['name']]['state'];
					}

					$notificationmode = updatenotify_get_mode("raid_gconcat", $raid['uuid']);
					switch ($notificationmode) {
						case UPDATENOTIFY_MODE_NEW:
							$size = gettext("Initializing");
							$status = gettext("Initializing");
							break;
						case UPDATENOTIFY_MODE_MODIFIED:
							$size = gettext("Modifying");
							$status = gettext("Modifying");
							break;
						case UPDATENOTIFY_MODE_DIRTY:
							$status = gettext("Deleting");
							break;
					}
          ?>
          <tr>
            <td class="listlr"><?=htmlspecialchars($raid['name']);?></td>
            <td class="listr"><?=htmlspecialchars($raid['type']);?></td>
            <td class="listr"><?=$size;?>&nbsp;</td>
            <td class="listbg"><?=$status;?>&nbsp;</td>
            <?php if (UPDATENOTIFY_MODE_DIRTY != $notificationmode):?>
            <td valign="middle" nowrap="nowrap" class="list">
							<a href="disks_raid_gconcat_edit.php?id=<?=$i;?>"><img src="e.gif" title="<?=gettext("Edit RAID"); ?>" border="0" alt="<?=gettext("Edit RAID"); ?>" /></a>&nbsp;
							<a href="disks_raid_gconcat.php?act=del&amp;id=<?=$i;?>" onclick="return confirm('<?=gettext("Do you really want to delete this volume?\\n!!! Note, all data will get lost and can not be recovered. !!!") ;?>')"><img src="x.gif" title="<?=gettext("Delete RAID") ;?>" border="0" alt="<?=gettext("Delete RAID") ;?>" /></a>
						</td>
						<?php else:?>
						<td valign="middle" nowrap="nowrap" class="list">
							<img src="del.gif" border="0" alt="" />
						</td>
						<?php endif;?>
					</tr>
					<?php $i++; endforeach;?>
          <tr>
            <td class="list" colspan="4"></td>
            <td class="list"> <a href="disks_raid_gconcat_edit.php"><img src="plus.gif" title="<?=gettext("Add RAID");?>" border="0" alt="<?=gettext("Add RAID");?>" /></a></td>
					</tr>
        </table>
        <div id="remarks">
        	<?php html_remark("info", gettext("Info"), sprintf(gettext("%s is used to create %s volumes."), "GEOM Concat", "JBOD"));?>
        </div>
        <?php include("formend.inc");?>
      </form>
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
