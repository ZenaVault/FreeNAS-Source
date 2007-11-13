#!/usr/local/bin/php
<?php
/*
	services_rsyncd_local_edit.php
	part of FreeNAS (http://www.freenas.org)
	Copyright (C) 2005-2007 Olivier Cochard-Labb� <olivier@freenas.org>.
	Improved by Mat Murdock <mmurdock@kimballequipment.com>.
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

$pgtitle = array(gettext("Services"),gettext("RSYNC"),gettext("Local"),isset($id)?gettext("Edit"):gettext("Add"));

/* Global arrays. */
$a_months = explode(" ",gettext("January February March April May June July August September October November December"));
$a_weekdays = explode(" ",gettext("Sunday Monday Tuesday Wednesday Thursday Friday Saturday"));
$a_mount = array();

if (!is_array($config['rsync'])) {
	$config['rsync'] = array();
	if (!is_array($config['rsync']['rsynclocal']))
		$config['rsync']['rsynclocal'] = array();
} else if (!is_array($config['rsync']['rsynclocal'])) {
	$config['rsync']['rsynclocal'] = array();
}

if (!is_array($config['mounts']['mount']))
	$config['mounts']['mount'] = array();

array_sort_key($config['mounts']['mount'], "devicespecialfile");

$a_mount = &$config['mounts']['mount'];
$a_rsynclocal = &$config['rsync']['rsynclocal'];

if (isset($id) && $a_rsynclocal[$id]) {
	$pconfig['opt_delete'] = isset($a_rsynclocal[$id]['opt_delete']);
	$pconfig['source'] = $a_rsynclocal[$id]['source'];
	$pconfig['destination'] = $a_rsynclocal[$id]['destination'];
	$pconfig['minute'] = $a_rsynclocal[$id]['minute'];
	$pconfig['hour'] = $a_rsynclocal[$id]['hour'];
	$pconfig['day'] = $a_rsynclocal[$id]['day'];
	$pconfig['month'] = $a_rsynclocal[$id]['month'];
	$pconfig['weekday'] = $a_rsynclocal[$id]['weekday'];
	$pconfig['sharetosync'] = $a_rsynclocal[$id]['sharetosync'];
	$pconfig['all_mins'] = $a_rsynclocal[$id]['all_mins'];
	$pconfig['all_hours'] = $a_rsynclocal[$id]['all_hours'];
	$pconfig['all_days'] = $a_rsynclocal[$id]['all_days'];
	$pconfig['all_months'] = $a_rsynclocal[$id]['all_months'];
	$pconfig['all_weekdays'] = $a_rsynclocal[$id]['all_weekdays'];
	$pconfig['description'] = $a_rsynclocal[$id]['description'];
} else {
	// Set default values.
	$pconfig['all_mins'] = 1;
	$pconfig['all_hours'] = 1;
	$pconfig['all_days'] = 1;
	$pconfig['all_months'] = 1;
	$pconfig['all_weekdays'] = 1;
}

