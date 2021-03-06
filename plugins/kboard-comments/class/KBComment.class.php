<?php
/**
 * KBoard 워드프레스 게시판 댓글
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBComment {
	
	var $userdata;
	var $row;
	
	public function __construct(){
		global $user_ID;
		$this->row = new stdClass();
		$this->userdata = get_userdata($user_ID);
	}

	public function __get($name){
		return stripslashes($this->row->{$name});
	}
	
	/**
	 * 댓글 정보를 입력받아 초기화 한다.
	 * @param object $comment
	 * @return Comment
	 */
	public function initWithRow($comment){
		$this->row = $comment;
		return $this;
	}
	
	/**
	 * 관리 권한이 있는지 확인한다.
	 * @return boolean
	 */
	public function isEditor(){
		$resource = kboard_query("SELECT board_id FROM `".KBOARD_DB_PREFIX."kboard_board_content` WHERE uid='{$this->content_uid}'");
		list($board_id) = mysql_fetch_row($resource);
		$board = new KBoard($board_id);
		
		if($this->user_uid == $this->userdata->data->ID && $this->userdata->data->ID){
			// 본인인 경우
			return true;
		}
		else if($board->isAdmin()){
			// 게시판 관리자 허용
			return true;
		}
		else{
			return false;
		}
	}
}
?>