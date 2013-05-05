<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright © 2002 - 2012 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: GetCurrentDir.func.php
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

function GetCurrentDir ($logic = FALSE) {
	$getcwd = getcwd();
	$getcwd = explode("/", $getcwd);
	$count = count($getcwd) - 1;
	$getcwd = $getcwd[$count];
	
	if ($logic) {
		if (getcwd() == TRUE) {
			return TRUE;
		} else {
			return FALSE;
		}
	} elseif (!$logic) {
		return $getcwd;
	}
}
?>