if (!is_array($config['mounts']['mount'])) {
	$nodisk_errors[] = gettext("You must configure mount point first.");
} else {
	if ($_POST) {
		unset($input_errors);
		unset($errormsg);

		$pconfig = $_POST;

		/* input validation */
		$reqdfields = explode(" ", "source destination");
		$reqdfieldsn = array(gettext("Source share"),gettext("Destination share"));
		do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);

		if (!$input_errors) {
			$rsynclocal = array();

			$rsynclocal['opt_delete'] = $_POST['opt_delete'] ? true : false;;
			$rsynclocal['minute'] = $_POST['minute'];
			$rsynclocal['hour'] = $_POST['hour'];
			$rsynclocal['day'] = $_POST['day'];
			$rsynclocal['month'] = $_POST['month'];
			$rsynclocal['weekday'] = $_POST['weekday'];
			$rsynclocal['source'] = $_POST['source'];
			$rsynclocal['destination'] = $_POST['destination'];
			$rsynclocal['all_mins'] = $_POST['all_mins'];
			$rsynclocal['all_hours'] = $_POST['all_hours'];
			$rsynclocal['all_days'] = $_POST['all_days'];
			$rsynclocal['all_months'] = $_POST['all_months'];
			$rsynclocal['all_weekdays'] = $_POST['all_weekdays'];
			$rsynclocal['description'] = $_POST['description'];

			if (isset($id) && $a_rsynclocal[$id])
				$a_rsynclocal[$id] = $rsynclocal;
			else
				$a_rsynclocal[] = $rsynclocal;
			touch($d_rsynclocaldirty_path);

			write_config();

			header("Location: services_rsyncd_local.php");
			exit;
		}
	}
}
?>
<?php include("fbegin.inc"); ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="services_rsyncd.php"><?=gettext("Server") ;?></a></li>
				<li class="tabinact"><a href="services_rsyncd_client.php"><?=gettext("Client") ;?></a></li>
				<li class="tabact"><a href="services_rsyncd_local.php" style="color:black" title="<?=gettext("Reload page");?>"><?=gettext("Local") ;?></a></li>
			</ul>
		</td>
	</tr>
  <tr>
    <td class="tabcont">
			<form action="services_rsyncd_local_edit.php" method="post" name="iform" id="iform">
				<?php if ($input_errors) print_input_errors($input_errors); ?><table width="100%" border="0" cellpadding="0" cellspacing="0">
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<tr>
	          <td width="22%" valign="top" class="vncellreq"><?=gettext("Source share");?></td>
	          <td width="78%" class="vtable">
	            <select name="source" class="formfld" id="source">
	              <?php foreach ($a_mount as $mountv): ?>
	              <option value="<?=$mountv['sharename'];?>"<?php if ($mountv['sharename'] == $pconfig['source']) echo "selected";?>>
	              <?php echo htmlspecialchars($mountv['sharename'] . " (" . gettext("Disk") . ": " . $mountv['mdisk'] . " " . gettext("Partition") . ": " . $mountv['partition'] . ")");?>
	              </option>
	              <?php endforeach; ?>
	            </select>
	          </td>
	    		</tr>
					<tr>
	          <td width="22%" valign="top" class="vncellreq"><?=gettext("Destination share");?></td>
	          <td width="78%" class="vtable">
	            <select name="destination" class="formfld" id="destination">
	              <?php foreach ($a_mount as $mountv): ?>
	              <option value="<?=$mountv['sharename'];?>"<?php if ($mountv['sharename'] == $pconfig['destination']) echo "selected";?>>
	              <?php echo htmlspecialchars($mountv['sharename'] . " (" . gettext("Disk") . ": " . $mountv['mdisk'] . " " . gettext("Partition") . ": " . $mountv['partition'] . ")");?>
	              </option>
	              <?php endforeach; ?>
	            </select>
          	</td>
    			</tr>
    			<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gettext("Synchronization Time");?></td>
						<td width="78%" class="vtable">
							<table width=100% border cellpadding="6" cellspacing="0">
								<tr>
									<td class="optsect_t"><b class="optsect_s"><?=gettext("minutes");?></b></td>
									<td class="optsect_t"><b class="optsect_s"><?=gettext("hours");?></b></td>
									<td class="optsect_t"><b class="optsect_s"><?=gettext("days");?></b></td>
									<td class="optsect_t"><b class="optsect_s"><?=gettext("months");?></b></td>
									<td class="optsect_t"><b class="optsect_s"><?=gettext("week days");?></b></td>
								</tr>
								<tr bgcolor=#cccccc>
									<td valign=top>
										<input type="radio" name="all_mins" id="all_mins1" value="1" <?php if (1 == $pconfig['all_mins']) echo "checked";?>>
										<?=gettext("All");?><br>
										<input type="radio" name="all_mins" id="all_mins2" value="0" <?php if (1 != $pconfig['all_mins']) echo "checked";?>>
										<?=gettext("Selected");?> ..<br>
										<table>
											<tr>
												<td valign=top>
													<select multiple size="12" name="minute[]" id="minutes1">
														<?php for ($i = 0; $i <= 11; $i++):?>
														<option value="<?=$i;?>" <?php if (is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign=top>
													<select multiple size="12" name="minute[]" id="minutes2">
														<?php for ($i = 12; $i <= 23; $i++):?>
														<option value="<?=$i;?>" <?php if (is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign=top>
													<select multiple size="12" name="minute[]" id="minutes3">
														<?php for ($i = 24; $i <= 35; $i++):?>
														<option value="<?=$i;?>" <?php if (is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign=top>
													<select multiple size="12" name="minute[]" id="minutes4">
														<?php for ($i = 36; $i <= 47; $i++):?>
														<option value="<?=$i;?>" <?php if (is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign=top>
													<select multiple size="12" name="minute[]" id="minutes5">
														<?php for ($i = 48; $i <= 59; $i++):?>
														<option value="<?=$i;?>" <?php if (is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
											</tr>
										</table>
										<br>
									</td>
									<td valign=top>
										<input type="radio" name="all_hours" id="all_hours1" value="1" <?php if (1 == $pconfig['all_hours']) echo "checked";?>>
										<?=gettext("All");?><br>
										<input type="radio" name="all_hours" id="all_hours2" value="0" <?php if (1 != $pconfig['all_hours']) echo "checked";?>>
										<?=gettext("Selected");?> ..<br>
										<table>
											<tr>
												<td valign=top>
													<select multiple size="12" name="hour[]" id="hours1">
														<?php for ($i = 0; $i <= 11; $i++):?>
														<option value="<?=$i;?>" <?php if (is_array($pconfig['hour']) && in_array("$i", $pconfig['hour'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign=top>
													<select multiple size="12" name="hour[]" id="hours2">
														<?php for ($i = 12; $i <= 23; $i++):?>
														<option value="<?=$i;?>" <?php if (is_array($pconfig['hour']) && in_array("$i", $pconfig['hour'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
											</tr>
										</table>
									</td>
									<td valign=top>
										<input type="radio" name="all_days" id="all_days1" value="1" <?php if (1 == $pconfig['all_days']) echo "checked";?>>
										<?=gettext("All");?><br>
										<input type="radio" name="all_days" id="all_days2" value="0" <?php if (1 != $pconfig['all_days']) echo "checked";?>>
										<?=gettext("Selected");?> ..<br>
										<table>
											<tr>
												<td valign=top>
													<select multiple size="12" name="day[]" id="days1">
														<?php for ($i = 0; $i <= 12; $i++):?>
														<option value="<?=$i;?>" <?php if (is_array($pconfig['day']) && in_array("$i", $pconfig['day'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign=top>
													<select multiple size="12" name="day[]" id="days2">
														<?php for ($i = 13; $i <= 24; $i++):?>
														<option value="<?=$i;?>" <?php if (is_array($pconfig['day']) && in_array("$i", $pconfig['day'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign=top>
													<select multiple size="7" name="day[]" id="days3">
														<?php for ($i = 25; $i <= 31; $i++):?>
														<option value="<?=$i;?>" <?php if (is_array($pconfig['day']) && in_array("$i", $pconfig['day'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
											</tr>
										</table>
									</td>
									<td valign=top>
										<input type="radio" name="all_months" id="all_months1" value="1" <?php if (1 == $pconfig['all_months']) echo "checked";?>>
										<?=gettext("All");?><br>
										<input type="radio" name="all_months" id="all_months2" value="0" <?php if (1 != $pconfig['all_months']) echo "checked";?>>
										<?=gettext("Selected");?> ..<br>
										<table>
											<tr>
												<td valign=top>
													<select multiple size="12" name="month[]" id="months">
														<?php $i = 1; foreach ($a_months as $month):?>
														<option value="<?=$i;?>" <?php if (isset($pconfig['month']) && in_array("$i", $pconfig['month'])) echo "selected";?>><?=htmlspecialchars($month);?></option>
														<?php $i++; endforeach;?>
													</select>
												</td>
											</tr>
										</table>
									</td>
									<td valign=top>
										<input type="radio" name="all_weekdays" id="all_weekdays1" value="1" <?php if (1 == $pconfig['all_weekdays']) echo "checked";?>>
										<?=gettext("All");?><br>
										<input type="radio" name="all_weekdays" id="all_weekdays2" value="0" <?php if (1 != $pconfig['all_weekdays']) echo "checked";?>>
										<?=gettext("Selected");?> ..<br>
										<table>
											<tr>
												<td valign=top>
													<select multiple size="7" name="weekday[]" id="weekdays">
														<?php $i = 0; foreach ($a_weekdays as $day):?>
														<option value="<?=$i;?>" <?php if (isset($pconfig['weekday']) && in_array("$i", $pconfig['weekday'])) echo "selected";?>><?=$day;?></option>
														<?php $i++; endforeach;?>
													</select>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr bgcolor=#cccccc>
									<td colspan=5>
										<?=gettext("Note: Ctrl-click (or command-click on the Mac) to select and de-select minutes, hours, days and months.");?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gettext("RSYNC Options"); ?></td>
						<td width="78%" class="vtable">
							<input name="opt_delete" id="opt_delete" type="checkbox" value="yes" <?php if ($pconfig['opt_delete']) echo "checked"; ?>> <?=gettext("Delete files that don't exist on sender."); ?><br>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gettext("Description");?></td>
						<td width="78%" class="vtable">
							<input name="description" type="text" class="formfld" id="description" size="40" value="<?=htmlspecialchars($pconfig['description']);?>">
						</td>
					</tr>
					<tr>
            <td width="22%" valign="top">&nbsp;</td>
            <td width="78%">
              <input name="Submit" type="submit" class="formbtn" value="<?=gettext("Save");?>" onClick="enable_change(true)">
							<?php if (isset($id) && $a_rsynclocal[$id]) : ?>
							<input name="id" type="hidden" value="<?=$id;?>">
							<?php endif; ?>
            </td>
      		</tr>
        </table>
			</form>
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
