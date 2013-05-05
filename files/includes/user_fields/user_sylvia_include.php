<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2012 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_sylvia_include.php
| Author: Yodix (www.on-deck.eu)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

global $Sylvia;

if ($profile_method == "input") {
	
} elseif ($profile_method == "display") {
	include_once INFUSIONS."sylvia/sylvia_core.php";
	
	if ($Sylvia->settings['warnings_list_uf'] == 0) {
		echo "<tr>\n";
		echo "<td class='tbl1'>".$locale['uf_sylvia']."</td>\n";
		echo "<td align='right' class='tbl1'>".($user_data['user_level'] == 101 ? $Sylvia->GetWarnsStatus($user_data['user_id'], TRUE)."&nbsp;[<a href='".INFUSIONS."sylvia/sylvia.php?page=warnings&amp;user_id=".$user_data['user_id']."' title='".$locale['uf_sylvia_list']."'>".$locale['uf_sylvia_list']."</a>]".(iSUPERADMIN || checkgroup($Sylvia->settings['warnings_moderator']) == TRUE ? "&nbsp;<a href='".INFUSIONS."sylvia/sylvia.php?page=management&amp;action=add&amp;user_id=".$user_data['user_id']."' title='".$Sylvia->locale['sylvia_fg_00_002']."'><img src='".INFUSIONS."sylvia/images/add.png' height='9' width='9' alt='".$Sylvia->locale['sylvia_fg_00_002']."' style='border:0;vertical-align:center' /></a>" : "") : $locale['uf_sylvia_admin'])."</td>\n";
		echo "</tr>\n";
	} elseif ($Sylvia->settings['warnings_list_uf'] == 1) {
		echo $Sylvia->RenderWarningsList($user_data['user_id']);
	}
} elseif ($profile_method == "validate_insert") {
	
} elseif ($profile_method == "validate_update") {
	
}
?>