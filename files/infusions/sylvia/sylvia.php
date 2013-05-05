<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright © 2002 - 2012 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sylvia.php
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
require_once "../../maincore.php";
require_once THEMES."templates/header.php";
require_once INFUSIONS."sylvia/sylvia_core.php";

if (!isset($_GET['page']) || !in_array($_GET['page'], explode(",", Sylvia::SitePages))) { $_GET['page'] = "warnings"; }
if ($settings['hide_userprofiles'] == 0) {
	if (!iMEMBER && (!isset($_GET['user_id']) || !isnum($_GET['user_id']) || dbcount("(user_id)", DB_USERS, "user_id='".$_GET['user_id']."'") == 0)) { redirect(BASEDIR."index.php"); }
} elseif ($settings['hide_userprofiles'] == 1) {
	if (!iMEMBER) { redirect(BASEDIR."index.php"); }
}

if (isset($_GET['page']) && $_GET['page'] == "warnings") {
	add_to_head("
		<script src='".INFUSIONS."sylvia/facebox/jquery-1.2.2.pack.js' type='text/javascript'></script>
		<link href='".INFUSIONS."sylvia/facebox/facebox.css' media='screen' rel='stylesheet' type='text/css' />
		<script src='".INFUSIONS."sylvia/facebox/facebox.js' type='text/javascript'></script>

		<script type='text/javascript'>
			jQuery(document).ready(function($) {
			  $('a[rel*=facebox]').facebox() 
			})
		</script>
	");
	
	if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }
	
	if (isset($_GET['user_id']) && isnum($_GET['user_id'])) {
		$user_result = dbquery("SELECT user_id, user_name, user_level, user_status FROM ".DB_USERS." WHERE user_id='".$_GET['user_id']."'");
		if (dbrows($user_result) != 0) {
			$user_data = dbarray($user_result);
			$CurrentUserTrue = FALSE;
		} else {
			$user_data = $userdata;
			$CurrentUserTrue = TRUE;
		}
	} else {
		$user_data = $userdata;
		$CurrentUserTrue = TRUE;
	}
	
	opentable($Sylvia->locale['sylvia_page_00_007'].((isset($userdata['user_id']) ? $userdata['user_id'] : 0) == $user_data['user_id'] ? "" : sprintf($Sylvia->locale['sylvia_page_00_008'], $user_data['user_name'])));
	if ((isset($userdata['user_id']) ? $userdata['user_id'] : 0) == $user_data['user_id']) echo "<div style='font-weight:bold;text-align:center;font-size:16px;margin:5px;'>".$Sylvia->locale['sylvia_page_04_010']."</div>\n";
	echo "<table width='85%' cellspacing='1' cellpadding='0' class='tbl-border center'>\n";
	$result = dbquery("SELECT warn_id, warn_moderator, warn_contents, warn_points, warn_data, warn_datestamp FROM ".DB_SYLVIA_WARNS." WHERE warn_user='".$user_data['user_id']."' ORDER BY warn_datestamp DESC LIMIT ".$_GET['rowstart'].",10");
	$rows = dbcount("(warn_id)", DB_SYLVIA_WARNS, "warn_user='".$user_data['user_id']."'");
	if ($user_data['user_level'] == 101) {
		if (dbrows($result) != 0) {
			$i = 0;
			echo "<tr>\n";
			echo "<td align='center' width='1%' class='tbl2' style='font-weight:bold;white-space:nowrap'>".$Sylvia->locale['sylvia_page_04_004']."</td>\n";
			echo "<td class='tbl2' style='font-weight:bold;'>".$Sylvia->locale['sylvia_page_04_000']."</td>\n";
			echo "<td align='center' width='1%' class='tbl2' style='font-weight:bold;white-space:nowrap'>".$Sylvia->locale['sylvia_page_04_001']."</td>\n";
			if ($Sylvia->settings['warnings_type'] == 2) {
				echo "<td align='center' width='1%' class='tbl2' style='font-weight:bold;white-space:nowrap'>".$Sylvia->locale['sylvia_page_04_002']."</td>\n";
			}
			if ($Sylvia->settings['pointx_connected'] == 1) {
				echo "<td align='center' width='1%' class='tbl2' style='font-weight:bold;white-space:nowrap'>".$Sylvia->locale['sylvia_page_04_003']."</td>\n";
			}
			if (iSUPERADMIN || checkgroup($Sylvia->settings['warnings_moderator']) == TRUE) {
				echo "<td align='center' width='1%' class='tbl2' style='font-weight:bold;white-space:nowrap'>".$Sylvia->locale['sylvia_page_04_005']."</td>\n";
			}
			echo "</tr>\n";
			while ($data = dbarray($result)) {
				$cell_color = ($i % 2 == 0 ? "tbl1" : "tbl2");
				$mod_result = dbquery("SELECT user_id, user_name, user_status FROM ".DB_USERS." WHERE user_id='".$data['warn_moderator']."'");
				if (dbrows($mod_result) != 0) {
					$mod_data = dbarray($mod_result);
					$ModData = profile_link($mod_data['user_id'], $mod_data['user_name'], $mod_data['user_status']);
				} else {
					$ModData = $Sylvia->locale['sylvia_page_04_008'];
				}
				echo "<tr>\n";
				echo "<td align='center' width='1%' class='".$cell_color."' style='white-space:nowrap'>".$ModData."</td>\n";
				echo "<td class='".$cell_color."'>".(strlen($data['warn_contents']) > 50 ? "<a href='#mydiv_".$data['warn_id']."' rel='facebox'>".trimlink(strip_tags(nl2br(parseubb($data['warn_contents']))), 50)."</a>" : nl2br(parseubb($data['warn_contents'])));
				if (strlen($data['warn_contents']) > 50) {
					echo "<div id='mydiv_".$data['warn_id']."' style='display:none'>".nl2br(parseubb($data['warn_contents']))."</div>";
				}
				echo "</td>\n";
				echo "<td align='center' width='1%' class='".$cell_color."' style='white-space:nowrap'>".showdate("forumdate", $data['warn_datestamp'])."</td>\n";
				if ($Sylvia->settings['warnings_type'] == 2) {
					echo "<td align='center' width='1%' class='".$cell_color."' style='white-space:nowrap'>".$data['warn_data']."%</td>\n";
				}
				if ($Sylvia->settings['pointx_connected'] == 1) {
					echo "<td align='center' width='1%' class='".$cell_color."' style='white-space:nowrap'>".$data['warn_points']."</td>\n";
				}
				if (iSUPERADMIN || checkgroup($Sylvia->settings['warnings_moderator']) == TRUE) {
					echo "<td align='center' width='1%' class='".$cell_color."' style='white-space:nowrap'>\n";
					echo "<a href='".FUSION_SELF."?page=management&amp;action=edit&amp;warn_id=".$data['warn_id']."'>".$Sylvia->locale['sylvia_page_04_006']."</a> -\n";
					echo "<a href='".FUSION_SELF."?page=management&amp;action=delete&amp;warn_id=".$data['warn_id']."'>".$Sylvia->locale['sylvia_page_04_007']."</a>\n";
					echo "</td>\n";
				}
				echo "</tr>\n";
				$i++;
			}
			echo "</table>\n";
		} else {
			if ((isset($userdata['user_id']) ? $userdata['user_id'] : 0) == $user_data['user_id']) {
				echo "<tr><td align='center' class='tbl1'>".$Sylvia->locale['sylvia_page_04_012']."</td></tr>\n</table>\n";
			} else {
				echo "<tr><td align='center' class='tbl1'>".$Sylvia->locale['sylvia_page_04_009']."</td></tr>\n</table>\n";
			}
		}
	} else {
		echo "<tr><td align='center' class='tbl1'>".$Sylvia->locale['sylvia_page_04_011']."</td></tr>\n</table>\n";
	}
	if ($rows > 10) echo "<div align='center' style=';margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 10, $rows, 3, "?page=warnings&amp;".(isset($_GET['user_id']) && isnum($_GET['user_id']) ? "user_id=".$_GET['user_id']."&amp;" : ""))."\n</div>\n";
	closetable();
} elseif ((iSUPERADMIN || checkgroup($Sylvia->settings['warnings_moderator']) == TRUE) && isset($_GET['page']) && $_GET['page'] == "management") {
	if (isset($_GET['action']) && $_GET['action'] == "add" && isset($_GET['user_id']) && isnum($_GET['user_id'])) {
		require_once INCLUDES."bbcode_include.php";
		
		$user_result = dbquery("SELECT user_id, user_name, user_level, user_status FROM ".DB_USERS." WHERE user_id='".$_GET['user_id']."'");
		if (dbrows($user_result) != 0) {
			$user_data = dbarray($user_result);
			if ($user_data['user_level'] >= 102) { redirect(FUSION_SELF."?page=warnings&amp;user_id=".$user_data['user_id']); }
			
			if (!isset($_GET['add_type']) || !in_array($_GET['add_type'], array("own", "defined"))) {
				if ($Sylvia->settings['warnings_only_defined'] == 0 && dbcount("(warn_id)", DB_SYLVIA_DEFINED) == 0) {
					redirect(FUSION_SELF."?page=management&amp;action=add&amp;user_id=".$_GET['user_id']."&amp;add_type=own&amp;post_id=".$_GET['post_id']);
				} elseif ($Sylvia->settings['warnings_only_defined'] == 1 && dbcount("(warn_id)", DB_SYLVIA_DEFINED) != 0) {
					redirect(FUSION_SELF."?page=management&amp;action=add&amp;user_id=".$_GET['user_id']."&amp;add_type=defined");
				} elseif ($Sylvia->settings['warnings_only_defined'] == 0 && dbcount("(warn_id)", DB_SYLVIA_DEFINED) != 0) {
					opentable($Sylvia->locale['sylvia_page_00_000']."&nbsp;".Sylvia::Version.$Sylvia->locale['sylvia_page_00_001'].$Sylvia->locale['sylvia_page_00_002'].sprintf($Sylvia->locale['sylvia_page_00_003'], $user_data['user_name']));
					echo "<div style='text-align:center;margin:5px;'><a href='".FUSION_SELF."?page=management&amp;action=add&amp;user_id=".$_GET['user_id']."&amp;add_type=own".(isset($_GET['post_id']) && Sylvia::ForumPostExists($_GET['post_id']) ? "&amp;post_id=".$_GET['post_id'] : "")."' title='".$Sylvia->locale['sylvia_page_01_005']."'>".$Sylvia->locale['sylvia_page_01_005']."</a> :: <a href='".FUSION_SELF."?page=management&amp;action=add&amp;user_id=".$_GET['user_id']."&amp;add_type=defined".(isset($_GET['post_id']) && Sylvia::ForumPostExists($_GET['post_id']) ? "&amp;post_id=".$_GET['post_id'] : "")."' title='".$Sylvia->locale['sylvia_page_01_006']."'>".$Sylvia->locale['sylvia_page_01_006']."</a></div>\n";
					closetable();
				} else {
					opentable($Sylvia->locale['sylvia_page_00_000']."&nbsp;".Sylvia::Version.$Sylvia->locale['sylvia_page_00_001'].$Sylvia->locale['sylvia_page_00_002'].sprintf($Sylvia->locale['sylvia_page_00_003'], $user_data['user_name']));
					$Sylvia->InitError(2);
					echo "<div style='text-align:center;margin:5px;color:#FF0000;'>".$Sylvia->GetError("warn_defined_error1")."<br />".$Sylvia->GetError("warn_defined_error2")."</div>\n";
					$Sylvia->DestructError();
					closetable();
				}
			} elseif (isset($_GET['add_type']) && $_GET['add_type'] == "defined") {
				add_to_head("
					<script src='".INFUSIONS."sylvia/facebox/jquery-1.2.2.pack.js' type='text/javascript'></script>
					<link href='".INFUSIONS."sylvia/facebox/facebox.css' media='screen' rel='stylesheet' type='text/css' />
					<script src='".INFUSIONS."sylvia/facebox/facebox.js' type='text/javascript'></script>

					<script type='text/javascript'>
						jQuery(document).ready(function($) {
						  $('a[rel*=facebox]').facebox() 
						})
					</script>
				");
				
				if (isset($_GET['adding']) && isnum($_GET['adding'])) {
					$def_result = dbquery("SELECT warn_contents, warn_message, warn_post_info, warn_points, warn_data FROM ".DB_SYLVIA_DEFINED." WHERE warn_id='".$_GET['adding']."'");
					if (dbrows($def_result) != 0) {
						$def_data = dbarray($def_result);
						$def_data['warn_message'] = str_replace("{WARN_MOD}", $userdata['user_name'], $def_data['warn_message']);
						$def_add = dbquery("INSERT INTO ".DB_SYLVIA_WARNS." (warn_user, warn_moderator, warn_contents, warn_points, warn_data, warn_datestamp) VALUES ('".$user_data['user_id']."', '".$userdata['user_id']."', '".$def_data['warn_contents']."', '".$def_data['warn_points']."', '".$def_data['warn_data']."', '".time()."')");
						
						if (isset($_POST['thread_id']) && isnum($_POST['thread_id'])) {
							$status_thread_id = "&amp;thread_id=".$_POST['thread_id'];
						} else {
							$status_thread_id = "";
						}
						
						if ($def_add) {
							if ($Sylvia->settings['warnings_private_message'] == 1) {
								require_once INCLUDES."infusions_include.php";
								$send_pm = send_pm($user_data['user_id'], $userdata['user_id'], $Sylvia->locale['sylvia_page_08_000'], $def_data['warn_message'], "y");
							}
							if ($Sylvia->settings['pointx_connected'] == 1 && $Sylvia->settings['pointx_returning'] == 1) {
								$PointX->AddPoints($user_data['user_id'], $def_data['warn_points'], TRUE, $Sylvia->locale['sylvia_page_06_001']);
							}
							if ($Sylvia->settings['warnings_forum_messages'] == 1 && isset($_GET['post_id']) && Sylvia::ForumPostExists($_GET['post_id'])) {
								$Sylvia->AddModMessage($_GET['post_id'], $userdata['user_id'], $def_data['warn_post_info']);
							}
							redirect(FUSION_SELF."?page=management&amp;user_id=".$user_data['user_id']."&amp;status=warn_added&amp;error=0".$status_thread_id);
						} else {
							redirect(FUSION_SELF."?page=management&amp;user_id=".$user_data['user_id']."&amp;status=warn_defined_error4&amp;error=1".$status_thread_id);
						}
					} else {
						redirect(FUSION_SELF."?page=management&amp;user_id=".$user_data['user_id']."&amp;status=warn_defined_error4&amp;error=1".$status_thread_id);
					}
				} else {
					opentable($Sylvia->locale['sylvia_page_00_000']."&nbsp;".Sylvia::Version.$Sylvia->locale['sylvia_page_00_001'].$Sylvia->locale['sylvia_page_00_002'].sprintf($Sylvia->locale['sylvia_page_00_003'], $user_data['user_name']));
					echo "<table width='400' cellspacing='1' cellpadding='0' class='tbl-border center'>\n";
					$result = dbquery("SELECT warn_id, warn_contents, warn_message, warn_points, warn_data FROM ".DB_SYLVIA_DEFINED." ORDER BY warn_id DESC");
					$i = 0;
					echo "<tr>\n";
					echo "<td class='tbl2'>".$Sylvia->locale['sylvia_page_07_000']."</td>\n";
					echo "<td class='tbl2'>".$Sylvia->locale['sylvia_page_07_001']."</td>\n";
					echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$Sylvia->locale['sylvia_page_07_002']."</td>\n";
					echo "</tr>\n";
					while ($data = dbarray($result)) {
						$cell_color = ($i % 2 == 0 ? "tbl1" : "tbl2");
						echo "<tr>\n";
						echo "<td class='".$cell_color."'>".(strlen($data['warn_contents']) > 20 ? "<a href='#mydivc_".$data['warn_id']."' rel='facebox'>".trimlink(strip_tags(parseubb($data['warn_contents'])), 20)."</a>" : nl2br(parseubb($data['warn_contents'])))."</td>\n";
						echo "<td class='".$cell_color."'>".(strlen($data['warn_message']) > 20 ? "<a href='#mydivm_".$data['warn_id']."' rel='facebox'>".trimlink(strip_tags(parseubb($data['warn_message'])), 20)."</a>" : nl2br(parseubb($data['warn_message'])))."</td>\n";
						if (strlen($data['warn_contents']) > 20) {
							echo "<div id='mydivc_".$data['warn_id']."' style='display:none'>".nl2br(parseubb($data['warn_contents']))."</div>";
						}
						if (strlen($data['warn_message']) > 20) {
							echo "<div id='mydivm_".$data['warn_id']."' style='display:none'>".nl2br(parseubb($data['warn_message']))."</div>";
						}
						echo "<td align='center' width='1%' class='".$cell_color."' style='white-space:nowrap'>\n";
						echo "<a href='".FUSION_SELF.$aidlink."&amp;page=management&amp;action=add&amp;user_id=".$_GET['user_id']."&amp;add_type=defined&amp;adding=".$data['warn_id'].(isset($_GET['post_id']) && Sylvia::ForumPostExists($_GET['post_id']) ? "&amp;post_id=".$_GET['post_id'] : "")."'>".$Sylvia->locale['sylvia_page_07_003']."</a>\n";
						if ($Sylvia->settings['warnings_only_defined'] == 0) {
							echo " - <a href='".FUSION_SELF.$aidlink."&amp;page=management&amp;action=add&amp;user_id=".$_GET['user_id']."&amp;add_type=own&amp;complete=".$data['warn_id'].(isset($_GET['post_id']) && Sylvia::ForumPostExists($_GET['post_id']) ? "&amp;post_id=".$_GET['post_id'] : "")."'>".$Sylvia->locale['sylvia_page_07_004']."</a>\n";
						}
						echo "</td>\n";
						echo "</tr>\n";
						$i++;
					}
					echo "</table>\n";
					closetable();
				}
			} elseif (isset($_GET['add_type']) && $_GET['add_type'] == "own") {
				if (isset($_POST['add_warn'])) {
					$warn_contents = isset($_POST['warn_contents']) ? trim(stripinput($_POST['warn_contents'])) : "";
					if ($Sylvia->settings['warnings_private_message'] == 1) {
						$warn_message = isset($_POST['warn_message']) ? trim(stripinput($_POST['warn_message'])) : "";
					} else {
						$warn_message = "";
					}
					
					if ($Sylvia->settings['warnings_forum_messages'] == 1) {
						$warn_post_info = isset($_POST['warn_post_info']) ? trim(stripinput($_POST['warn_post_info'])) : "";
						$wfm_post_id = isset($_POST['wfm_post_id']) && Sylvia::ForumPostExists($_POST['wfm_post_id']) ? $_POST['wfm_post_id'] : 0;
					} else {
						$warn_post_info = "";
						$wfm_post_id = 0;
					}
					
					if ($Sylvia->settings['warnings_type'] == 2) {
						$warn_data = isset($_POST['warn_data']) && isnum($_POST['warn_data']) ? $_POST['warn_data'] : 0;
						$warn_data = $warn_data <= 100 ? $warn_data : 0;
					} else {
						$warn_data = 0;
					}
					
					if ($Sylvia->settings['pointx_connected'] == 1) {
						$warn_points = isset($_POST['warn_points']) && isnum($_POST['warn_points']) ? $_POST['warn_points'] : 0;
					} else {
						$warn_points = 0;
					}
					
					if (isset($_POST['thread_id']) && isnum($_POST['thread_id'])) {
						$status_thread_id = "&amp;thread_id=".$_POST['thread_id'];
					} else {
						$status_thread_id = "";
					}
					
					if (!empty($warn_contents)) {
						if ($Sylvia->settings['warnings_type'] == 1 || ($Sylvia->settings['warnings_type'] == 2 && $warn_data != 0)) {
							if ($Sylvia->settings['pointx_connected'] == 0 || ($Sylvia->settings['pointx_connected'] == 1 && $warn_points != 0)) {
								$result = dbquery("INSERT INTO ".DB_SYLVIA_WARNS." (warn_user, warn_moderator, warn_contents, warn_points, warn_data, warn_datestamp) VALUES ('".$user_data['user_id']."', '".$userdata['user_id']."', '".$warn_contents."', '".$warn_points."', '".$warn_data."', '".time()."')");
								if ($result) {
									if ($warn_message != "") {
										require_once INCLUDES."infusions_include.php";
										$send_pm = send_pm($user_data['user_id'], $userdata['user_id'], $Sylvia->locale['sylvia_page_08_000'], $warn_message, "y");
									}
									if ($warn_post_info != "" && $wfm_post_id != 0) {
										$Sylvia->AddModMessage($wfm_post_id, $userdata['user_id'], $warn_post_info);
									}
									if ($warn_points != 0) {
										$PointX->TakePoints($user_data['user_id'], $warn_points, TRUE, $Sylvia->locale['sylvia_page_06_000']);
									}
									redirect(FUSION_SELF."?page=management&amp;user_id=".$user_data['user_id']."&amp;status=warn_added&amp;error=0".$status_thread_id);
								} else {
									redirect(FUSION_SELF."?page=management&amp;user_id=".$user_data['user_id']."&amp;status=warn_add_error1&amp;error=1".$status_thread_id);
								}
							} else {
								redirect(FUSION_SELF."?page=management&amp;user_id=".$user_data['user_id']."&amp;status=warn_add_error2&amp;error=1".$status_thread_id);
							}
						} else {
							redirect(FUSION_SELF."?page=management&amp;user_id=".$user_data['user_id']."&amp;status=warn_add_error3&amp;error=1".$status_thread_id);
						}
					} else {
						redirect(FUSION_SELF."?page=management&amp;user_id=".$user_data['user_id']."&amp;status=warn_add_error4&amp;error=1".$status_thread_id);
					}
				} elseif (isset($_POST['cancel'])) {
					redirect(FORUM."viewthread.php?thread_id=".$_POST['thread_id']);
				} else {
					if (isset($_GET['complete']) && isnum($_GET['complete']) && dbcount("(warn_id)", DB_SYLVIA_DEFINED, "warn_id='".$_GET['complete']."'") == 1) {
						$complete_result = dbquery("SELECT warn_contents, warn_message, warn_post_info, warn_points, warn_data FROM ".DB_SYLVIA_DEFINED." WHERE warn_id='".$_GET['complete']."'");
						$complete_data = dbarray($complete_result);
						
						$warn_contents = $complete_data['warn_contents'];
						$warn_message = $complete_data['warn_message'];
						$warn_post_info = $complete_data['warn_post_info'];
						$warn_points = $complete_data['warn_points'];
						$warn_data = $complete_data['warn_data'];
					} else {
						$warn_contents = "";
						$warn_message = "";
						$warn_post_info = "";
						$warn_points = "";
						$warn_data = "";
					}
					opentable($Sylvia->locale['sylvia_page_00_000']."&nbsp;".Sylvia::Version.$Sylvia->locale['sylvia_page_00_001'].$Sylvia->locale['sylvia_page_00_002'].sprintf($Sylvia->locale['sylvia_page_00_003'], $user_data['user_name']));
					echo "<form name='addwarn' method='post' action='".FUSION_SELF."?page=management&amp;action=add&amp;user_id=".$user_data['user_id']."&amp;add_type=own'>\n";
					echo "<table cellpadding='0' cellspacing='0' width='400' class='center'>\n<tr>\n";
					echo "<td colspan='2' class='tbl'>".$Sylvia->locale['sylvia_page_01_000'].":<br />\n";
					echo "<textarea type='text' name='warn_contents' cols='70' rows='4' class='textbox' style='width:98%'>".$warn_contents."</textarea><br />\n";
					echo display_bbcodes("95%;", "warn_contents", "addwarn", "b|i|u|color|url|center|size|big|small")."</td>\n";
					echo "</tr>\n<tr>\n";
					if ($Sylvia->settings['warnings_private_message'] == 1) {
						echo "<td colspan='2' class='tbl'>".$Sylvia->locale['sylvia_admin_09_001'].":<br />\n";
						echo "<textarea type='text' name='warn_message' cols='70' rows='4' class='textbox' style='width:98%'>".$warn_message."</textarea><br />\n";
						echo display_bbcodes("95%;", "warn_message", "addwarn")."</td>\n";
						echo "</tr>\n<tr>\n";
					}
					if ($Sylvia->settings['warnings_forum_messages'] == 1 && isset($_GET['post_id']) && Sylvia::ForumPostExists($_GET['post_id'])) {
						echo "<td colspan='2' class='tbl'>".$Sylvia->locale['sylvia_admin_09_006'].":<br />\n";
						echo "<textarea type='text' name='warn_post_info' cols='70' rows='4' class='textbox' style='width:98%'>".$warn_post_info."</textarea><br />\n";
						echo "<input type='hidden' name='wfm_post_id' value='".$_GET['post_id']."' />\n";
						echo display_bbcodes("95%;", "warn_post_info", "addwarn", "b|i|u|color|url|center|size|big|small")."\n";
						echo "</td>\n";
						echo "</tr>\n<tr>\n";
					}
					if ($Sylvia->settings['warnings_type'] == 2) {
						echo "<td align='center' width='50%' class='tbl'>".$Sylvia->locale['sylvia_page_01_001'].":<br />\n";
						echo "<input type='text' name='warn_data' value='".$warn_data."' maxlength='3' class='textbox' style='width:50px;' />%</td>\n";
					}
					if ($Sylvia->settings['pointx_connected'] == 1) {
						echo "<td align='center' class='tbl'>".$Sylvia->locale['sylvia_page_01_002'].":<br />\n";
						echo "<input type='text' name='warn_points' value='".$warn_points."' class='textbox' style='width:50px;' /></td>\n";
					}
					echo "</tr>\n<tr>\n";
					echo "<td colspan='2' width='50%' align='center' class='tbl'>\n";
					if (isset($_GET['thread_id']) && isnum($_GET['thread_id'])) {
						echo "<input type='hidden' name='thread_id' value='".$_GET['thread_id']."' />\n";
					}
					echo "<input type='submit' name='add_warn' value='".$Sylvia->locale['sylvia_page_01_003']."' class='button' />\n";
					if (isset($_GET['thread_id']) && isnum($_GET['thread_id'])) {
						echo "&nbsp;<input type='submit' name='cancel' value='".$Sylvia->locale['sylvia_page_01_004']."' class='button' />\n";
					}
					echo "</td>\n";
					echo "</tr>\n</table>\n</form>\n";
					closetable();
				}
			}
		} else {
			redirect(FUSION_SELF."?page=management&amp;status=warn_add_error5&amp;error=1");
		}
	} elseif (isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['warn_id']) && isnum($_GET['warn_id'])) {
		require_once INCLUDES."bbcode_include.php";
		
		$result = dbquery("SELECT warn_id, warn_user, warn_moderator, warn_contents, warn_data, warn_datestamp FROM ".DB_SYLVIA_WARNS." WHERE warn_id='".$_GET['warn_id']."'");
		if (dbrows($result) != 0) {
			$data = dbarray($result);
			
			$user_data = dbarray(dbquery("SELECT user_id, user_name, user_status FROM ".DB_USERS." WHERE user_id='".$data['warn_user']."'"));
			$mod_result = dbquery("SELECT user_id, user_name, user_status FROM ".DB_USERS." WHERE user_id='".$data['warn_moderator']."'");
			if (dbrows($mod_result) != 0) {
				$mod_data = dbarray($mod_result);
				$ModInfo = profile_link($mod_data['user_id'], $mod_data['user_name'], $mod_data['user_status']);
			} else {
				$ModInfo = "<span style='color:#FF0000;'>".$Sylvia->locale['sylvia_page_02_005']."</span>";
			}
			
			if (isset($_POST['update_warn'])) {
				$warn_contents = isset($_POST['warn_contents']) ? trim(stripinput($_POST['warn_contents'])) : "";
				
				if ($Sylvia->settings['warnings_type'] == 2) {
					$warn_data = isset($_POST['warn_data']) && isnum($_POST['warn_data']) ? $_POST['warn_data'] : 0;
					$warn_data = $warn_data <= 100 ? $warn_data : 0;
				} else {
					$warn_data = 0;
				}
				
				if (isset($_POST['thread_id']) && isnum($_POST['thread_id'])) {
					$status_thread_id = "&amp;thread_id=".$_POST['thread_id'];
				} else {
					$status_thread_id = "";
				}
				
				if (!empty($warn_contents)) {
					if ($Sylvia->settings['warnings_type'] == 1 || ($Sylvia->settings['warnings_type'] == 2 && $warn_data != 0)) {
						$result2 = dbquery("UPDATE ".DB_SYLVIA_WARNS." SET warn_contents='".$warn_contents."', warn_data='".$warn_data."' WHERE warn_id='".$data['warn_id']."'");
						if ($result2) {
							redirect(FUSION_SELF."?page=management&amp;user_id=".$user_data['user_id']."&amp;status=warn_updated&amp;error=0".$status_thread_id);
						} else {
							redirect(FUSION_SELF."?page=management&amp;user_id=".$user_data['user_id']."&amp;status=warn_update_error1&amp;error=1".$status_thread_id);
						}
					} else {
						redirect(FUSION_SELF."?page=management&amp;user_id=".$user_data['user_id']."&amp;status=warn_update_error2&amp;error=1".$status_thread_id);
					}
				} else {
					redirect(FUSION_SELF."?page=management&amp;user_id=".$user_data['user_id']."&amp;status=warn_update_error3&amp;error=1".$status_thread_id);
				}
			} elseif (isset($_POST['cancel'])) {
				redirect(FORUM."viewthread.php?thread_id=".$_POST['thread_id']);
			} else {
				opentable($Sylvia->locale['sylvia_page_00_000']."&nbsp;".Sylvia::Version.$Sylvia->locale['sylvia_page_00_001'].$Sylvia->locale['sylvia_page_00_002'].sprintf($Sylvia->locale['sylvia_page_00_004'], $user_data['user_name']));
				echo "<form name='updatewarn' method='post' action='".FUSION_SELF."?page=management&amp;action=edit&amp;warn_id=".$data['warn_id']."'>\n";
				echo "<table cellpadding='0' cellspacing='0' width='400' class='center'>\n<tr>\n";
				echo "<td class='tbl' style='text-align:center;margin:10px;'><strong>".$Sylvia->locale['sylvia_page_02_002'].":</strong>&nbsp;".$ModInfo."</td>\n";
				echo "</tr>\n<tr>\n";
				echo "<td class='tbl'>".$Sylvia->locale['sylvia_page_02_000'].":<br />\n";
				echo "<textarea type='text' name='warn_contents' cols='70' rows='4' class='textbox' style='width:98%'>".$data['warn_contents']."</textarea><br />\n";
				echo display_bbcodes("95%;", "warn_contents", "updatewarn", "b|i|u|color|url|center|size|big|small")."</td>\n";
				echo "</tr>\n<tr>\n";
				if ($Sylvia->settings['warnings_type'] == 2) {
					echo "<td class='tbl'>".$Sylvia->locale['sylvia_page_02_001'].":<br />\n";
					echo "<input type='text' name='warn_data' value='".$data['warn_data']."' maxlength='3' class='textbox' style='width:50px;' />%</td>\n";
				}
				echo "</tr>\n<tr>\n";
				echo "<td align='center' class='tbl'>\n";
				if (isset($_GET['thread_id']) && isnum($_GET['thread_id'])) {
					echo "<input type='hidden' name='thread_id' value='".$_GET['thread_id']."' />\n";
				}
				echo "<input type='submit' name='update_warn' value='".$Sylvia->locale['sylvia_page_02_003']."' class='button' />\n";
				if (isset($_GET['thread_id']) && isnum($_GET['thread_id'])) {
					echo "&nbsp;<input type='submit' name='cancel' value='".$Sylvia->locale['sylvia_page_02_004']."' class='button' />\n";
				}
				echo "</td>\n";
				echo "</tr>\n</table>\n</form>\n";
				closetable();
			}
		} else {
			redirect(FUSION_SELF."?page=management&amp;status=warn_update_error4&amp;error=1");
		}
	} elseif (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['warn_id']) && isnum($_GET['warn_id'])) {
		$result = dbquery("SELECT warn_id, warn_user, warn_contents, warn_points FROM ".DB_SYLVIA_WARNS." WHERE warn_id='".$_GET['warn_id']."'");
		if (dbrows($result) != 0) {
			$data =  dbarray($result);
			$user_name = dbresult(dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_id='".$data['warn_user']."'"), 0);
			
			if (isset($_POST['yes'])) {
				$result2 = dbquery("DELETE FROM ".DB_SYLVIA_WARNS." WHERE warn_id='".$data['warn_id']."'");
				if ($result2) {
					if ($Sylvia->settings['pointx_connected'] == 1 && $Sylvia->settings['pointx_returning'] == 1) {
						$PointX->AddPoints($data['warn_user'], $data['warn_points'], TRUE, $Sylvia->locale['sylvia_page_06_001']);
					}
					redirect(FUSION_SELF."?page=management&amp;user_id=".$data['warn_user']."&amp;status=warn_deleted&amp;error=0");
				} else {
					redirect(FUSION_SELF."?page=management&amp;user_id=".$data['warn_user']."&amp;status=warn_delete_error1&amp;error=1");
				}
			} elseif (isset($_POST['no'])) {
				redirect(FUSION_SELF."?page=warnings&amp;user_id=".$data['warn_user']);
			} else {
				opentable($Sylvia->locale['sylvia_page_00_000']."&nbsp;".Sylvia::Version.$Sylvia->locale['sylvia_page_00_001'].$Sylvia->locale['sylvia_page_00_002'].sprintf($Sylvia->locale['sylvia_page_00_005'], $user_name));
				echo "<form name='settingsform' method='post' action='".FUSION_SELF."?page=management&amp;action=delete&amp;warn_id=".$data['warn_id']."'>\n";
				echo "<div style='text-align:center;'>\n";
				echo $Sylvia->locale['sylvia_page_03_000']."<br />\n";
				echo "<strong>".$Sylvia->locale['sylvia_page_03_001']."</strong>".nl2br(parseubb($data['warn_contents']))."<br /><br />\n";
				echo "<input type='submit' name='yes' value='".$Sylvia->locale['sylvia_page_03_002']."' class='button' />&nbsp;<input type='submit' name='no' value='".$Sylvia->locale['sylvia_page_03_003']."' class='button' />";
				echo "</div>\n</form>\n";
				closetable();
			}
		} else {
			redirect(FUSION_SELF."?page=management&amp;status=warn_delete_error2&amp;error=1");
		}
	} else {
		if (!isset($_GET['status'])) { $_GET['status'] = "status_not_defined"; }
		if (!isset($_GET['error'])) { $_GET['error'] = 1; }
		opentable($Sylvia->locale['sylvia_page_00_000']."&nbsp;".Sylvia::Version.$Sylvia->locale['sylvia_page_00_001'].$Sylvia->locale['sylvia_page_00_002'].$Sylvia->locale['sylvia_page_00_006']);
		$Sylvia->InitError(2);
		echo "<div style='font-size:15px;font-weight:bold;margin:5px;text-align:center;color:#".(isset($_GET['error']) && $_GET['error'] == 1 ? "FF0000" : "009900").";'>".$Sylvia->GetError($_GET['status'])."</div>\n<br />\n";
		echo "<div style='text-align:center;'>\n";
		if (isset($_GET['thread_id']) && isnum($_GET['thread_id'])) {
			echo "<a href='".FORUM."viewthread.php?thread_id=".$_GET['thread_id']."' title='".$Sylvia->locale['sylvia_page_05_000']."'>".$Sylvia->locale['sylvia_page_05_000']."</a><br />\n";
		}
		if (isset($_GET['user_id']) && isnum($_GET['user_id'])) {
			echo "<a href='".FUSION_SELF."?page=warnings&amp;user_id=".$_GET['user_id']."' title='".$Sylvia->locale['sylvia_page_05_001']."'>".$Sylvia->locale['sylvia_page_05_001']."</a><br />\n";
		}
		echo "<a href='".BASEDIR."index.php' title='".$Sylvia->locale['sylvia_page_05_003']."'>".$Sylvia->locale['sylvia_page_05_003']."</a>\n";
		echo "</div>\n";
		$Sylvia->DestructError();
		closetable();
	}
} elseif ($Sylvia->settings['warnings_forum_messages'] == 1 && ((iSUPERADMIN || checkgroup($Sylvia->settings['warnings_moderator']) == TRUE) && isset($_GET['page']) && $_GET['page'] == "forum_messages")) {
	include_once INCLUDES."bbcode_include.php";
	if (isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['message_id']) && isnum($_GET['message_id'])) {
		$result = dbquery("SELECT message_id, message_post, message_mod, message_contents FROM ".DB_SYLVIA_MESSAGES." WHERE message_id='".$_GET['message_id']."'");
		if (dbrows($result) != 0) {
			$data = dbarray($result);
			if (isset($_POST['save_message'])) {
				$warn_post_info = isset($_POST['warn_post_info']) && !empty($_POST['warn_post_info']) ? trim(stripinput($_POST['warn_post_info'])) : "";
				$post_id = isset($_POST['post_id']) && Sylvia::ForumPostExists($_POST['post_id']) ? $_POST['post_id'] : 0;
				
				if ($warn_post_info != "") {
					$result2 = dbquery("UPDATE ".DB_SYLVIA_MESSAGES." SET message_contents='".$warn_post_info."' WHERE message_id='".$data['message_id']."'");
					
					$Sylvia->InitError(2);
					opentable($Sylvia->locale['sylvia_page_00_000']."&nbsp;".Sylvia::Version.$Sylvia->locale['sylvia_page_00_001'].$Sylvia->locale['sylvia_page_00_002'].$Sylvia->locale['sylvia_page_09_000']);
					echo "<div style='text-align:center;'><span style='color:#".($result2 ? "009900" : "FF0000").";'>".$Sylvia->GetError($result2 ? "message_updated" : "message_update_error")."</span><br />";
					if ($result2) echo "<a href='".FORUM."viewthread.php?thread_id=".dbresult(dbquery("SELECT thread_id FROM ".DB_POSTS." WHERE post_id='".$data['message_post']."'"), 0)."' title='".$Sylvia->locale['sylvia_page_09_003']."'>".$Sylvia->locale['sylvia_page_09_003']."</a>";
					echo "</div>\n";					
					closetable();
					$Sylvia->DestructError();
				} else {
					redirect(FUSION_SELF."?page=forum_messages&amp;action=edit&amp;message_id=".$data['message_id']."&amp;empty_field");
				}
			} else {
				opentable($Sylvia->locale['sylvia_page_00_000']."&nbsp;".Sylvia::Version.$Sylvia->locale['sylvia_page_00_001'].$Sylvia->locale['sylvia_page_00_002'].$Sylvia->locale['sylvia_page_09_000']);
				echo "<form name='editmessage' method='post' action='".FUSION_SELF."?page=forum_messages&amp;action=edit&amp;message_id=".$data['message_id']."'>\n";
				if (isset($_GET['empty_field'])) echo "<div style='text-align:center;color:#FF0000;margin:5px;'>".$Sylvia->locale['sylvia_page_09_001']."</div>\n";
				echo "<table cellpadding='0' cellspacing='0' width='400' class='center'>\n<tr>\n";
				echo "<td colspan='2' class='tbl'>".$Sylvia->locale['sylvia_admin_09_006'].":<br />\n";
				echo "<textarea type='text' name='warn_post_info' cols='70' rows='4' class='textbox' style='width:98%'>".$data['message_contents']."</textarea><br />\n";
				echo "<input type='hidden' name='post_id' value='".$data['message_post']."' />\n";
				echo display_bbcodes("95%;", "warn_post_info", "editmessage", "b|i|u|color|url|size|big|small")."\n";
				echo "</td>\n";
				echo "</tr>\n<tr>\n";
				echo "<td align='center' colspan='2' class='tbl'>\n";
				echo "<input type='submit' name='save_message' value='".$Sylvia->locale['sylvia_page_09_002']."' class='button' /></td>\n";
				echo "</tr>\n</table>\n</form>\n";
				closetable();
			}
		} else {
			redirect(FORUM);
		}
	} elseif (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['message_id']) && isnum($_GET['message_id'])) {
		$result = dbquery("SELECT message_id, message_post FROM ".DB_SYLVIA_MESSAGES." WHERE message_id='".$_GET['message_id']."'");
		if (dbrows($result) != 0) {
			$data = dbarray($result);
			
			if (isset($_POST['yes'])) {
				$result2 = dbquery("DELETE FROM ".DB_SYLVIA_MESSAGES." WHERE message_id='".$data['message_id']."'");
				
				$Sylvia->InitError(2);
				opentable($Sylvia->locale['sylvia_page_00_000']."&nbsp;".Sylvia::Version.$Sylvia->locale['sylvia_page_00_001'].$Sylvia->locale['sylvia_page_00_002'].$Sylvia->locale['sylvia_page_09_000']);
				echo "<div style='text-align:center;'><span style='color:#".($result2 ? "009900" : "FF0000").";'>".$Sylvia->GetError($result2 ? "message_deleted" : "message_delete_error")."</span><br />";
				if ($result2) echo "<a href='".FORUM."viewthread.php?thread_id=".dbresult(dbquery("SELECT thread_id FROM ".DB_POSTS." WHERE post_id='".$data['message_post']."'"), 0)."' title='".$Sylvia->locale['sylvia_page_09_003']."'>".$Sylvia->locale['sylvia_page_09_003']."</a>";
				echo "</div>\n";					
				closetable();
				$Sylvia->DestructError();
			} elseif (isset($_POST['no'])) {
				redirect(FORUM."viewthread.php?thread_id=".dbresult(dbquery("SELECT thread_id FROM ".DB_POSTS." WHERE post_id='".$data['message_post']."'"), 0));
			} else {
				opentable($Sylvia->locale['sylvia_page_00_000']."&nbsp;".Sylvia::Version.$Sylvia->locale['sylvia_page_00_001'].$Sylvia->locale['sylvia_page_00_002'].$Sylvia->locale['sylvia_page_09_000']);
				echo "<div style='text-align:center;'>\n";
				echo $Sylvia->locale['sylvia_fg_00_005']."<br />";
				echo "<form name='editmessage' method='post' action='".FUSION_SELF."?page=forum_messages&amp;action=delete&amp;message_id=".$data['message_id']."'>\n";
				echo "<input type='submit' name='yes' value='".$Sylvia->locale['sylvia_fg_00_006']."' class='button' />&nbsp;<input type='submit' name='no' value='".$Sylvia->locale['sylvia_fg_00_007']."' class='button' />";
				echo "</form>\n</div>\n";
				closetable();
			}
		} else {
			redirect(FORUM);
		}
	} else {
		redirect(FORUM);
	}
} elseif ((!iSUPERADMIN && checkgroup($Sylvia->settings['warnings_moderator']) == FALSE) && isset($_GET['page']) && ($_GET['page'] == "management" || $_GET['page'] == "forum_messages")) {
	opentable($Sylvia->locale['sylvia_page_00_009']);
	echo "<div style='font-size:15px;font-weight:bold;margin:5px;text-align:center;color:#FF0000;'>".$Sylvia->locale['sylvia_page_05_002']."</div>\n";
	closetable();
}

require_once THEMES."templates/footer.php";
?>