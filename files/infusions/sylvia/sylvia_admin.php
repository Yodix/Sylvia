<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright © 2002 - 2012 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sylvia_admin.php
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
require_once THEMES."templates/admin_header.php";
require_once INFUSIONS."sylvia/sylvia_core.php";

if (!checkrights("SVA") || !defined("iAUTH") || $_GET['aid'] != iAUTH) { redirect("../index.php"); }
if (!isset($_GET['page']) || !in_array($_GET['page'], explode(",", Sylvia::AdminPages))) { $_GET['page'] = "blockades"; }
if (isset($_POST['update_version'])) { $_GET['page'] = "update"; }

if ($Sylvia->settings['warning_update_info'] == 1) {
	$locale_update = in_array($settings['locale'], array("Polish", "Polish-utf8", "English")) ? $settings['locale'] : "English";
	$update = simplexml_load_file("http://www.on-deck.eu/addons/sylvia/info_".$locale_update.".xml");
	if (str_replace(".", "", $update->ver) > str_replace(".", "", Sylvia::Version)) {
		unset($_GET['page']);
		
		$_GET['page'] = "update";
	} else {
		unset($update);
	}
}

$Sylvia->InitNavi();
$Sylvia->GenNavi("<table cellpadding='0' cellspacing='0' class='tbl-border' align='center' style='width:500px; margin-bottom:20px; text-align:center;'>\n<tr>\n");
$Sylvia->GenNavi("<td class='tbl1'>\n");
$Sylvia->GenNavi("<a href='".FUSION_SELF.$aidlink."&amp;page=blockades' title='".$Sylvia->locale['sylvia_admin_00_003']."'>".(isset($_GET['page']) && $_GET['page'] == "blockades" ? "<img src='".INFUSIONS."sylvia/images/".$settings['locale']."/blockades.png' style='vertical-align:middle;' alt='".$Sylvia->locale['sylvia_admin_00_003']."' borde='0' width='120' height='20' />" : "<img src='".INFUSIONS."sylvia/images/".$settings['locale']."/blockades2.png' style='vertical-align:middle;' alt='".$Sylvia->locale['sylvia_admin_00_003']."' width='120' height='20' border='0' onmouseover=\"this.src = '".INFUSIONS."sylvia/images/".$settings['locale']."/blockades.png'\" onmouseout=\"this.src = '".INFUSIONS."sylvia/images/".$settings['locale']."/blockades2.png'\" /></a>")."</a> :: \n");
$Sylvia->GenNavi("<a href='".FUSION_SELF.$aidlink."&amp;page=defined' title='".$Sylvia->locale['sylvia_admin_00_010']."'>".(isset($_GET['page']) && $_GET['page'] == "defined" ? "<img src='".INFUSIONS."sylvia/images/".$settings['locale']."/defined.png' style='vertical-align:middle;' alt='".$Sylvia->locale['sylvia_admin_00_010']."' borde='0' width='120' height='20' />" : "<img src='".INFUSIONS."sylvia/images/".$settings['locale']."/defined2.png' style='vertical-align:middle;' alt='".$Sylvia->locale['sylvia_admin_00_010']."' width='120' height='20' border='0' onmouseover=\"this.src = '".INFUSIONS."sylvia/images/".$settings['locale']."/defined.png'\" onmouseout=\"this.src = '".INFUSIONS."sylvia/images/".$settings['locale']."/defined2.png'\" /></a>")."</a> :: \n");
$Sylvia->GenNavi("<a href='".FUSION_SELF.$aidlink."&amp;page=settings' title='".$Sylvia->locale['sylvia_admin_00_010']."'>".(isset($_GET['page']) && $_GET['page'] == "settings" ? "<img src='".INFUSIONS."sylvia/images/".$settings['locale']."/settings.png' style='vertical-align:middle;' alt='".$Sylvia->locale['sylvia_admin_00_010']."' borde='0' width='120' height='20' />" : "<img src='".INFUSIONS."sylvia/images/".$settings['locale']."/settings2.png' style='vertical-align:middle;' alt='".$Sylvia->locale['sylvia_admin_00_010']."' width='120' height='20' border='0' onmouseover=\"this.src = '".INFUSIONS."sylvia/images/".$settings['locale']."/settings.png'\" onmouseout=\"this.src = '".INFUSIONS."sylvia/images/".$settings['locale']."/settings2.png'\" /></a>")."</a>\n");
$Sylvia->GenNavi("</td>\n");
$Sylvia->GenNavi("</tr>\n</table>\n");

if (isset($_GET['status']) && !empty($_GET['status'])) {
	$Sylvia->InitError(1);
	echo "<div id='close-message'><div class='admin-message'>".$Sylvia->GetError($_GET['status'])."</div></div>\n";
	$Sylvia->DestructError();
}

