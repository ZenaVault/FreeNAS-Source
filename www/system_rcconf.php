#!/usr/local/bin/php
<?php
/*
	system_rcconf.php
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

$pgtitle = array(gettext("System"), gettext("Advanced"), gettext("rc.conf"));

if (!is_array($config['system']['rcconf']['param']))
	$config['system']['rcconf']['param'] = array();

array_sort_key($config['system']['rcconf']['param'], "name");

$a_rcvar = &$config['system']['rcconf']['param'];

if ($_POST) {
	if ($_POST['apply']) {
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_exec_service("rcconf.sh");
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
		if ($retval == 0) {
			if (file_exists($d_rcconfdirty_path))
				unlink($d_rcconfdirty_path);
		}
	}
}

if ($_GET['act'] === "del") {
	if ($_GET['id'] === "all") {
		foreach ($a_rcvar as $rcvark => $rcvarv) {
			mwexec("/usr/local/sbin/rconf attribute remove {$a_rcvar[$rcvark]['name']}");
			unset($a_rcvar[$rcvark]);
		}
		write_config();
		touch($d_rcconfdirty_path);
		header("Location: system_rcconf.php");
		exit;
	} else {
		if ($a_rcvar[$_GET['id']]) {
			mwexec("/usr/local/sbin/rconf attribute remove {$a_rcvar[$_GET['id']]['name']}");
			unset($a_rcvar[$_GET['id']]);
			write_config();
			touch($d_rcconfdirty_path);
			header("Location: system_rcconf.php");
			exit;
		}
	}
}
?>
<?php include("fbegin.inc");?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
    <td class="tabnavtbl">
      <ul id="tabnav">
      	<li class="tabinact"><a href="system_advanced.php"><?=gettext("Advanced");?></a></li>
      	<li class="tabinact"><a href="system_email.php"><?=gettext("Email");?></a></li>
      	<li class="tabinact"><a href="system_proxy.php"><?=gettext("Proxy");?></a></li>
      	<li class="tabinact"><a href="system_swap.php"><?=gettext("Swap");?></a></li>
        <li class="tabinact"><a href="system_rc.php"><?=gettext("Command scripts");?></a></li>
        <li class="tabinact"><a href="system_cron.php"><?=gettext("Cron");?></a></li>
        <li class="tabact"><a href="system_rcconf.php" title="<?=gettext("Reload page");?>"><?=gettext("rc.conf");?></a></li>
        <li class="tabinact"><a href="system_sysctl.php"><?=gettext("sysctl.conf");?></a></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td class="tabcont">
    	<form action="system_rcconf.php" method="post">
    		<?php if ($savemsg) print_info_box($savemsg);?>
	    	<?php if (file_exists($d_rcconfdirty_path)) print_config_change_box();?>
	      <table width="100%" border="0" cellpadding="0" cellspacing="0">
	        <tr>
	          <td width="40%" class="listhdrr"><?=gettext("Variable");?></td>
	          <td width="20%" class="listhdrr"><?=gettext("Value");?></td>
	          <td width="30%" class="listhdrr"><?=gettext("Comment");?></td>
	          <td width="10%" class="list"></td>
	        </tr>
				  <?php $i = 0; foreach($a_rcvar as $rcvarv):?>
	        <tr>
	          <td class="listlr"><?=htmlspecialchars($rcvarv['name']);?>&nbsp;</td>
	          <td class="listr"><?=htmlspecialchars($rcvarv['value']);?>&nbsp;</td>
	          <td class="listr"><?=htmlspecialchars($rcvarv['comment']);?>&nbsp;</td>
	          <td valign="middle" nowrap class="list">
	            <a href="system_rcconf_edit.php?id=<?=$i;?>"><img src="e.gif" title="<?=gettext("Edit option");?>" width="17" height="17" border="0"></a>
	            <a href="system_rcconf.php?act=del&id=<?=$i;?>" onclick="return confirm('<?=gettext("Do you really want to delete this option?");?>')"><img src="x.gif" title="<?=gettext("Delete option");?>" width="17" height="17" border="0"></a>
	          </td>
	        </tr>
	        <?php $i++; endforeach;?>
	        <tr>
	          <td class="list" colspan="3"></td>
	          <td class="list"><a href="system_rcconf_edit.php"><img src="plus.gif" title="<?=gettext("Add option");?>" width="17" height="17" border="0"></a>
	          <?php if (!empty($a_rcvar)):?>
						<a href="system_rcconf.php?act=del&id=all" onclick="return confirm('<?=gettext("Do you really want to delete all options?");?>')"><img src="x.gif" title="<?=gettext("Delete all options");?>" width="17" height="17" border="0"></a>
						<?php endif;?>
						</td>
	        </tr>
	      </table>
	      <p>
					<span class="vexpl">
						<span class="red"><strong><?=gettext("Note");?>:</strong></span><br/>
						<?php echo gettext("These option(s) will be added to /etc/rc.conf. This allow you to overwrite options used by various generic startup scripts.");?>
					</span>
				</p>
			</form>
	  </td>
  </tr>
</table>
<?php include("fend.inc");?>