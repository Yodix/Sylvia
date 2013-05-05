<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright © 2002 - 2012 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Sylvia.class.php
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

class Sylvia {
	public $enabled = FALSE;
	public $settings = array();
	public $locale = array();
	
	public $px_version = FALSE;
	
	private $px_connect = FALSE;
	private $PointX;
	
	private $isset_error = FALSE;
	private $error_group = 0;
	
	private $isset_blockade = FALSE;
	private $blockade = array();
	private $html = "";
	
	private $isset_navi = FALSE;
	private $navi_content = "";
	
	const Version = "1.0.2";
	const AdminPages = "blockades,defined,settings";
	const SitePages = "warnings,management,forum_messages";
	
	public function __construct () {
		if (dbcount("(inf_id)", DB_INFUSIONS, "inf_folder='sylvia'") == 1) {
			include_once INCLUDES."infusions_include.php";
			
			$ifLocale = $this->GetLocale();
			if (!$ifLocale) $super_error = TRUE;
			
			if (str_replace(".", "", dbresult(dbquery("SELECT inf_version FROM ".DB_INFUSIONS." WHERE inf_folder='sylvia'"), 0)) < str_replace(".", "", Sylvia::Version)) {
				$this->Update();
			}
			
			$ifSettings = $this->GetSettings();
			if (!isset($this->settings['error_detector_rand'])) $this->settings['error_detector_rand'] = "huA71";
			
			Sylvia::ErrorDetector($ifSettings, FALSE, sprintf($this->locale['sylvia_core_01_000'], $this->settings['error_detector_rand'], $this->locale['sylvia_core_01_002']), $this->locale['sylvia_core_01_003'].$this->locale['sylvia_core_01_001']);
			Sylvia::ErrorDetector($ifLocale, FALSE, sprintf($this->locale['sylvia_core_01_000'], $this->settings['error_detector_rand'], $this->locale['sylvia_core_01_004']), $this->locale['sylvia_core_01_005'].$this->locale['sylvia_core_01_001']);
			
			$this->ConnectPointX();
			$this->SetUserToAdmin();
			
			if ($this->settings['error_detector_rand'] == "") {
				set_setting("error_detector_rand", Sylvia::GenKey(), "sylvia");
			}
			
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	private function GetLocale () {
		global $settings;
		
		if (file_exists(INFUSIONS."sylvia/locale/".$settings['locale'].".php")) {
			include INFUSIONS."sylvia/locale/".$settings['locale'].".php";
			include INFUSIONS."sylvia/locale/".$settings['locale']."_errors.php";
			
			if (isset($locale)) {
				$this->locale = $locale;
				
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			include INFUSIONS."sylvia/locale/English.php";
			include INFUSIONS."sylvia/locale/English_errors.php";
			
			if (isset($locale)) {
				$this->locale = $locale;
			}
			
			return FALSE;
		}
	}
	
	private function GetSettings () {
		$result = dbquery("SELECT settings_name, settings_value FROM ".DB_SETTINGS_INF." WHERE settings_inf='sylvia'");
		if (dbrows($result) != 0) {
			while ($data = dbarray($result)) {
				$this->settings[$data['settings_name']] = $data['settings_value'];
			}
			
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	private function Update ($get_settings = FALSE) {
		$result = dbquery("CREATE TABLE ".DB_SYLVIA_MESSAGES." (
		message_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		message_post MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		message_mod MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
		message_contents TEXT NOT NULL,
		message_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
		PRIMARY KEY (message_id),
		KEY message_datestamp (message_datestamp)
		) ENGINE=MYISAM;");
		
		if (!isset($this->settings['warnings_private_message'])) {
			$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warning_update_info', '0', 'sylvia')");
		}
		if (!isset($this->settings['warnings_only_defined'])) {
			$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warnings_forum_messages', '0', 'sylvia')");
		}
		if (!isset($this->settings['warnings_moderator'])) {
			$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warnings_list_uf', '0', 'sylvia')");
		}
		if (!isset($this->settings['warnings_moderator'])) {
			$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warnings_list_nof', '5', 'sylvia')");
		}
		if (!isset($this->settings['warnings_moderator'])) {
			$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('error_detector_rand', '', 'sylvia')");
		}
		
		$result = dbquery("ALTER TABLE ".DB_CAPTCHA." ADD warn_post_info TEXT NOT NULL AFTER warn_message");
		
		$versuin_update = dbquery("UPDATE ".DB_INFUSIONS." SET inf_version='".Sylvia::Version."' WHERE inf_folder='sylvia'");
	}
	
	private function ConnectPointX () {
		global $PointX;
		
		if (dbcount("(inf_id)", DB_INFUSIONS, "inf_folder='pointx'") == 1 && file_exists(INFUSIONS."pointx/system/PointX.class.php")) {
			if (str_replace(".", "", dbresult(dbquery("SELECT inf_version FROM ".DB_INFUSIONS." WHERE inf_folder='pointx'"), 0)) >= 202) {
				if ($this->settings['pointx_connected'] == 1) {
					$this->px_connect = TRUE;
					$this->PointX = $PointX;
					
					return TRUE;
				} else {
					$this->px_connect = FALSE;
					return FALSE;
				}
			} else {
				$this->px_version = TRUE;
				
				if ($this->settings['pointx_connected'] == 1) {
					$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='0' WHERE settings_name='pointx_connected' AND settings_inf='sylvia'");
					$this->px_connect = FALSE;
					return FALSE;
				} else {
					$this->px_connect = FALSE;
					return FALSE;
				}
			}
		} else {
			if ($this->settings['pointx_connected'] == 1) {
				$result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='0' WHERE settings_name='pointx_connected' AND settings_inf='sylvia'");
				$this->px_connect = FALSE;
				return FALSE;
			} else {
				$this->px_connect = FALSE;
				return FALSE;
			}
		}
	}
	
	private function SetUserToAdmin () {
		global $userdata;
		
		if (iADMIN) {
			$result = dbquery("DELETE FROM ".DB_SYLVIA_WARNS." WHERE warn_user='".$userdata['user_id']."'");
			return $result ? TRUE : FALSE;
		} else {
			return TRUE;
		}
	}
	
	/* Funkcje Specjalne */
	
	public static function GenKey ($length = 5) {
		$chars = array("abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ", "123456789");
		$count = array((strlen($chars[0]) - 1), (strlen($chars[1]) - 1));
		$key = "";
		for ($i = 0; $i < $length; $i++) {
			$type = mt_rand(0, 1);
			$key .= substr($chars[$type], mt_rand(0, $count[$type]), 1);
		}
		if (dbcount("(blockade_id)", DB_SYLVIA_BLOCKADES, "blockade_code='".$key."'") != 0) {
			return PointX::GenKey($length);
		} else {
			return $key;
		}
	}
	
	public static function ErrorDetector ($value, $error_value, $title, $message) {
		$check = dbcount("(message_id)", DB_MESSAGES, "message_subject='".$title."'") == 0 ? FALSE : TRUE;
		if (!$check && $value == $error_value) {
			$result = dbquery("SELECT user_id FROM ".DB_USERS." WHERE user_level='103'");
			while ($data = dbarray($result)) {
				send_pm($data['user_id'], ($data['user_id'] != 1 ? 1 : 2), $title, $message, "n");
			}
		}
	}
	
	/* Funckje dla Ostrze¿eñ */
	
	public function GetWarnsStatus ($user_id, $only_show = FALSE) {
		if ($this->settings['warnings_type'] == 1) {
			return dbcount("(warn_id)", DB_SYLVIA_WARNS, "warn_user='".$user_id."'");
		} elseif ($this->settings['warnings_type'] == 2) {
			$percent_warns = 0;
			$percent_result = dbquery("SELECT warn_data FROM ".DB_SYLVIA_WARNS." WHERE warn_user='".$user_id."'");
			if (dbrows($percent_result) != 0) {
				while ($percent_data = dbarray($percent_result)) {
					$percent_warns = $percent_warns+$percent_data['warn_data'];
				}
				
				if ($percent_warns > 100) {
					return "100".($only_show ? "%" : "");
				} else {
					return $percent_warns.($only_show ? "%" : "");
				}
			} else {
				return $percent_warns.($only_show ? "%" : "");
			}
		}
	}
	
	public function RenderWarningsList ($user_id = FALSE) {
		global $userdata;
		
		if (!$user_id) $user_id = $userdata['user_id'];
		$result = dbquery("SELECT warn_moderator, warn_contents, warn_points, warn_data, warn_datestamp FROM ".DB_SYLVIA_WARNS." WHERE warn_user='".$user_id."' ORDER BY warn_datestamp DESC LIMIT 0,".$this->settings['warnings_list_nof']);
		if (dbrows($result) != 0) {
			$return = "<tr>\n";
			$return .= "<td width='1%' class='tbl1' colspan='2'><table width='100%' cellpadding='0' cellspacing='0' border='0'>\n";
			while ($data = dbarray($result)) {
				$mod_result = dbquery("SELECT user_id, user_name, user_status FROM ".DB_USERS." WHERE user_id='".$data['warn_moderator']."'");
				if (dbrows($mod_result) != 0) {
					$mod_data = dbarray($mod_result);
					$ModLink = profile_link($mod_data['user_id'], $mod_data['user_name'], $mod_data['user_status']);
				} else {
					$ModLink = $this->locale['sylvia_core_00_003'];
				}
				
				$return .= "<tr><td class='small'>".sprintf($this->locale['sylvia_core_00_002'], $ModLink)."</td><td class='small' style='text-align:right;'>".showdate("%d %B %Y %H:%M", $data['warn_datestamp'])."<br />".($this->settings['warnings_type'] == 2 ? "<strong>".$this->locale['sylvia_core_00_004']."</strong>&nbsp;".$data['warn_data']."%&nbsp;" : "").($this->settings['pointx_connected'] == 1 ? "<strong>".$this->locale['sylvia_core_00_005']."</strong>&nbsp;".$data['warn_points'] : "")."</td></tr>\n";
				$return .= "<tr><td colspan='2'>".nl2br(parsesmileys(parseubb($data['warn_contents'])))."</td></tr>\n";
				
			}
			if (iSUPERADMIN || checkgroup($Sylvia->settings['warnings_moderator']) == TRUE) {
				$return .= "<tr><td style='font-size:10px;'><a href='".INFUSIONS."sylvia/sylvia.php?page=warnings&amp;user_id=".$user_id."' title='".$this->locale['sylvia_fg_00_008']."'>".$this->locale['sylvia_fg_00_008']."</a></td><td style='font-size:10px;' style='text-align:right;'><a href='".INFUSIONS."sylvia/sylvia.php?page=management&amp;action=add&amp;user_id=".$user_id."' title='".$this->locale['sylvia_fg_00_002']."'><img src='".INFUSIONS."sylvia/images/add.png' height='9' width='9' alt='".$this->locale['sylvia_fg_00_002']."' style='border:0;' align='right' /></a></td></tr>\n";
			} else {
				$return .= "<tr><td colspan='2' style='font-size:10px;'><a href='".INFUSIONS."sylvia/sylvia.php?page=warnings&amp;user_id=".$user_id."' title='".$this->locale['sylvia_fg_00_008']."'>".$this->locale['sylvia_fg_00_008']."</a></tr>\n";
			}
			$return .= "</table>\n</td>\n</tr>\n";
			
			return $return;
		} else {
			return "";
		}
		
	}
	
	/* Funkcje dla Blokad */
	
	public function InitBlockade ($key) {
		$result = dbquery("SELECT blockade_text, blockade_data FROM ".DB_SYLVIA_BLOCKADES." WHERE blockade_code='".$key."' AND blockade_active='1'");
		if (dbrows($result) != 0) {
			$data = dbarray($result);
			
			$this->blockade = array(
				"text" => $data['blockade_text'],
				"data" => $data['blockade_data']
			);
			$this->isset_blockade = TRUE;
		} else {
			$this->isset_blockade = FALSE;
			$this->blockade = array();
		}
	}
	
	public function InsertCode ($html) {
		if ($this->isset_blockade) {
			$this->html .= $html;
		} else {
			$this->html = "";
		}
	}
	
	public function LoadBlockade ($logic = FALSE) {
		global $userdata;
		
		if (iMEMBER && $this->isset_blockade) {
			$user_warns = $this->GetWarnsStatus($userdata['user_id']);
			if ($user_warns >= $this->blockade['data']) {
				return $logic ? TRUE : $this->blockade['text'];
			} else {
				return $logic ? FALSE : $this->html;
			}
		} else {
			return $logic ? FALSE :$this->html;
		}
	}
	
	public function ClearCode () {
		$this->html = "";
	}
	
	public function DestructBlockade () {
		$this->isset_blockade = FALSE;
		$this->blockade = array();
		$this->html = "";
		
		return TRUE;
	}
	
	/* Wiadomoœæ od Moderatora */
	
	public function AddModMessage ($post_id, $mod_id, $message) {
		$result = dbquery("INSERT INTO ".DB_SYLVIA_MESSAGES." (message_post, message_mod, message_contents, message_datestamp) VALUES ('".$post_id."', '".$mod_id."', '".$message."', '".time()."')");
		return $result ? TRUE : FALSE;
	}
	
	public static function ForumPostExists ($post_id) {
		return dbcount("(post_id)", DB_POSTS, "post_id='".$post_id."'") == 1 ? TRUE : FALSE;
	}
	
	/* Nawigacja */
	
	public function InitNavi () {
		if ($this->isset_navi == FALSE && empty($this->navi_content)) {
			$this->isset_navi = TRUE;
			
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	public function GenNavi ($html) {
		$this->navi_content .= $html;
	}
	
	public function RenderNavi () {
		if ($this->isset_navi == TRUE && !empty($this->navi_content)) {
			return $this->navi_content;
		} else {
			return FALSE;
		}
	}
	
	public function DestructNavi () {
		$this->isset_navi = FALSE;
		$this->navi_content = "";
		
		return TRUE;
	}
	
	/* Wiadomoœci i B³êdy */
	
	public function InitError ($group) {
		$array = array(1, 2, 3);
		
		if ($this->isset_error == FALSE && $this->error_group == 0 && in_array($group, $array)) {
			$this->isset_error = TRUE;
			$this->error_group = $group;
			
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	public function GetError ($message) {
		if ($this->isset_error == TRUE && $this->error_group == 1) {
			$group = "sylvia_error_01";
		} elseif ($this->isset_error == TRUE && $this->error_group == 2) {
			$group = "sylvia_error_02";
		} elseif ($this->isset_error == TRUE && $this->error_group == 3) {
			$group = "sylvia_error_03";
		} else {
			$group = "sylvia_error_00";
		}
		
		if (isset($this->locale[$group][$message])) {
			return $this->locale[$group][$message];
		} else {
			return $this->locale['sylvia_error_00']['locale_error'];
		}
	}
	
	public function DestructError () {
		$this->isset_error = FALSE;
		$this->error_group = 0;
		
		return TRUE;
	}
}
?>