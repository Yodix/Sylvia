<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright © 2002 - 2012 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: SylviaForumGenerator.class.php
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

class SylviaForumGenerator {
	private $Sylvia;
	private $initializer;
	private $locale;
	
	private $threadID;
	
	private $usersArr = array();
	private $usersArrTmp = array();
	
	private $postsArr = array();
	private $postsArrTmp = array();
	
	public function __construct ($mainClass) {
		$this->Sylvia = $mainClass;
		
		if (isset($_GET['thread_id']) && isnum($_GET['thread_id'])) {
            $this->threadID = $_GET['thread_id'];
        } else if (isset($_GET['post_id']) && isnum($_GET['post_id'])) {
            $result = dbquery("SELECT thread_id FROM ".DB_POSTS." WHERE post_id='".$_GET['post_id']."'");
            if (!dbrows($result)) return;
            $this->threadID = dbresult($result, 0, 1);
        } else {
            return;
        }
		
		if (($this->Sylvia->settings['warnings_forum_info'] == 1 || $this->Sylvia->settings['warnings_forum_messages'] == 1) && !empty($this->threadID)) {
            $this->GetPostData();
        }
	}
	
	private function GetPostData () {
        if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
            if (isset($_GET['pid']) && isnum($_GET['pid'])) {
                $reply_count = dbcount("(post_id)", DB_POSTS, "thread_id='".$_GET['thread_id']."' AND post_id<='".$_GET['pid']."'");
                if ($reply_count > 20) { $_GET['rowstart'] = ((ceil($reply_count / "20")-1) * 20); } else { $_GET['rowstart'] = 0; }
            } else {
                $_GET['rowstart'] = 0;
            }
        }

        $result = dbquery("SELECT post_id, post_author FROM ".DB_POSTS." WHERE thread_id='".$this->threadID."' ORDER BY post_datestamp LIMIT ".$_GET['rowstart'].",20");
        while($data = dbarray($result)) {
            $this->usersArr[] = $data['post_author'];
            $this->postsArr[] = $data['post_id'];
        }
        $this->usersArrTmp = array_merge(array(""), $this->usersArr);
        $this->postsArrTmp = array_merge(array(""), $this->postsArr);
    }
	
	private function ForumInfoData () {
		$current_user_id = next($this->usersArrTmp);
		$current_post_id = next($this->postsArrTmp);
		
		$user_level = dbresult(dbquery("SELECT user_level FROM ".DB_USERS." WHERE user_id='".$current_user_id."'"), 0);
		$user_status = dbresult(dbquery("SELECT user_status FROM ".DB_USERS." WHERE user_id='".$current_user_id."'"), 0);
		if ($user_level == 101 && $user_status == 0) {
			return "<!--forum_thread_user_info--><span class='small'><strong><a href='".INFUSIONS."sylvia/sylvia.php?page=warnings&amp;user_id=".$current_user_id."' title='".$this->Sylvia->locale['sylvia_fg_00_001']."'>".$this->Sylvia->locale['sylvia_fg_00_000']."</a></strong> ".$this->Sylvia->GetWarnsStatus($current_user_id, TRUE)."</span>".(iSUPERADMIN || checkgroup($this->Sylvia->settings['warnings_moderator']) ? "&nbsp;<a href='".INFUSIONS."sylvia/sylvia.php?page=management&amp;action=add&amp;user_id=".$current_user_id."&amp;thread_id=".$this->threadID."&amp;post_id=".$current_post_id."' title='".$this->Sylvia->locale['sylvia_fg_00_002']."'><img src='".INFUSIONS."sylvia/images/add.png' height='9' width='9' alt='".$this->Sylvia->locale['sylvia_fg_00_002']."' style='border:0;vertical-align:center' /></a>" : "")."<br />\n";
		}
    }
	
	private function ForumInfoPost () {
		$current_post_id = next($this->postsArrTmp);
		$result = dbquery("SELECT message_id, message_mod, message_contents, message_datestamp FROM ".DB_SYLVIA_MESSAGES." WHERE message_post='".$current_post_id."' ORDER BY message_datestamp DESC");
		if (dbrows($result) != 0) {
			$messages = "<hr style='height : 1px; border : 1px dotted #eee;  width:50%;' align='left'>\n";
			$messages .= "<span style='font-size:9px;color:green'>\n";
			$messages .= "<strong>".$this->Sylvia->locale['sylvia_core_00_006']."</strong><br />\n<ol>\n";
			while ($data = dbarray($result)) {
				$mod_result = dbquery("SELECT user_id, user_name, user_status FROM ".DB_USERS." WHERE user_id='".$data['message_mod']."'");
				if (dbrows($mod_result) != 0) {
					$mod_data = dbarray($mod_result);
					$mod_data = profile_link($mod_data['user_id'], $mod_data['user_name'], $mod_data['user_status']);
				} else {
					$mod_data = $this->Sylvia->locale['sylvia_core_00_007'];
				}
				$messages .= "<li>".parseubb($data['message_contents'])." - ".$mod_data." ".showdate("%d/%m/%Y %H:%M", $data['message_datestamp']);
				if (checkgroup($this->Sylvia->settings['warnings_moderator']) == TRUE) {
					$messages .= "&nbsp;<a href='".INFUSIONS."sylvia/sylvia.php?page=forum_messages&amp;action=edit&amp;message_id=".$data['message_id']."' title='".$this->Sylvia->locale['sylvia_fg_00_003']."'><img src='".get_image("edit")."' alt='Edit' /></a>\n";
					$messages .= "&nbsp;<a href='".INFUSIONS."sylvia/sylvia.php?page=forum_messages&amp;action=delete&amp;message_id=".$data['message_id']."' title='".$this->Sylvia->locale['sylvia_fg_00_004']."'><img src='".get_image("no")."' alt='Delete' /></a>\n";
				}
				$messages .= "</li>\n";
			}
			$messages .= "</ol>\n</span>\n";
			$messages .= "<!--sub_forum_post_message-->\n";
			
			return $messages;
		} else {
			return "";
		}
    }
	
	private function AddToForumInfo () {
        reset($this->usersArrTmp);
        $this->initializer = preg_replace_callback("#<!--forum_thread_user_info-->#", array($this, "ForumInfoData"), $this->initializer);
    }
	
	private function AddToPostInfo () {
        reset($this->postsArrTmp);
        $this->initializer = preg_replace_callback("#<!--sub_forum_post_message-->#", array($this, "ForumInfoPost"), $this->initializer);
    }
	
	public function setInitializer($initializer) {
        $this->initializer = $initializer;
    }
	
	public function Execute () {
        if($this->Sylvia->settings['warnings_forum_info'] == 1) {
            $this->AddToForumInfo();
		}
		if($this->Sylvia->settings['warnings_forum_messages'] == 1) {
            $this->AddToPostInfo();
		}
		
        return $this->initializer;
    }
}
?>