if (isset($_GET['page']) && $_GET['page'] == "blockades") {
	if (isset($_GET['action']) && $_GET['action'] == "create" && !isset($_GET['blockade_id'])) {
		if (isset($_POST['add_blockade'])) {
			$blockade_name = isset($_POST['blockade_name']) ? stripinput($_POST['blockade_name']) : "";
			$blockade_text = isset($_POST['blockade_text']) ? trim(stripinput($_POST['blockade_text'])) : "";
			$blockade_data = isset($_POST['blockade_data']) && isnum($_POST['blockade_data']) ? $_POST['blockade_data'] : 0;
			
			if ($Sylvia->settings['warnings_type'] == 2) {
				$blockade_data = $blockade_data <= 100 ? $blockade_data : 0;
			}
			
			if (!empty($blockade_name)) {
				if (!empty($blockade_text)) {
					if ($blockade_data != 0) {
						if (dbcount("(blockade_id)", DB_SYLVIA_BLOCKADES, "blockade_name='".$blockade_name."'") == 0) {
							$result = dbquery("INSERT INTO ".DB_SYLVIA_BLOCKADES." (blockade_name, blockade_text, blockade_code, blockade_data, blockade_active) VALUES ('".$blockade_name."', '".$blockade_text."', '".Sylvia::GenKey(25)."', '".$blockade_data."', '1')");
							if ($result) {
								redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_added");
							} else {
								redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_add_error1");
							}
						} else {
							redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_add_error2");
						}
					} else {
						redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_add_error3");
					}
				} else {
					redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_add_error4");
				}
			} else {
				redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_add_error5");
			}
		} else {
			opentable($Sylvia->locale['sylvia_admin_00_000']."&nbsp".Sylvia::Version.$Sylvia->locale['sylvia_admin_00_001'].$Sylvia->locale['sylvia_admin_00_003'].$Sylvia->locale['sylvia_admin_00_001'].$Sylvia->locale['sylvia_admin_00_005']);
			echo $Sylvia->RenderNavi();
			echo "<form name='addblockade' method='post' action='".FUSION_SELF.$aidlink."&amp;page=blockades&amp;action=create'>\n";
			echo "<table cellpadding='0' cellspacing='0' width='400' class='center'>\n<tr>\n";
			echo "<td width='1%' class='tbl' style='white-space:nowrap'>".$Sylvia->locale['sylvia_admin_03_000'].":</td>\n";
			echo "<td class='tbl'><input type='text' name='blockade_name' value='' class='textbox' style='width:200px;' /></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td width='1%' class='tbl' style='white-space:nowrap'>".$Sylvia->locale['sylvia_admin_03_001'].":</td>\n";
			echo "<td class='tbl'><input type='text' name='blockade_text' value='' class='textbox' style='width:200px;' /></td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td width='1%' class='tbl' style='white-space:nowrap'>".($Sylvia->settings['warnings_type'] == 1 ? $Sylvia->locale['sylvia_admin_03_002'] : $Sylvia->locale['sylvia_admin_03_003']).":</td>\n";
			echo "<td class='tbl'><input type='text' name='blockade_data' value='' class='textbox' style='width:50px;' />".($Sylvia->settings['warnings_type'] == 2 ? "%" : "")."</td>\n";
			echo "</tr>\n<tr>\n";
			echo "<td align='center' colspan='2' class='tbl'>\n";
			echo "<input type='submit' name='add_blockade' value='".$Sylvia->locale['sylvia_admin_03_004']."' class='button' /></td>\n";
			echo "</tr>\n</table>\n</form>\n";
			closetable();
		}
	} elseif (isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['blockade_id']) && isnum($_GET['blockade_id'])) {
		$result = dbquery("SELECT blockade_id, blockade_name, blockade_text, blockade_code, blockade_data, blockade_active FROM ".DB_SYLVIA_BLOCKADES." WHERE blockade_id='".$_GET['blockade_id']."'");
		if (dbrows($result) != 0) {
			$data = dbarray($result);
			
			if (isset($_POST['update_blockade'])) {
				$blockade_name = isset($_POST['blockade_name']) ? stripinput($_POST['blockade_name']) : "";
				$blockade_text = isset($_POST['blockade_text']) ? trim(stripinput($_POST['blockade_text'])) : "";
				$blockade_data = isset($_POST['blockade_data']) && isnum($_POST['blockade_data']) ? $_POST['blockade_data'] : 0;
				$blockade_active = isset($_POST['blockade_active']) && $_POST['blockade_active'] == 1 ? $_POST['blockade_active'] : 0;
				
				if ($Sylvia->settings['warnings_type'] == 2) {
					$blockade_data = $blockade_data <= 100 ? $blockade_data : 0;
				}
				
				if (!empty($blockade_name)) {
					if (!empty($blockade_text)) {
						if ($blockade_data != 0) {
							if ($data['blockade_name'] == $blockade_name || dbcount("(blockade_id)", DB_SYLVIA_BLOCKADES, "blockade_name='".$blockade_name."'") == 0) {
								$result2 = dbquery("UPDATE ".DB_SYLVIA_BLOCKADES." SET blockade_name='".$blockade_name."', blockade_text='".$blockade_text."', blockade_data='".$blockade_data."', blockade_active='".$blockade_active."' WHERE blockade_id='".$data['blockade_id']."'");
								if ($result) {
									redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_updated");
								} else {
									redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_update_error1");
								}
							} else {
								redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_update_error2");
							}
						} else {
							redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_update_error3");
						}
					} else {
						redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_update_error4");
					}
				} else {
					redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_update_error5");
				}
			} else {
				opentable($Sylvia->locale['sylvia_admin_00_000']."&nbsp".Sylvia::Version.$Sylvia->locale['sylvia_admin_00_001'].$Sylvia->locale['sylvia_admin_00_003'].$Sylvia->locale['sylvia_admin_00_001'].sprintf($Sylvia->locale['sylvia_admin_00_006'], $data['blockade_name']));
				echo $Sylvia->RenderNavi();
				echo "<form name='updateblockade' method='post' action='".FUSION_SELF.$aidlink."&amp;page=blockades&amp;action=edit&amp;blockade_id=".$data['blockade_id']."'>\n";
				echo "<table cellpadding='0' cellspacing='0' width='400' class='center'>\n<tr>\n";
				echo "<td width='1%' class='tbl' style='white-space:nowrap'>".$Sylvia->locale['sylvia_admin_04_000'].":</td>\n";
				echo "<td class='tbl'><input type='text' name='blockade_name' value='".$data['blockade_name']."' class='textbox' style='width:200px;' /></td>\n";
				echo "</tr>\n<tr>\n";
				echo "<td width='1%' class='tbl' style='white-space:nowrap'>".$Sylvia->locale['sylvia_admin_04_001'].":</td>\n";
				echo "<td class='tbl'><input type='text' name='blockade_text' value='".$data['blockade_text']."' class='textbox' style='width:200px;' /></td>\n";
				echo "</tr>\n<tr>\n";
				echo "<td width='1%' class='tbl' style='white-space:nowrap'>".($Sylvia->settings['warnings_type'] == 1 ? $Sylvia->locale['sylvia_admin_04_002'] : $Sylvia->locale['sylvia_admin_04_003']).":</td>\n";
				echo "<td class='tbl'><input type='text' name='blockade_data' value='".$data['blockade_data']."' class='textbox' style='width:50px;' />".($Sylvia->settings['warnings_type'] == 2 ? "%" : "")."</td>\n";
				echo "</tr>\n<tr>\n";
				echo "<td width='1%' class='tbl' style='white-space:nowrap'>".$Sylvia->locale['sylvia_admin_04_005'].":</td>\n";
				echo "<td class='tbl'><label><input type='checkbox' name='blockade_active' value='1'".($data['blockade_active'] == 1 ? " checked='checked'" : "")." />".$Sylvia->locale['sylvia_admin_04_006']."</label></td>\n";
				echo "</tr>\n<tr>\n";
				echo "<td align='center' colspan='2' class='tbl'>\n";
				echo "<input type='submit' name='update_blockade' value='".$Sylvia->locale['sylvia_admin_04_007']."' class='button' /></td>\n";
				echo "</tr>\n</table>\n</form>\n";
				closetable();
			}
		} else {
			redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_update_error6");
		}
	} elseif (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['blockade_id']) && isnum($_GET['blockade_id'])) {
		$result = dbquery("SELECT blockade_id, blockade_name FROM ".DB_SYLVIA_BLOCKADES." WHERE blockade_id='".$_GET['blockade_id']."'");
		if (dbrows($result) != 0) {
			$data = dbarray($result);
			
			if (isset($_POST['yes'])) {
				$result2 = dbquery("DELETE FROM ".DB_SYLVIA_BLOCKADES." WHERE blockade_id='".$_GET['blockade_id']."'");
				if ($result2) {
					redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_deleted");
				} else {
					redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_delete_error1");
				}
			} elseif (isset($_POST['no'])) {
				redirect(FUSION_SELF.$aidlink."&amp;page=blockades");
			} else {
				opentable($Sylvia->locale['sylvia_admin_00_000']."&nbsp".Sylvia::Version.$Sylvia->locale['sylvia_admin_00_001'].$Sylvia->locale['sylvia_admin_00_003'].$Sylvia->locale['sylvia_admin_00_001'].sprintf($Sylvia->locale['sylvia_admin_00_007'], $data['blockade_name']));
				echo $Sylvia->RenderNavi();
				echo "<form name='updateblockade' method='post' action='".FUSION_SELF.$aidlink."&amp;page=blockades&amp;action=delete&amp;blockade_id=".$data['blockade_id']."'>\n";
				echo "<div style='text-align:center;'>\n";
				echo sprintf($Sylvia->locale['sylvia_admin_05_000'], $data['blockade_name'])."<br /><br />\n";
				echo "<input type='submit' name='yes' value='".$Sylvia->locale['sylvia_admin_05_001']."' class='button' />&nbsp;<input type='submit' name='no' value='".$Sylvia->locale['sylvia_admin_05_002']."' class='button' />";
				echo "</div>\n</form>\n";
				closetable();
			}
		} else {
			redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_delete_error2");
		}
	} elseif (isset($_GET['action']) && $_GET['action'] == "enable" && isset($_GET['blockade_id']) && isnum($_GET['blockade_id'])) {
		$result = dbquery("SELECT blockade_id, blockade_name FROM ".DB_SYLVIA_BLOCKADES." WHERE blockade_id='".$_GET['blockade_id']."'");
		if (dbrows($result) != 0) {
			$data = dbarray($result);
			
			if (isset($_POST['yes'])) {
				$result2 = dbquery("UPDATE ".DB_SYLVIA_BLOCKADES." SET blockade_active='1' WHERE blockade_id='".$_GET['blockade_id']."'");
				if ($result2) {
					redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_enabled");
				} else {
					redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_enable_error1");
				}
			} elseif (isset($_POST['no'])) {
				redirect(FUSION_SELF.$aidlink."&amp;page=blockades");
			} else {
				opentable($Sylvia->locale['sylvia_admin_00_000']."&nbsp".Sylvia::Version.$Sylvia->locale['sylvia_admin_00_001'].$Sylvia->locale['sylvia_admin_00_003'].$Sylvia->locale['sylvia_admin_00_001'].sprintf($Sylvia->locale['sylvia_admin_00_008'], $data['blockade_name']));
				echo $Sylvia->RenderNavi();
				echo "<form name='enableblockade' method='post' action='".FUSION_SELF.$aidlink."&amp;page=blockades&amp;action=enable&amp;blockade_id=".$data['blockade_id']."'>\n";
				echo "<div style='text-align:center;'>\n";
				echo sprintf($Sylvia->locale['sylvia_admin_06_000'], $Sylvia->locale['sylvia_admin_06_001'], $data['blockade_name'])."<br /><br />\n";
				echo "<input type='submit' name='yes' value='".$Sylvia->locale['sylvia_admin_06_003']."' class='button' />&nbsp;<input type='submit' name='no' value='".$Sylvia->locale['sylvia_admin_06_004']."' class='button' />";
				echo "</div>\n</form>\n";
				closetable();
			}
		} else {
			redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_enable_error2");
		}
	} elseif (isset($_GET['action']) && $_GET['action'] == "disable" && isset($_GET['blockade_id']) && isnum($_GET['blockade_id'])) {
		$result = dbquery("SELECT blockade_id, blockade_name FROM ".DB_SYLVIA_BLOCKADES." WHERE blockade_id='".$_GET['blockade_id']."'");
		if (dbrows($result) != 0) {
			$data = dbarray($result);
			
			if (isset($_POST['yes'])) {
				$result2 = dbquery("UPDATE ".DB_SYLVIA_BLOCKADES." SET blockade_active='0' WHERE blockade_id='".$_GET['blockade_id']."'");
				if ($result2) {
					redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_disabled");
				} else {
					redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_disable_error1");
				}
			} elseif (isset($_POST['no'])) {
				redirect(FUSION_SELF.$aidlink."&amp;page=blockades");
			} else {
				opentable($Sylvia->locale['sylvia_admin_00_000']."&nbsp".Sylvia::Version.$Sylvia->locale['sylvia_admin_00_001'].$Sylvia->locale['sylvia_admin_00_003'].$Sylvia->locale['sylvia_admin_00_001'].sprintf($Sylvia->locale['sylvia_admin_00_009'], $data['blockade_name']));
				echo $Sylvia->RenderNavi();
				echo "<form name='disableblockade' method='post' action='".FUSION_SELF.$aidlink."&amp;page=blockades&amp;action=disable&amp;blockade_id=".$data['blockade_id']."'>\n";
				echo "<div style='text-align:center;'>\n";
				echo sprintf($Sylvia->locale['sylvia_admin_06_000'], $Sylvia->locale['sylvia_admin_06_002'], $data['blockade_name'])."<br /><br />\n";
				echo "<input type='submit' name='yes' value='".$Sylvia->locale['sylvia_admin_06_003']."' class='button' />&nbsp;<input type='submit' name='no' value='".$Sylvia->locale['sylvia_admin_06_004']."' class='button' />";
				echo "</div>\n</form>\n";
				closetable();
			}
		} else {
			redirect(FUSION_SELF.$aidlink."&amp;page=blockades&amp;status=blockade_disable_error2");
		}
	} else {
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
		
		opentable($Sylvia->locale['sylvia_admin_00_000']."&nbsp".Sylvia::Version.$Sylvia->locale['sylvia_admin_00_001'].$Sylvia->locale['sylvia_admin_00_003']);
		echo $Sylvia->RenderNavi();
		echo "<div style='font-weight:bold;text-align:center;margin:5px;'><a href='".FUSION_SELF.$aidlink."&amp;page=blockades&amp;action=create' title='".$Sylvia->locale['sylvia_admin_02_011']."'>".$Sylvia->locale['sylvia_admin_02_011']."</a></div>\n";
		echo "<table width='500' cellspacing='1' cellpadding='0' class='tbl-border center'>\n";
		$result = dbquery("SELECT blockade_id, blockade_name, blockade_text, blockade_code, blockade_data, blockade_active FROM ".DB_SYLVIA_BLOCKADES." ORDER BY blockade_name ASC LIMIT ".$_GET['rowstart'].",5");
		$rows = dbcount("(blockade_id)", DB_SYLVIA_BLOCKADES);
		if (dbrows($result) != 0) {
			$i = 0;
			echo "<tr>\n";
			echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'></td>\n";
			echo "<td class='tbl2'>".$Sylvia->locale['sylvia_admin_02_000']."</td>\n";
			echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'>".($Sylvia->settings['warnings_type'] == 1 ? $Sylvia->locale['sylvia_admin_02_003'] : $Sylvia->locale['sylvia_admin_02_015'])."</td>\n";
			echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$Sylvia->locale['sylvia_admin_02_004']."</td>\n";
			echo "</tr>\n";
			while ($data = dbarray($result)) {
				$cell_color = ($i % 2 == 0 ? "tbl1" : "tbl2");
				echo "<tr>\n";
				echo "<td align='center' width='1%' class='".$cell_color."' style='white-space:nowrap'>".($data['blockade_active'] == 1 ? "<span style='font-weight:bold;color:#009900;'>[A]</span>" : "<span style='font-weight:bold;color:#FF0000;'>[N]</span>")."</td>\n";
				echo "<td class='".$cell_color."' style='font-size:9px;'>\n";
				echo "<strong>".$Sylvia->locale['sylvia_admin_02_001'].":</strong>&nbsp;".$data['blockade_name'];
				echo "<br />\n";
				echo "<strong>".$Sylvia->locale['sylvia_admin_02_002'].":</strong>&nbsp;".$data['blockade_code'];
				echo "<br />\n";
				echo "<strong>".$Sylvia->locale['sylvia_admin_02_007'].":</strong>&nbsp;".(strlen($data['blockade_text']) > 35 ? "<a href='#mydiv_".$data['blockade_id']."' rel='facebox'>".trimlink($data['blockade_text'], 35)."</a>" : $data['blockade_text']);
				if (strlen($data['blockade_text']) > 35) {
					echo "<div id='mydiv_".$data['blockade_id']."' style='display:none'>".$data['blockade_text']."</div>";
				}
				echo "</td>\n";
				echo "<td align='center' width='1%' class='".$cell_color."' style='white-space:nowrap'>".number_format($data['blockade_data'], 0).($Sylvia->settings['warnings_type'] == 1 ? "" : "%")."</td>\n";
				echo "<td align='center' width='1%' class='".$cell_color."' style='white-space:nowrap'>\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;page=blockades&amp;action=edit&amp;blockade_id=".$data['blockade_id']."'>".$Sylvia->locale['sylvia_admin_02_005']."</a> -\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;page=blockades&amp;action=delete&amp;blockade_id=".$data['blockade_id']."'>".$Sylvia->locale['sylvia_admin_02_006']."</a><br />\n";
				if ($data['blockade_active'] == 1) {
					echo "<a href='".FUSION_SELF.$aidlink."&amp;page=blockades&amp;action=disable&amp;blockade_id=".$data['blockade_id']."'>".$Sylvia->locale['sylvia_admin_02_014']."</a>\n";
				} elseif ($data['blockade_active'] == 0) {
					echo "<a href='".FUSION_SELF.$aidlink."&amp;page=blockades&amp;action=enable&amp;blockade_id=".$data['blockade_id']."'>".$Sylvia->locale['sylvia_admin_02_013']."</a>\n";
				}
				echo "</td>\n";
				echo "</tr>\n";
				$i++;
			}
			echo "</table>\n";
		} else {
			echo "<tr><td align='center' class='tbl1'>".$Sylvia->locale['sylvia_admin_02_010']."</td></tr>\n</table>\n";
		}
		if ($rows > 5) echo "<div align='center' style=';margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 5, $rows, 3, $aidlink."&amp;page=blockades&amp;")."\n</div>\n";
		echo "<div style='font-size:9px;text-align:center;margin:5px;'><strong>".$Sylvia->locale['sylvia_admin_02_012']."</strong><span style='color:#009900;'>[A]</span>&nbsp;".$Sylvia->locale['sylvia_admin_02_008']." | <span style='color:#FF0000;'>[N]</span>&nbsp;".$Sylvia->locale['sylvia_admin_02_009']."</div>\n";
		closetable();
	}
} elseif (isset($_GET['page']) && $_GET['page'] == "defined") {
	if (isset($_GET['action']) && $_GET['action'] == "create" && !isset($_GET['warn_id'])) {
		require_once INCLUDES."bbcode_include.php";
		
		if (isset($_POST['add_warn'])) {
			$warn_contents = isset($_POST['warn_contents']) ? trim(stripinput($_POST['warn_contents'])) : "";
			$warn_message = isset($_POST['warn_message']) ? trim(stripinput($_POST['warn_message'])) : "";
			$warn_post_info = isset($_POST['warn_post_info']) ? trim(stripinput($_POST['warn_post_info'])) : "";
			
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
			
			if (!empty($warn_contents)) {
				if ($Sylvia->settings['warnings_type'] == 1 || ($Sylvia->settings['warnings_type'] == 2 && $warn_data != 0)) {
					if ($Sylvia->settings['pointx_connected'] == 0 || ($Sylvia->settings['pointx_connected'] == 1 && $warn_points != 0)) {
						$result = dbquery("INSERT INTO ".DB_SYLVIA_DEFINED." (warn_contents, warn_message, warn_post_info, warn_points, warn_data) VALUES ('".$warn_contents."', '".$warn_message."', '".$warn_post_info."', '".$warn_points."', '".$warn_data."')");
						if ($result) {
							redirect(FUSION_SELF.$aidlink."&amp;page=defined&amp;status=defined_warn_added");
						} else {
							redirect(FUSION_SELF.$aidlink."&amp;page=defined&amp;status=defined_warn_add_error1");
						}
					} else {
						redirect(FUSION_SELF.$aidlink."&amp;page=defined&amp;status=defined_warn_add_error2");
					}
				} else {
					redirect(FUSION_SELF.$aidlink."&amp;page=defined&amp;status=defined_warn_add_error3");
				}
			} else {
				redirect(FUSION_SELF.$aidlink."&amp;page=defined&amp;status=defined_warn_add_error4");
			}
		} else {
			opentable($Sylvia->locale['sylvia_admin_00_000']."&nbsp".Sylvia::Version.$Sylvia->locale['sylvia_admin_00_001'].$Sylvia->locale['sylvia_admin_00_011']);
			echo $Sylvia->RenderNavi();
			echo "<form name='addwarn' method='post' action='".FUSION_SELF.$aidlink."&amp;page=".$_GET['page']."&amp;action=".$_GET['action']."'>\n";
			echo "<table cellpadding='0' cellspacing='0' width='400' class='center'>\n<tr>\n";
			echo "<td colspan='2' class='tbl'>".$Sylvia->locale['sylvia_admin_09_000'].":<br />\n";
			echo "<textarea type='text' name='warn_contents' cols='70' rows='4' class='textbox' style='width:98%'></textarea><br />\n";
			echo display_bbcodes("95%;", "warn_contents", "addwarn", "b|i|u|color|url|center|size|big|small")."</td>\n";
			echo "</tr>\n<tr>\n";
			if ($Sylvia->settings['warnings_private_message'] == 1) {
				echo "<td colspan='2' class='tbl'>".$Sylvia->locale['sylvia_admin_09_001'].":<br />\n";
				echo "<textarea type='text' name='warn_message' cols='70' rows='4' class='textbox' style='width:98%'></textarea><br />\n";
				echo "<span class='small'><strong>".$Sylvia->locale['sylvia_admin_08_008']."</strong>{WARN_MOD} - ".$Sylvia->locale['sylvia_admin_08_009']."</span><br />\n";
				echo display_bbcodes("95%;", "warn_message", "addwarn")."\n";
				echo "</td>\n";
				echo "</tr>\n<tr>\n";
			}
			if ($Sylvia->settings['warnings_forum_messages'] == 1) {
				echo "<td colspan='2' class='tbl'>".$Sylvia->locale['sylvia_admin_09_006'].":<br />\n";
				echo "<textarea type='text' name='warn_post_info' cols='70' rows='4' class='textbox' style='width:98%'></textarea><br />\n";
				echo display_bbcodes("95%;", "warn_post_info", "addwarn", "b|i|u|color|url|center|size|big|small")."\n";
				echo "</td>\n";
				echo "</tr>\n<tr>\n";
			}
			if ($Sylvia->settings['warnings_type'] == 2) {
				echo "<td align='center' width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_09_002'].":<br />\n";
				echo "<input type='text' name='warn_data' value='' maxlength='3' class='textbox' style='width:50px;' />%</td>\n";
			}
			if ($Sylvia->settings['pointx_connected'] == 1) {
				echo "<td align='center' class='tbl'>".$Sylvia->locale['sylvia_admin_09_003'].":<br />\n";
				echo "<input type='text' name='warn_points' value='' class='textbox' style='width:50px;' /></td>\n";
			}
			echo "</tr>\n<tr>\n";
			echo "<td colspan='2' width='50%' align='center' class='tbl'>\n";
			echo "<input type='submit' name='add_warn' value='".$Sylvia->locale['sylvia_admin_09_004']."' class='button' />\n";
			echo "</td>\n";
			echo "</tr>\n</table>\n</form>\n";
			closetable();
		}
	} elseif (isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['warn_id']) && isnum($_GET['warn_id'])) {
		require_once INCLUDES."bbcode_include.php";
		
		$result = dbquery("SELECT warn_id, warn_contents, warn_message, warn_post_info, warn_points, warn_data FROM ".DB_SYLVIA_DEFINED." WHERE warn_id='".$_GET['warn_id']."'");
		if (dbrows($result) != 0) {
			$data = dbarray($result);
			
			if (isset($_POST['update_warn'])) {
				$warn_contents = isset($_POST['warn_contents']) ? trim(stripinput($_POST['warn_contents'])) : "";
				$warn_message = isset($_POST['warn_message']) ? trim(stripinput($_POST['warn_message'])) : "";
				$warn_post_info = isset($_POST['warn_post_info']) ? trim(stripinput($_POST['warn_post_info'])) : "";
				
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
				
				if (!empty($warn_contents)) {
					if ($Sylvia->settings['warnings_type'] == 1 || ($Sylvia->settings['warnings_type'] == 2 && $warn_data != 0)) {
						if ($Sylvia->settings['pointx_connected'] == 0 || ($Sylvia->settings['pointx_connected'] == 1 && $warn_points != 0)) {
							$result2 = dbquery("UPDATE ".DB_SYLVIA_DEFINED." SET warn_contents='".$warn_contents."', warn_message='".$warn_message."', warn_post_info='".$warn_post_info."', warn_points='".$warn_points."', warn_data='".$warn_data."' WHERE warn_id='".$data['warn_id']."'");
							if ($result2) {
								redirect(FUSION_SELF.$aidlink."&amp;page=defined&amp;status=defined_warn_updated");
							} else {
								redirect(FUSION_SELF.$aidlink."&amp;page=defined&amp;status=defined_warn_update_error1");
							}
						} else {
							redirect(FUSION_SELF.$aidlink."&amp;page=defined&amp;status=defined_warn_update_error2");
						}
					} else {
						redirect(FUSION_SELF.$aidlink."&amp;page=defined&amp;status=defined_warn_update_error3");
					}
				} else {
					redirect(FUSION_SELF.$aidlink."&amp;page=defined&amp;status=defined_warn_update_error4");
				}
			} else {
				opentable($Sylvia->locale['sylvia_admin_00_000']."&nbsp".Sylvia::Version.$Sylvia->locale['sylvia_admin_00_001'].$Sylvia->locale['sylvia_admin_00_012']);
				echo $Sylvia->RenderNavi();
				echo "<form name='updatewarn' method='post' action='".FUSION_SELF.$aidlink."&amp;page=".$_GET['page']."&amp;action=".$_GET['action']."&amp;warn_id=".$_GET['warn_id']."'>\n";
				echo "<table cellpadding='0' cellspacing='0' width='400' class='center'>\n<tr>\n";
				echo "<td colspan='2' class='tbl'>".$Sylvia->locale['sylvia_admin_09_000'].":<br />\n";
				echo "<textarea type='text' name='warn_contents' cols='70' rows='4' class='textbox' style='width:98%'>".$data['warn_contents']."</textarea><br />\n";
				echo display_bbcodes("95%;", "warn_contents", "updatewarn", "b|i|u|color|url|center|size|big|small")."</td>\n";
				echo "</tr>\n<tr>\n";
				if ($Sylvia->settings['warnings_private_message'] == 1) {
					echo "<td colspan='2' class='tbl'>".$Sylvia->locale['sylvia_admin_09_001'].":<br />\n";
					echo "<textarea type='text' name='warn_message' cols='70' rows='4' class='textbox' style='width:98%'>".$data['warn_message']."</textarea><br />\n";
					echo display_bbcodes("95%;", "warn_message", "updatewarn")."</td>\n";
					echo "</tr>\n<tr>\n";
				}
				if ($Sylvia->settings['warnings_forum_messages'] == 1) {
					echo "<td colspan='2' class='tbl'>".$Sylvia->locale['sylvia_admin_09_006'].":<br />\n";
					echo "<textarea type='text' name='warn_post_info' cols='70' rows='4' class='textbox' style='width:98%'>".$data['warn_post_info']."</textarea><br />\n";
					echo display_bbcodes("95%;", "warn_post_info", "addwarn", "b|i|u|color|url|center|size|big|small")."\n";
					echo "</td>\n";
					echo "</tr>\n<tr>\n";
				}
				if ($Sylvia->settings['warnings_type'] == 2) {
					echo "<td align='center' width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_09_002'].":<br />\n";
					echo "<input type='text' name='warn_data' value='".$data['warn_data']."' maxlength='3' class='textbox' style='width:50px;' />%</td>\n";
				}
				if ($Sylvia->settings['pointx_connected'] == 1) {
					echo "<td align='center' class='tbl'>".$Sylvia->locale['sylvia_admin_09_003'].":<br />\n";
					echo "<input type='text' name='warn_points' value='".$data['warn_points']."' class='textbox' style='width:50px;' /></td>\n";
				}
				echo "</tr>\n<tr>\n";
				echo "<td colspan='2' width='50%' align='center' class='tbl'>\n";
				echo "<input type='submit' name='update_warn' value='".$Sylvia->locale['sylvia_admin_09_005']."' class='button' />\n";
				echo "</td>\n";
				echo "</tr>\n</table>\n</form>\n";
				closetable();
			}
		} else {
			redirect(FUSION_SELF.$aidlink."&amp;page=defined&amp;status=defined_warn_update_error5");
		}
	} elseif (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['warn_id']) && isnum($_GET['warn_id'])) {
		$check = dbcount("(warn_id)", DB_SYLVIA_DEFINED, "warn_id='".$_GET['warn_id']."'");
		if ($check == 1) {
			if (isset($_POST['yes'])) {
				$result = dbquery("DELETE FROM ".DB_SYLVIA_DEFINED." WHERE warn_id='".$_GET['warn_id']."'");
				if ($result) {
					redirect(FUSION_SELF.$aidlink."&amp;page=defined&amp;status=defined_warn_deleted");
				} else {
					redirect(FUSION_SELF.$aidlink."&amp;page=defined&amp;status=defined_warn_delete_error1");
				}
			} elseif (isset($_POST['no'])) {
				redirect(FUSION_SELF.$aidlink."&amp;page=defined");
			} else {
				opentable($Sylvia->locale['sylvia_admin_00_000']."&nbsp".Sylvia::Version.$Sylvia->locale['sylvia_admin_00_001'].$Sylvia->locale['sylvia_admin_00_012']);
				echo $Sylvia->RenderNavi();
				echo "<form name='addwarn' method='post' action='".FUSION_SELF.$aidlink."&amp;page=".$_GET['page']."&amp;action=".$_GET['action']."&amp;warn_id=".$_GET['warn_id']."'>\n";
				echo "<div style='text-align:center;'>\n";
				echo $Sylvia->locale['sylvia_admin_10_000']."<br /><br />";
				echo "<input type='submit' name='yes' value='".$Sylvia->locale['sylvia_admin_10_001']."' class='button' />&nbsp;<input type='submit' name='no' value='".$Sylvia->locale['sylvia_admin_10_002']."' class='button' />";
				echo "</div>\n</form>\n";
				closetable();
			}
		} else {
			redirect(FUSION_SELF.$aidlink."&amp;page=defined&amp;status=defined_warn_delete_error2");
		}
	} else {
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
		
		opentable($Sylvia->locale['sylvia_admin_00_000']."&nbsp".Sylvia::Version.$Sylvia->locale['sylvia_admin_00_001'].$Sylvia->locale['sylvia_admin_00_010']);
		echo $Sylvia->RenderNavi();
		echo "<div style='text-align:center;font-weight:bold;margin:5px;'><a href='".FUSION_SELF.$aidlink."&amp;page=defined&amp;action=create' title='".$Sylvia->locale['sylvia_admin_08_007']."'>".$Sylvia->locale['sylvia_admin_08_007']."</a></div>\n";
		echo "<table width='80%' cellspacing='1' cellpadding='0' class='tbl-border center'>\n";
		$result = dbquery("SELECT warn_id, warn_contents, warn_message, warn_points, warn_data FROM ".DB_SYLVIA_DEFINED." ORDER BY warn_id DESC LIMIT ".$_GET['rowstart'].",10");
		$rows = dbcount("(warn_id)", DB_SYLVIA_DEFINED);
		if (dbrows($result) != 0) {
			$i = 0;
			echo "<tr>\n";
			echo "<td class='tbl2'>".$Sylvia->locale['sylvia_admin_08_000']."</td>\n";
			if ($Sylvia->settings['warnings_type'] == 2) {
				echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$Sylvia->locale['sylvia_admin_08_002']."</td>\n";
			}
			if ($Sylvia->settings['pointx_connected'] == 1) {
				echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$Sylvia->locale['sylvia_admin_08_001']."</td>\n";
			}
			echo "<td align='center' width='1%' class='tbl2' style='white-space:nowrap'>".$Sylvia->locale['sylvia_admin_08_003']."</td>\n";
			echo "</tr>\n";
			while ($data = dbarray($result)) {
				$cell_color = ($i % 2 == 0 ? "tbl1" : "tbl2");
				echo "<tr>\n";
				echo "<td class='".$cell_color."'>".(strlen($data['warn_contents']) > 50 ? "<a href='#mydiv_".$data['warn_id']."' rel='facebox'>".trimlink(strip_tags(nl2br(parseubb($data['warn_contents']))), 50)."</a>" : nl2br(parseubb($data['warn_contents'])));
				if (strlen($data['warn_contents']) > 50) {
					echo "<div id='mydiv_".$data['warn_id']."' style='display:none'>".nl2br(parseubb($data['warn_contents']))."</div>";
				}
				echo "</td>\n";
				if ($Sylvia->settings['warnings_type'] == 2) {
					echo "<td align='center' width='1%' class='".$cell_color."' style='white-space:nowrap'>".$data['warn_data']."</td>\n";
				}
				if ($Sylvia->settings['pointx_connected'] == 1) {
					echo "<td align='center' width='1%' class='".$cell_color."' style='white-space:nowrap'>".$data['warn_points']."</td>\n";
				}
				echo "<td align='center' width='1%' class='".$cell_color."' style='white-space:nowrap'>\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;page=defined&amp;action=edit&amp;warn_id=".$data['warn_id']."'>".$Sylvia->locale['sylvia_admin_08_004']."</a> -\n";
				echo "<a href='".FUSION_SELF.$aidlink."&amp;page=defined&amp;action=delete&amp;warn_id=".$data['warn_id']."'>".$Sylvia->locale['sylvia_admin_08_005']."</a>\n";
				echo "</td>\n";
				echo "</tr>\n";
				$i++;
			}
			echo "</table>\n";
		} else {
			echo "<tr><td align='center' class='tbl1'>".$Sylvia->locale['sylvia_admin_08_006']."</td></tr>\n</table>\n";
		}
		if ($rows > 10) echo "<div align='center' style=';margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 5, $rows, 3, $aidlink."&amp;page=blockades&amp;")."\n</div>\n";
		closetable();
	}
} elseif (isset($_GET['page']) && $_GET['page'] == "settings") {
	if (isset($_GET['set_uf'])) {
		if (file_exists(INCLUDES."user_fields/user_sylvia_include.php") && file_exists(INCLUDES."user_fields/user_sylvia_include_var.php")) {
			if ($Sylvia->settings['warnings_list_uf'] == 1) {
				if (dbcount("(field_cat_id)", DB_USER_FIELD_CATS, "field_cat_name='".stripinput($Sylvia->locale['sylvia_core_01_006'])."'") == 0) {
					$uf_cat_order = dbresult(dbquery("SELECT MAX(field_cat_order) FROM ".DB_USER_FIELD_CATS.""), 0) + 1;
					$result = dbquery("INSERT INTO ".DB_USER_FIELD_CATS." (field_cat_name, field_cat_order) VALUES ('".stripinput($Sylvia->locale['sylvia_core_01_006'])."', '".$uf_cat_order."')");
					if ($result) {
						$uf_cat_result = dbquery("SELECT field_cat_id FROM ".DB_USER_FIELD_CATS." WHERE field_cat_name='".stripinput($Sylvia->locale['sylvia_core_01_006'])."'");
						$uf_cat_id = dbresult($uf_cat_result, 0);
						
						if (dbcount("(field_id)", DB_USER_FIELDS, "field_name='user_sylvia'") == 0) {
							$field_order = dbresult(dbquery("SELECT MAX(field_order) FROM ".DB_USER_FIELDS." WHERE field_cat='".$field_cat."'"), 0) + 1;
							$result2 = dbquery("INSERT INTO ".DB_USER_FIELDS." (field_name, field_cat, field_required, field_log, field_registration, field_order) VALUES ('user_sylvia', '".$uf_cat_id."', '0', '0', '0', '".$field_order."')");
							redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=".($result2 ? "user_field_customized" : "user_field_customize_error"));
						} elseif (dbcount("(field_id)", DB_USER_FIELDS, "field_name='user_sylvia'") == 1) {
							$result3 = dbquery("UPDATE ".DB_USER_FIELDS." field_id='".$uf_cat_id."' WHERE field_name='user_sylvia'");
							redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=".($result3 ? "user_field_customized" : "user_field_customize_error"));
						}
					} else {
						redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=user_field_customize_error3");
					}
				} elseif (dbcount("(field_cat_id)", DB_USER_FIELD_CATS, "field_cat_name='".$Sylvia->locale['sylvia_core_01_006']."'") == 1) {
					$uf_cat_result = dbquery("SELECT field_cat_id FROM ".DB_USER_FIELD_CATS." WHERE field_cat_name='".stripinput($Sylvia->locale['sylvia_core_01_006'])."'");
					$uf_cat_id = dbresult($uf_cat_result, 0);
					
					if (dbcount("(field_id)", DB_USER_FIELDS, "field_name='user_sylvia'") == 0) {
						$field_order = dbresult(dbquery("SELECT MAX(field_order) FROM ".DB_USER_FIELDS." WHERE field_cat='".$uf_cat_id."'"), 0) + 1;
						$result2 = dbquery("INSERT INTO ".DB_USER_FIELDS." (field_name, field_cat, field_required, field_log, field_registration, field_order) VALUES ('user_sylvia', '".$uf_cat_id."', '0', '0', '0', '".$field_order."')");
						redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=".($result2 ? "user_field_customized" : "user_field_customize_error"));
					} elseif (dbcount("(field_id)", DB_USER_FIELDS, "field_name='user_sylvia'") == 1) {
						$result3 = dbquery("UPDATE ".DB_USER_FIELDS." field_cat='".$uf_cat_id."' WHERE field_name='user_sylvia'");
						redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=".($result3 ? "user_field_customized" : "user_field_customize_error"));
					}
				} elseif (dbcount("(field_cat_id)", DB_USER_FIELD_CATS, "field_cat_name='".$Sylvia->locale['sylvia_core_01_006']."'") > 1) {
					redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=user_field_customize_error3");
				}
			} elseif ($Sylvia->settings['warnings_list_uf'] == 0) {
				if (dbcount("(field_cat_id)", DB_USER_FIELD_CATS, "field_cat_name='".stripinput($Sylvia->locale['sylvia_core_01_007'])."'") == 0) {
					redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=user_field_customize_error3");
				} elseif (dbcount("(field_cat_id)", DB_USER_FIELD_CATS, "field_cat_name='".stripinput($Sylvia->locale['sylvia_core_01_007'])."'") == 1) {
					$result = dbquery("SELECT field_cat_id FROM ".DB_USER_FIELD_CATS." WHERE field_cat_name='".stripinput($Sylvia->locale['sylvia_core_01_007'])."'");
					$uf_cat_id = dbresult($result, 0);
					
					if (dbcount("(field_id)", DB_USER_FIELDS, "field_name='user_sylvia'") == 0) {
						$field_order = dbresult(dbquery("SELECT MAX(field_order) FROM ".DB_USER_FIELDS." WHERE field_cat='".$uf_cat_id."'"), 0) + 1;
						$result2 = dbquery("INSERT INTO ".DB_USER_FIELDS." (field_name, field_cat, field_required, field_log, field_registration, field_order) VALUES ('user_sylvia', '".$uf_cat_id."', '0', '0', '0', '".$field_order."')");
						redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=".($result2 ? "user_field_customized" : "user_field_customize_error"));
					} elseif (dbcount("(field_id)", DB_USER_FIELDS, "field_name='user_sylvia'") == 1) {
						$result3 = dbquery("UPDATE ".DB_USER_FIELDS." field_cat='".$uf_cat_id."' WHERE field_name='user_sylvia'");
						redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=".($result3 ? "user_field_customized" : "user_field_customize_error"));
					}
				} elseif (dbcount("(field_cat_id)", DB_USER_FIELD_CATS, "field_cat_name='".stripinput($Sylvia->locale['sylvia_core_01_007'])."'") > 1) {
					redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=user_field_customize_error3");
				}
			}
		} else {
			redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=user_field_customize_error2");
		}
	}
	if (isset($_POST['save_settings'])) {
		include_once INCLUDES."infusions_include.php";
		
		$error = FALSE;
		$higher_settings = FALSE;
		
		if (isset($_POST['warning_update_info']) && ($_POST['warning_update_info'] == 1 || $_POST['warning_update_info'] == 0)) {
			$update = set_setting("warning_update_info", $_POST['warning_update_info'], "sylvia");
			if (!$update) $error = TRUE;
		}
		if (isset($_POST['warning_system_enabled']) && ($_POST['warning_system_enabled'] == 1 || $_POST['warning_system_enabled'] == 0)) {
			$update = set_setting("warning_system_enabled", $_POST['warning_system_enabled'], "sylvia");
			if (!$update) $error = TRUE;
		}
		if (isset($_POST['warnings_type']) && ($_POST['warnings_type'] == 2 || $_POST['warnings_type'] == 1)) {
			$update = set_setting("warnings_type", $_POST['warnings_type'], "sylvia");
			if (!$update) $error = TRUE;
		}
		if (isset($_POST['warnings_ban_enabled']) && ($_POST['warnings_ban_enabled'] == 1 || $_POST['warnings_ban_enabled'] == 0)) {
			$update = set_setting("warnings_ban_enabled", $_POST['warnings_ban_enabled'], "sylvia");
			if (!$update) $error = TRUE;
		}
		if (isset($_POST['warnings_ban_number']) && isnum($_POST['warnings_ban_number']) && strlen($_POST['warnings_ban_number']) <= 10) {
			$update = set_setting("warnings_ban_number", $_POST['warnings_ban_number'], "sylvia");
			if (!$update) $error = TRUE;
		}
		if (isset($_POST['warnings_ban_time']) && isnum($_POST['warnings_ban_time']) && strlen($_POST['warnings_ban_time']) <= 5) {
			$update = set_setting("warnings_ban_time", $_POST['warnings_ban_time'], "sylvia");
			if (!$update) $error = TRUE;
		}
		if (isset($_POST['warnings_private_message']) && ($_POST['warnings_private_message'] == 1 || $_POST['warnings_private_message'] == 0)) {
			$update = set_setting("warnings_private_message", $_POST['warnings_private_message'], "sylvia");
			if (!$update) $error = TRUE;
		}
		if (isset($_POST['warnings_only_defined']) && ($_POST['warnings_only_defined'] == 1 || $_POST['warnings_only_defined'] == 0)) {
			$update = set_setting("warnings_only_defined", $_POST['warnings_only_defined'], "sylvia");
			if (!$update) $error = TRUE;
		}
		if (isset($_POST['warnings_list_uf']) && ($_POST['warnings_list_uf'] == 1 || $_POST['warnings_list_uf'] == 0)) {
			$update = set_setting("warnings_list_uf", $_POST['warnings_list_uf'], "sylvia");
			if (!$update) $error = TRUE;
		}
		if (isset($_POST['warnings_list_nof']) && isnum($_POST['warnings_list_nof'])) {
			$update = set_setting("warnings_list_nof", $_POST['warnings_list_nof'], "sylvia");
			if (!$update) $error = TRUE;
		}
		if (isset($_POST['warnings_moderator']) && isnum($_POST['warnings_moderator'])) {
			$update = set_setting("warnings_moderator", $_POST['warnings_moderator'], "sylvia");
			if (!$update) $error = TRUE;
		}
		if (isset($_POST['warnings_forum_info']) && ($_POST['warnings_forum_info'] == 1 || $_POST['warnings_forum_info'] == 0)) {
			$update = set_setting("warnings_forum_info", $_POST['warnings_forum_info'], "sylvia");
			if (!$update) $error = TRUE;
		}
		if (isset($_POST['warnings_forum_messages']) && ($_POST['warnings_forum_messages'] == 1 || $_POST['warnings_forum_messages'] == 0)) {
			$update = set_setting("warnings_forum_messages", $_POST['warnings_forum_messages'], "sylvia");
			if (!$update) $error = TRUE;
		}
		if (isset($_POST['pointx_connected']) && ($_POST['pointx_connected'] == 1 || $_POST['pointx_connected'] == 0)) {
			$update = set_setting("pointx_connected", $_POST['pointx_connected'], "sylvia");
			if (!$update) $error = TRUE;
		}
		if (isset($_POST['pointx_returning']) && ($_POST['pointx_returning'] == 1 || $_POST['pointx_returning'] == 0)) {
			$update = set_setting("pointx_returning", $_POST['pointx_returning'], "sylvia");
			if (!$update) $error = TRUE;
		}
		
		redirect(FUSION_SELF.$aidlink."&amp;page=settings&amp;status=".($error ? "settings_update_error" : "settings_updated"));
	} else {
		opentable($Sylvia->locale['sylvia_admin_00_000']."&nbsp".Sylvia::Version.$Sylvia->locale['sylvia_admin_00_001'].$Sylvia->locale['sylvia_admin_00_004']);
		echo $Sylvia->RenderNavi();
		echo "<form name='settings' method='post' action='".FUSION_SELF.$aidlink."&amp;page=settings'>\n";
		echo "<table cellpadding='0' cellspacing='0' width='500' class='center'>\n<tr>\n";
		echo "<td align='center' colspan='2' class='tbl2'><span class='small2'>".$Sylvia->locale['sylvia_admin_07_030']."</span></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_07_031']."</td>\n";
		echo "<td width='50%' class='tbl'><select name='warning_update_info' class='textbox'>\n";
		echo "<option value='1'".($Sylvia->settings['warning_update_info'] == "1" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_000']."</option>\n";
		echo "<option value='0'".($Sylvia->settings['warning_update_info'] == "0" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_001']."</option>\n";
		echo "</select></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td align='center' colspan='2' class='tbl2'><span class='small2'>".$Sylvia->locale['sylvia_admin_07_011']."</span></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_07_003']."</td>\n";
		echo "<td width='50%' class='tbl'><select name='warning_system_enabled' class='textbox'>\n";
		echo "<option value='1'".($Sylvia->settings['warning_system_enabled'] == "1" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_000']."</option>\n";
		echo "<option value='0'".($Sylvia->settings['warning_system_enabled'] == "0" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_001']."</option>\n";
		echo "</select></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_07_004']."</td>\n";
		echo "<td width='50%' class='tbl'><select name='warnings_type' class='textbox'>\n";
		echo "<option value='1'".($Sylvia->settings['warnings_type'] == "1" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_005']."</option>\n";
		echo "<option value='2'".($Sylvia->settings['warnings_type'] == "2" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_006']."</option>\n";
		echo "</select></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_07_007']."</td>\n";
		echo "<td width='50%' class='tbl'><select name='warnings_ban_enabled' class='textbox'>\n";
		echo "<option value='1'".($Sylvia->settings['warnings_ban_enabled'] == "1" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_000']."</option>\n";
		echo "<option value='0'".($Sylvia->settings['warnings_ban_enabled'] == "0" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_001']."</option>\n";
		echo "</select></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_07_017']."</td>\n";
		echo "<td width='50%' class='tbl'><select name='warnings_private_message' class='textbox'>\n";
		echo "<option value='1'".($Sylvia->settings['warnings_private_message'] == "1" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_000']."</option>\n";
		echo "<option value='0'".($Sylvia->settings['warnings_private_message'] == "0" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_001']."</option>\n";
		echo "</select></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_07_018']."</td>\n";
		echo "<td width='50%' class='tbl'><select name='warnings_only_defined' class='textbox'>\n";
		echo "<option value='0'".($Sylvia->settings['warnings_only_defined'] == "0" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_019']."</option>\n";
		echo "<option value='1'".($Sylvia->settings['warnings_only_defined'] == "1" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_020']."</option>\n";
		echo "</select></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_07_021']."</td>\n";
		echo "<td width='50%' class='tbl'><select name='warnings_moderator' class='textbox'>\n";
		$groups_arr = getusergroups(); $groups_except = array("0", "101");
		foreach ($groups_arr as $group) {
			if (!in_array($group[0], $groups_except)) {
				echo "<option value='".$group[0]."'".($Sylvia->settings['warnings_moderator'] == $group[0] ? " selected='selected'" : "").">".$group[1]."</option>\n";
			}
		}
		echo "</select></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_07_008']."</td>\n";
		echo "<td width='50%' class='tbl'><input type='text' name='warnings_ban_number' value='".$Sylvia->settings['warnings_ban_number']."' maxlength='10' class='textbox' style='width:50px;' autocomplete='off' /></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_07_009']."</td>\n";
		echo "<td width='50%' class='tbl'><input type='text' name='warnings_ban_time' value='".$Sylvia->settings['warnings_ban_time']."' maxlength='5' class='textbox' style='width:50px;' autocomplete='off' />&nbsp;".$Sylvia->locale['sylvia_admin_07_010']."</td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td align='center' colspan='2' class='tbl2'><span class='small2'>".$Sylvia->locale['sylvia_admin_07_029']."</span></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_07_028']."</td>\n";
		echo "<td width='50%' class='tbl'><select name='warnings_forum_info' class='textbox'>\n";
		echo "<option value='1'".($Sylvia->settings['warnings_forum_info'] == "1" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_000']."</option>\n";
		echo "<option value='0'".($Sylvia->settings['warnings_forum_info'] == "0" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_001']."</option>\n";
		echo "</select></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_07_027']."</td>\n";
		echo "<td width='50%' class='tbl'><select name='warnings_forum_messages' class='textbox'>\n";
		echo "<option value='1'".($Sylvia->settings['warnings_forum_messages'] == "1" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_000']."</option>\n";
		echo "<option value='0'".($Sylvia->settings['warnings_forum_messages'] == "0" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_001']."</option>\n";
		echo "</select></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td align='center' colspan='2' class='tbl2'><span class='small2'>".$Sylvia->locale['sylvia_admin_07_024']."</span></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_07_022']."</td>\n";
		echo "<td width='50%' class='tbl'><select name='warnings_list_uf' class='textbox'>\n";
		echo "<option value='1'".($Sylvia->settings['warnings_list_uf'] == "1" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_000']."</option>\n";
		echo "<option value='0'".($Sylvia->settings['warnings_list_uf'] == "0" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_001']."</option>\n";
		echo "</select></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_07_023']."</td>\n";
		echo "<td width='50%' class='tbl'><input type='text' name='warnings_list_nof' value='".$Sylvia->settings['warnings_list_nof']."' maxlength='10' class='textbox' style='width:50px;' autocomplete='off' /></td>\n";
		echo "</tr>\n<tr>\n";
		echo "<td colspan='2' style='text-align:center;' class='tbl'><a href='".FUSION_SELF.$aidlink."&amp;page=settings&amp;set_uf' title='".$Sylvia->locale['sylvia_admin_07_025']."'>".$Sylvia->locale['sylvia_admin_07_025']."</a></td>\n";
		echo "</tr>\n<tr>\n";
		if (dbcount("(inf_id)", DB_INFUSIONS, "inf_folder='pointx'") == 1 && file_exists(INFUSIONS."pointx/system/PointX.class.php")) {
			if ($Sylvia->px_version == FALSE) {
				echo "<td align='center' colspan='2' class='tbl2'><span class='small2'>".$Sylvia->locale['sylvia_admin_07_012']."</span></td>\n";
				echo "</tr>\n<tr>\n";
				echo "<td align='center' colspan='2' class='tbl'><span class='small'>".$Sylvia->locale['sylvia_admin_07_013']."</span></td>\n";
				echo "</tr>\n<tr>\n";
				echo "<td width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_07_014']."</td>\n";
				echo "<td width='50%' class='tbl'><select name='pointx_connected' class='textbox'>\n";
				echo "<option value='1'".($Sylvia->settings['pointx_connected'] == "1" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_000']."</option>\n";
				echo "<option value='0'".($Sylvia->settings['pointx_connected'] == "0" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_001']."</option>\n";
				echo "</select></td>\n";
				echo "</tr>\n<tr>\n";
				echo "<td width='50%' class='tbl'>".$Sylvia->locale['sylvia_admin_07_015']."</td>\n";
				echo "<td width='50%' class='tbl'><select name='pointx_returning' class='textbox'>\n";
				echo "<option value='1'".($Sylvia->settings['pointx_returning'] == "1" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_000']."</option>\n";
				echo "<option value='0'".($Sylvia->settings['pointx_returning'] == "0" ? " selected='selected'" : "").">".$Sylvia->locale['sylvia_admin_07_001']."</option>\n";
				echo "</select></td>\n";
				echo "</tr>\n<tr>\n";
			} elseif ($Sylvia->px_version == TRUE) {
				echo "<td align='center' colspan='2' class='tbl-error'><div class='small2' style=margin:5px;'>".$Sylvia->locale['sylvia_admin_07_016']."</div></td>\n";
				echo "</tr>\n<tr>\n";
			}
		}
		echo "<td align='center' colspan='2' class='tbl'><br />\n";
		echo "<input type='submit' name='save_settings' value='".$Sylvia->locale['sylvia_admin_07_002']."' class='button' /></td>\n";
		echo "</tr>\n</table>\n</form>\n";
		closetable();
	}
} elseif (isset($_GET['page']) && $_GET['page'] == "update") {
	if (isset($_POST['update_version'])) {
		copy("http://www.on-deck.eu/addons/sylvia/".$update->file, "update/update.zip");
		
		$Zip = new ZipArchive;
		$update_file = "update/update.zip";
		if ($Zip->open($update_file) !== TRUE) {
			$error = TRUE;
		} else {
			$error = FALSE;
			
			$Zip->extractTo(INFUSIONS);
			$Zip->close();
		}
		
		@unlink("update/update.zip");
		
		redirect(FUSION_SELF.$aidlink."&amp;page=main&amp;status=".(!$error ? "infusion_updated" : "infusion_update_error"));
	} else {
		opentable($Sylvia->locale['sylvia_admin_00_000']."&nbsp".Sylvia::Version.$Sylvia->locale['sylvia_admin_00_001'].$Sylvia->locale['sylvia_admin_00_013']);
		echo $Sylvia->RenderNavi();
		echo "<div style='text-align:center;'>\n";
		echo "<span style='color:#009900;font-weight:bold;'>".$Sylvia->locale['sylvia_admin_11_000']."</span><br />\n";
		echo sprintf($Sylvia->locale['sylvia_admin_11_001'], Sylvia::Version)."<br />";
		echo sprintf($Sylvia->locale['sylvia_admin_11_002'], $update->ver);
		if ($update->possible == "yes") {
			echo "<p>".$Sylvia->locale['sylvia_admin_11_003']."</p>";
			if (str_replace(".", "", $update->ver) - str_replace(".", "", Sylvia::Version) > 1) echo "<p>".sprintf($Sylvia->locale['sylvia_admin_11_004'], "<strong>", "</strong>")."</p>";
		} elseif ($update->possible == "no") {
			echo "<p>".$Sylvia->locale['sylvia_admin_11_005']."</p>";
		}
		echo "<form name='settings' method='post' action='".FUSION_SELF.$aidlink."&amp;page=update'>\n";
		if ($update->possible == "yes") {
			echo "<input type='submit' name='update_version' value='".$Sylvia->locale['sylvia_admin_11_006']."' class='button' />\n";
		}
		echo "</form>\n</div>\n";
		closetable();
	}
}

require_once THEMES."templates/footer.php";
?>