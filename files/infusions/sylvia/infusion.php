<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright © 2002 - 2012 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion.php
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

include INFUSIONS."sylvia/infusion_db.php";

if (file_exists(INFUSIONS."sylvia/locale/".$settings['locale'].".php")) {
	include INFUSIONS."sylvia/locale/".$settings['locale'].".php";
} else {
	include INFUSIONS."sylvia/locale/English.php";
}

$inf_title = $locale['sylvia_info_00_000'];
$inf_description = $locale['sylvia_info_00_001'];
$inf_version = "1.0.2";
$inf_developer = "Yodix";
$inf_email = "yodix@on-deck.eu";
$inf_weburl = "http://www.on-deck.eu";
$inf_folder = "sylvia";

$inf_newtable[1] = DB_SYLVIA_BLOCKADES." (
blockade_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
blockade_name VARCHAR(200) NOT NULL DEFAULT '',
blockade_text VARCHAR(255) NOT NULL DEFAULT '',
blockade_code VARCHAR(25) NOT NULL DEFAULT '',
blockade_data INT(10) UNSIGNED NOT NULL DEFAULT '0',
blockade_active TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
PRIMARY KEY (blockade_id)
) ENGINE=MyISAM;";

$inf_newtable[2] = DB_SYLVIA_DEFINED." (
warn_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
warn_contents TEXT NOT NULL,
warn_message TEXT NOT NULL,
warn_post_info TEXT NOT NULL,
warn_points INT(10) UNSIGNED NOT NULL DEFAULT '0',
warn_data INT(10) UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY (warn_id)
) ENGINE=MyISAM;";

$inf_newtable[3] = DB_SYLVIA_MESSAGES." (
message_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
message_post MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
message_mod MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
message_contents TEXT NOT NULL,
message_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY (message_id),
KEY message_datestamp (message_datestamp)
) ENGINE=MyISAM;";

$inf_newtable[4] = DB_SYLVIA_WARNS." (
warn_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
warn_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
warn_moderator MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
warn_contents TEXT NOT NULL,
warn_points INT(10) UNSIGNED NOT NULL DEFAULT '0',
warn_data INT(10) UNSIGNED NOT NULL DEFAULT '0',
warn_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY (warn_id),
KEY warn_datestamp (warn_datestamp)
) ENGINE=MyISAM;";

$inf_insertdbrow[1] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warning_system_enabled', '1', '".$inf_folder."')";
$inf_insertdbrow[2] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warning_update_info', '0', '".$inf_folder."')";
$inf_insertdbrow[3] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warnings_type', '1', '".$inf_folder."')";
$inf_insertdbrow[4] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warnings_ban_enabled', '1', '".$inf_folder."')";
$inf_insertdbrow[5] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warnings_ban_number', '5', '".$inf_folder."')";
$inf_insertdbrow[6] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warnings_ban_time', '0', '".$inf_folder."')";
$inf_insertdbrow[7] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warnings_forum_info', '1', '".$inf_folder."')";
$inf_insertdbrow[8] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warnings_forum_messages', '0', '".$inf_folder."')";
$inf_insertdbrow[9] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warnings_private_message', '1', '".$inf_folder."')";
$inf_insertdbrow[10] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warnings_only_defined', '0', '".$inf_folder."')";
$inf_insertdbrow[11] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warnings_moderator', '103', '".$inf_folder."')";
$inf_insertdbrow[12] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warnings_list_uf', '0', '".$inf_folder."')"; 
$inf_insertdbrow[13] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('warnings_list_nof', '5', '".$inf_folder."')"; 
$inf_insertdbrow[14] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('pointx_connected', '0', '".$inf_folder."')";
$inf_insertdbrow[15] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('pointx_returning', '0', '".$inf_folder."')";
$inf_insertdbrow[16] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('error_detector_rand', '', '".$inf_folder."')";
$inf_insertdbrow[17] = DB_SYLVIA_BLOCKADES." (blockade_name, blockade_text, blockade_code, blockade_data, blockade_active) VALUES ('".$locale['sylvia_info_00_005']."', '".$locale['sylvia_info_00_007']."', 'sk5PZ159l86ja3HQay195W2jF', '4', '1')";
$inf_insertdbrow[18] = DB_SYLVIA_BLOCKADES." (blockade_name, blockade_text, blockade_code, blockade_data, blockade_active) VALUES ('".$locale['sylvia_info_00_006']."', '".$locale['sylvia_info_00_009']."', 'SEcH7uxPTl91PhPh91R485591', '3', '1')";
$inf_insertdbrow[19] = DB_SYLVIA_BLOCKADES." (blockade_name, blockade_text, blockade_code, blockade_data, blockade_active) VALUES ('".$locale['sylvia_info_00_004']."', '".$locale['sylvia_info_00_008']."', 'Fh1Vc6884Z9ejwz24d2K6fI6P', '2', '1')";

$inf_droptable[1] = DB_SYLVIA_BLOCKADES;
$inf_droptable[2] = DB_SYLVIA_DEFINED;
$inf_droptable[3] = DB_SYLVIA_MESSAGES;
$inf_droptable[4] = DB_SYLVIA_WARNS;

$inf_deldbrow[1] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";

$inf_adminpanel[1] = array(
	"title" => $locale['sylvia_info_00_002'],
	"image" => "sylvia.gif",
	"panel" => "sylvia_admin.php",
	"rights" => "SVA"
);

$inf_sitelink[1] = array(
	"title" => $locale['sylvia_info_00_003'],
	"url" => "sylvia.php?page=warnings",
	"visibility" => "101"
);
?>