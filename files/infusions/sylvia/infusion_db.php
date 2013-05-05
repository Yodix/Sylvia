<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright © 2002 - 2012 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusion_db.php
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

if (!defined("DB_SYLVIA_BLOCKADES")) {
	define("DB_SYLVIA_BLOCKADES", DB_PREFIX."sylvia_blockades");
}

if (!defined("DB_SYLVIA_DEFINED")) {
	define("DB_SYLVIA_DEFINED", DB_PREFIX."sylvia_defined");
}

if (!defined("DB_SYLVIA_MESSAGES")) {
	define("DB_SYLVIA_MESSAGES", DB_PREFIX."sylvia_messages");
}

if (!defined("DB_SYLVIA_WARNS")) {
	define("DB_SYLVIA_WARNS", DB_PREFIX."sylvia_warns");
}
?>