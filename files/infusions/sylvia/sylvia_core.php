<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright © 2002 - 2012 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sylvia_core.php
| Version: 1.0.2
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

include_once INCLUDES."infusions_include.php";

include INFUSIONS."sylvia/infusion_db.php";
include INFUSIONS."sylvia/system/Sylvia.class.php";

$Sylvia = new Sylvia();

if (isset($Sylvia->settings['warning_system_enabled'])) {
	if ($Sylvia->settings['warning_system_enabled'] == 1 && !iADMIN && iMEMBER && $Sylvia->settings['warnings_ban_enabled'] == 1) {
		if ($Sylvia->settings['warnings_ban_time'] != 0 && (($Sylvia->settings['warnings_type'] == 1 && $Sylvia->GetWarnsStatus($userdata['user_id']) >= $Sylvia->settings['warnings_ban_number']) || ($Sylvia->settings['warnings_type'] == 2 && $Sylvia->GetWarnsStatus($userdata['user_id']) == 100))) {
			require_once INCLUDES."sendmail_include.php";
			require_once INCLUDES."suspend_include.php";
			
			$actiontime = ($Sylvia->settings['warnings_ban_time'] * 86400) + time();
			$result = dbquery("UPDATE ".DB_USERS." SET user_status='3', user_actiontime='".$actiontime."' WHERE user_id='".$userdata['user_id']."'");
			suspend_log($userdata['user_id'], 3, $Sylvia->locale['sylvia_core_00_001'], TRUE);
			sendemail($userdata['user_name'], $userdata['user_email'], $settings['siteusername'], $settings['siteemail'], $Sylvia->locale['sylvia_core_00_000'], $Sylvia->locale['sylvia_core_00_001']);
			$result2 = dbquery("DELETE FROM ".DB_SYLVIA_WARNS." WHERE warn_user='".$userdata['user_id']."'");
		} elseif ($Sylvia->settings['warnings_ban_time'] == 0 && (($Sylvia->settings['warnings_type'] == 1 && $Sylvia->GetWarnsStatus($userdata['user_id']) >= $Sylvia->settings['warnings_ban_number']) || ($Sylvia->settings['warnings_type'] == 2 && $Sylvia->GetWarnsStatus($userdata['user_id']) == 100))) {
			require_once INCLUDES."sendmail_include.php";
			require_once INCLUDES."suspend_include.php";
			
			$result = dbquery("UPDATE ".DB_USERS." SET user_status='1', user_actiontime='0' WHERE user_id='".$userdata['user_id']."'");
			suspend_log($userdata['user_id'], 1, $Sylvia->locale['sylvia_core_00_001'], TRUE);
			sendemail($userdata['user_name'], $userdata['user_email'], $settings['siteusername'], $settings['siteemail'], $Sylvia->locale['sylvia_core_00_000'], $Sylvia->locale['sylvia_core_00_001']);
			$result2 = dbquery("DELETE FROM ".DB_SYLVIA_WARNS." WHERE warn_user='".$userdata['user_id']."'");
		}
	}

	if (dbcount("(blockade_id)", DB_SYLVIA_BLOCKADES, "blockade_code='Fh1Vc6884Z9ejwz24d2K6fI6P' AND blockade_active='1'") == 1) {
		$Sylvia->InitBlockade("Fh1Vc6884Z9ejwz24d2K6fI6P");
		if ($Sylvia->LoadBlockade(TRUE) == TRUE) {
			$replace = "<textarea name='shout_message' rows='4' cols='20' class='textbox' disabled='disabled' style='width:140px'>".$Sylvia->LoadBlockade();
			$replaced = "<textarea name='shout_message' rows='4' cols='20' class='textbox' style='width:140px'>";
			replace_in_output(addslashes($replaced), addslashes($replace), "si");
			
			unset($_POST['post_shout'], $_POST['shout_message'], $replace, $replaced);
		}
		$Sylvia->DestructBlockade();
	}

	if (dbcount("(blockade_id)", DB_SYLVIA_BLOCKADES, "blockade_code='SEcH7uxPTl91PhPh91R485591' AND blockade_active='1'") == 1) {
		$Sylvia->InitBlockade("SEcH7uxPTl91PhPh91R485591");
		if ($Sylvia->LoadBlockade(TRUE) == TRUE) {
			$replace = "<textarea name='comment_message' cols='70' rows='6' class='textbox' disabled='disabled' style='width:360px'>".$Sylvia->LoadBlockade();
			$replaced = "<textarea name='comment_message' cols='70' rows='6' class='textbox' style='width:360px'>";
			replace_in_output(addslashes($replaced), addslashes($replace), "si");
			
			unset($_POST['post_comment'], $_POST['comment_message'], $replace, $replaced);
		}
		$Sylvia->DestructBlockade();
	}

	if (dbcount("(blockade_id)", DB_SYLVIA_BLOCKADES, "blockade_code='sk5PZ159l86ja3HQay195W2jF' AND blockade_active='1'") == 1) {
		include_once INFUSIONS."sylvia/system/GetCurrentDir.func.php";
		
		$Sylvia->InitBlockade("sk5PZ159l86ja3HQay195W2jF");
		if (GetCurrentDir(TRUE)) {
			$current_dir = GetCurrentDir();
		} else {
			$current_dir = "forum";
		}
		if (($current_dir == "forum" && FUSION_SELF == "post.php") && $Sylvia->LoadBlockade(TRUE) == TRUE) {
			redirect(INFUSIONS."sylvia/blockade.php?key=sk5PZ159l86ja3HQay195W2jF");
		}
		$Sylvia->DestructBlockade();
	}

	if ($Sylvia->settings['warning_system_enabled'] == 1 && ($Sylvia->settings['warnings_forum_messages'] == 1 || $Sylvia->settings['warnings_forum_info'] == 1)) {
		include INFUSIONS."sylvia/system/SylviaForumGenerator.class.php";
		
		$SylviaForumGenerator = new SylviaForumGenerator($Sylvia);

		function sylvia_forum_generator ($initializer) {
			global $SylviaForumGenerator;

			$SylviaForumGenerator->setInitializer($initializer);
			return $SylviaForumGenerator->Execute();
		}

		add_handler("sylvia_forum_generator");
	}
}
?>