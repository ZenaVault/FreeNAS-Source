#!/usr/local/bin/php
<?php 
/*
	disks_manage_edit.php
	part of FreeNAS (http://www.freenas.org)
	Copyright (C) 2005-2007 Olivier Cochard-Labb� <olivier@freenas.org>.
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

$pgtitle = array(gettext("Disks"),gettext("Disk"),isset($id)?gettext("Edit"):gettext("Add"));

/* get disk list (without CDROM) */
//$disklist = get_physical_disks_list();

/* get All disk list (with CDROM) */
$disklist = array_merge((array)get_physical_disks_list(),(array)get_cdrom_list());

if (!is_array($config['disks']['disk']))
	$config['disks']['disk'] = array();

disks_sort();

$a_disk = &$config['disks']['disk'];

if (isset($id) && $a_disk[$id]) {
	$pconfig['name'] = $a_disk[$id]['name'];
	$pconfig['harddiskstandby'] = $a_disk[$id]['harddiskstandby'];
	$pconfig['acoustic'] = $a_disk[$id]['acoustic'];
	$pconfig['fstype'] = $a_disk[$id]['fstype'];
	$pconfig['apm'] = $a_disk[$id]['apm'];
	$pconfig['udma'] = $a_disk[$id]['udma'];
	$pconfig['fullname'] = $a_disk[$id]['fullname'];
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;
		
	/* check for name conflicts */
	foreach ($a_disk as $disk) {
		if (isset($id) && ($a_disk[$id]) && ($a_disk[$id] === $disk))
			continue;

		if ($disk['name'] == $_POST['name']) {
			$input_errors[] = gettext("This disk already exists in the disk list.");
			break;
		}
	}

	if (!$input_errors) {
		$disks = array();
		
		$devname = $_POST['name'];
		$devharddiskstandby = $_POST['harddiskstandby'];
		$harddiskacoustic = $_POST['acoustic'];
		$harddiskapm  = $_POST['apm'];
		$harddiskudma  = $_POST['udma'];
		$harddiskfstype = $_POST['fstype'];
		
		$disks['name'] = $devname;
		$disks['fullname'] = "/dev/$devname";
		$disks['harddiskstandby'] = $devharddiskstandby ;
		$disks['acoustic'] = $harddiskacoustic ;
		if ($harddiskfstype) $disks['fstype'] = $harddiskfstype ;
		$disks['apm'] = $harddiskapm ;
		$disks['udma'] = $harddiskudma ;
		$disks['type'] = $disklist[$devname]['type'];
		$disks['desc'] = $disklist[$devname]['desc'];
		$disks['size'] = $disklist[$devname]['size'];

		if (isset($id) && $a_disk[$id])
			$a_disk[$id] = $disks;
		else
			$a_disk[] = $disks;

		touch($d_diskdirty_path);

		write_config();
		rc_exec_service("ataidle");

		header("Location: disks_manage.php");
		exit;
	}
}
?>
<?php include("fbegin.inc"); ?>
<?php if ($input_errors) print_input_errors($input_errors); ?>
<form action="disks_manage_edit.php" method="post" name="iform" id="iform">
	<table width="100%" border="0" cellpadding="6" cellspacing="0">
		<tr>
			<td width="22%" valign="top" class="vncellreq"><?=gettext("Disk"); ?></td>
			<td width="78%" class="vtable">
				<select name="name" class="formfld" id="name">
				  <?php foreach ($disklist as $diski => $diskv): ?>
				  <option value="<?=$diski;?>" <?php if ($diski == $pconfig['name']) echo "selected";?>><?php echo htmlspecialchars($diski . ": " .$diskv['size'] . " (" . $diskv['desc'] . ")");?></option>
		  		<?php endforeach; ?>
		  	</select>
		  </td>
		</tr>
		<tr> 
			<td width="22%" valign="top" class="vncell"><?=gettext("UDMA mode"); ?></td>
			<td width="78%" class="vtable">
				<select name="udma" class="formfld" id="udma">
				<?php $types = explode(",", "Auto,UDMA-33,UDMA-66,UDMA-100,UDMA-133"); $vals = explode(" ", "auto UDMA2 UDMA4 UDMA5 UDMA6");
				$j = 0; for ($j = 0; $j < count($vals); $j++): ?>
					<option value="<?=$vals[$j];?>" <?php if ($vals[$j] == $pconfig['udma']) echo "selected";?>><?=htmlspecialchars($types[$j]);?></option>
				<?php endfor; ?>
				</select>
				<br>
				<?=gettext("You can force UDMA mode if you have 'UDMA_ERROR.... LBA' message with your hard drive."); ?>
			</td>
		</tr>
		<tr> 
			<td width="22%" valign="top" class="vncell"><?=gettext("Hard disk standby time"); ?></td>
			<td width="78%" class="vtable"> 
				<select name="harddiskstandby" class="formfld">
				<?php $sbvals = array(0=>gettext("Always on"), 5=>"5 ".gettext("minutes"), 10=>"10 ".gettext("minutes"), 20=>"20 ".gettext("minutes"), 30=>"30 ".gettext("minutes"), 60=>"60 ".gettext("minutes"));?>
				<?php foreach ($sbvals as $sbval => $sbname): ?>
					<option value="<?=$sbval;?>" <?php if($pconfig['harddiskstandby'] == $sbval) echo 'selected';?>><?=htmlspecialchars($sbname);?></option>
				<?php endforeach; ?>
				</select>
				<br>
				<?=gettext("Puts the hard disk into standby mode when the selected amount of time after the last access has elapsed. <em>Do not set this for CF cards.</em>") ;?>
			</td>
		</tr>
		<tr> 
			<td width="22%" valign="top" class="vncell"><?=gettext("Advanced Power Management"); ?></td>
			<td width="78%" class="vtable"> 
				<select name="apm" class="formfld">
				<?php $apmvals = array(0=>gettext("Disabled"),1=>gettext("Minimum power usage with Standby"),64=>gettext("Medium power usage with Standby"),128=>gettext("Minimum power usage without Standby"),192=>gettext("Medium power usage without Standby"),254=>gettext("Maximum performance, maximum power usage"));?>
				<?php foreach ($apmvals as $apmval => $apmname): ?>
					<option value="<?=$apmval;?>" <?php if($pconfig['apm'] == $apmval) echo 'selected';?>><?=htmlspecialchars($apmname);?></option>
				<?php endforeach; ?>
				</select>
				<br>
				<?=gettext("This allows  you  to lower the power consumption of the drive, at the expense of performance.<br><em>Do not set this for CF cards.</em>"); ?>
			</td>
		</tr>
		<tr> 
			<td width="22%" valign="top" class="vncell"><?=gettext("Acoustic level"); ?></td>
			<td width="78%" class="vtable"> 
				<select name="acoustic" class="formfld">
				<?php $acvals = array(0=>gettext("Disabled"),1=>gettext("Minimum performance, Minimum acoustic output"),64=>gettext("Medium acoustic output"),127=>gettext("Maximum performance, maximum acoustic output"));?>
				<?php foreach ($acvals as $acval => $acname): ?>
					<option value="<?=$acval;?>" <?php if($pconfig['acoustic'] == $acval) echo 'selected';?>><?=htmlspecialchars($acname);?></option>
				<?php endforeach; ?>
				</select>
				<br>
				<?=gettext("This allows you to set how loud the drive is while it's operating.<br><em>Do not set this for CF cards.</em>"); ?>
			</td>
		</tr>
		<tr> 
			<td width="22%" valign="top" class="vncell"><?=gettext("Preformatted FS"); ?></td>
			<td width="78%" class="vtable"> 
				<select name="fstype" class="formfld">
				<?php $fstlist = get_fstype_list(); ?>
				<?php foreach ($fstlist as $fstval => $fstname): ?>
					<option value="<?=$fstval;?>" <?php if($pconfig['fstype'] == $fstval) echo 'selected';?>><?=gettext($fstname);?></option>
				<?php endforeach; ?>
				</select>
				<br>
				<?php echo sprintf( gettext("This allows you to set FS type for preformated disk with data.<br><em>Leave 'unformated' for unformated disk and then use <a href=%s>format menu</a>.</em>"), "disks_init.php"); ?>
			</td>
		</tr>
		<tr> 
			<td width="22%" valign="top">&nbsp;</td>
			<td width="78%"> <input name="Submit" type="submit" class="formbtn" value="<?=((isset($id) && $a_disk[$id]))?gettext("Save"):gettext("Add")?>"> 
			<?php if (isset($id) && $a_disk[$id]): ?>
				<input name="id" type="hidden" value="<?=$id;?>">
			<?php endif; ?>
			</td>
		</tr>
	</table>
</form>
<?php include("fend.inc"); ?>
