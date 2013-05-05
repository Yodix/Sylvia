<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright © 2002 - 2012 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blockade.php
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

if (isset($_GET['key'])) {
	$_GET['key'] = stripinput($_GET['key']);
	
	$result = dbquery("SELECT blockade_text FROM ".DB_SYLVIA_BLOCKADES." WHERE blockade_code='".$_GET['key']."'");
	if (dbrows($result) != 0) {
		$data = dbarray($result);
		opentable($Sylvia->locale['sylvia_blockade_00_000']);
		echo "<div style='font-size:15px;font-weight:bold;margin:5px;text-align:center;'>".$data['blockade_text']."</div>\n";
		closetable();
	} else {
		redirect(BASEDIR."index.php");
	}
} else {
	redirect(BASEDIR."index.php");
}

require_once THEMES."templates/footer.php";
?>