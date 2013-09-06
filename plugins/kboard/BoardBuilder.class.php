<?php
/**
 * KBoard 워드프레스 게시판 생성
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardBuilder {
	
	var $mod;
	var $board_id;
	var $uid;
	var $skin;
	var $category1;
	var $category2;
	var $rpp;
	var $url;
	var $board;
	
	private $meta;
	private $skin_path;
	
	public function __construct($board_id=''){
		$_GET['uid'] = intval($_GET['uid']);
		$_GET['pageid'] = intval($_GET['pageid']);
		$_GET['mod'] = kboard_xssfilter(kboard_htmlclear($_GET['mod']));
		$_GET['category1'] = kboard_xssfilter(kboard_htmlclear($_GET['category1']));
		$_GET['category2'] = kboard_xssfilter(kboard_htmlclear($_GET['category2']));
		$_GET['keyword'] = kboard_xssfilter(kboard_htmlclear($_GET['keyword']));
		$_GET['search'] = kboard_xssfilter(kboard_htmlclear($_GET['search']));
		
		$_POST['uid'] = intval($_POST['uid']);
		$_POST['mod'] = kboard_xssfilter(kboard_htmlclear($_POST['mod']));
		
		$uid = $_GET['uid']?$_GET['uid']:$_POST['uid'];
		$mod = $_GET['mod']?$_GET['mod']:$_POST['mod'];
		
		$this->mod = in_array($mod, array('list', 'document', 'editor', 'remove'))?$mod:'list';
		$this->category1 = $_GET['category1'];
		$this->category2 = $_GET['category2'];
		$this->uid = $uid;
		$this->skin = 'default';
		
		if($board_id) $this->setBoardID($board_id);
	}
	
	/**
	 * 게시판 뷰(View)를 설정한다. (List/Document/Editor/Remove)
	 * @param string $mod
	 */
	public function setMOD($mod){
		$this->mod = $mod;
	}
	
	/**
	 * 게시판 스킨을 설정한다.
	 * @param string $skin
	 */
	public function setSkin($skin){
		$this->skin = $skin;
	}
	
	/**
	 * 게시판 ID를 설정한다.
	 * @param int $board_id
	 */
	public function setBoardID($board_id){
		$this->board_id = $board_id;
	}
	
	/**
	 * 페이지당 게시물 숫자를 설정한다.
	 * @param int $rpp
	 */
	public function setRpp($rpp){
		$this->rpp = $rpp;
	}
	
	/**
	 * 게시판 실제 주소를 설정한다.
	 * @param string $url
	 */
	public function setURL($url){
		$this->url = $url;
	}
	
	/**
	 * 게시판 페이지를 생성하고 반환한다.
	 * @return string
	 */
	public function create(){
		$this->meta = new KBoardMeta($this->board_id);
		if($meta->pass_autop == 'enable'){
			call_user_func(array($this, 'builder'.ucfirst($this->mod)));
			return '';
		}
		else{
			ob_start();
			call_user_func(array($this, 'builder'.ucfirst($this->mod)));
			return ob_get_clean();
		}
	}
	
	/**
	 * 게시판 리스트 페이지를 생성한다.
	 */
	public function builderList(){
		global $user_ID;
		$userdata = get_userdata($user_ID);
		$url = new Url();
		
		$list = new ContentList($this->board_id);
		$list->category1($this->category1);
		$list->category2($this->category2);
		
		$list->rpp($this->rpp)->page($_GET['pageid'])->getList($_GET['keyword'], $_GET['search']);
		
		$skin_path = KBOARD_URL_PATH . "/skin/$this->skin";
		$board = $this->board;
		
		include KBOARD_DIR_PATH . "/skin/$this->skin/list.php";
	}
	
	/**
	 * 게시판 본문 페이지를 생성한다.
	 */
	public function builderDocument(){
		global $user_ID;
		$userdata = get_userdata($user_ID);
		$url = new Url();
		
		$content = new Content($this->board_id);
		$content->initWithUID($this->uid);
		
		$skin_path = KBOARD_URL_PATH . "/skin/$this->skin";
		$board = $this->board;
		
		if(!$this->board->isReader($content->member_uid, $content->secret) && $content->notice != 'true'){
			if($this->board->permission_write=='all'){
				if(!$this->board->isConfirm($content->password, $content->uid)){
					include KBOARD_DIR_PATH . "/skin/$this->skin/confirm.php";
				}
				else{
					$allow_document = true;
				}
			}
			else if(!$user_ID){
				die('<script>alert("로그인 하셔야 사용할 수 있습니다.");location.href="'.wp_login_url().'";</script>');
			}
			else{
				die('<script>alert("권한이 없습니다.");history.go(-1);</script>');
			}
		}
		else{
			$allow_document = true;
		}
		
		if($allow_document == true){
			$content->increaseView();
			$content->initWithUID($this->uid);
			
			// 에디터를 사용하지 않으면 자동으로 link를 생성한다.
			if($board->use_editor){
				$content->content = nl2br($content->content);
			}
			else{
				$content->content = nl2br(Content::autolink($content->content));
			}
			
			// 게시글 숏코드(Shortcode) 실행
			if($this->meta->shortcode_execute==1){
				$content->content = do_shortcode($content->content);
			}
			
			include KBOARD_DIR_PATH . "/skin/$this->skin/document.php";
		}
	}
	
	/**
	 * 게시판 에디터 페이지를 생성한다.
	 */
	public function builderEditor(){
		global $user_ID;
		$userdata = get_userdata($user_ID);
		$url = new Url();
		
		if($this->board->isWriter() && $this->board->permission_write=='all' && $_POST['title']){
			$next_url = $url->set('uid', $this->uid)->set('mod', 'editor')->toString();
			if(!$user_ID && !$_POST['password']) die('<script>alert("비밀번호를 입력해주세요.");location.href="'.$next_url.'";</script>');
		}
		
		$content = new Content($this->board_id);
		$content->initWithUID($this->uid);
		
		$skin_path = KBOARD_URL_PATH . "/skin/$this->skin";
		$board = $this->board;
		
		if(!$this->uid && !$this->board->isWriter()){
			die('<script>alert("권한이 없습니다.");history.go(-1);</script>');
		}
		else if($this->uid && !$this->board->isEditor($content->member_uid)){
			if($this->board->permission_write=='all'){
				if(!$this->board->isConfirm($content->password, $content->uid)){
					$confirm_view = true;
				}
			}
			else{
				die('<script>alert("권한이 없습니다.");history.go(-1);</script>');
			}
		}
		
		if($confirm_view){
			include KBOARD_DIR_PATH . "/skin/$this->skin/confirm.php";
		}
		else{
			$content->execute();
			$content->initWithUID($this->uid);
			
			if($this->uid){
				$next_url = $url->set('uid', $this->uid)->set('mod', 'document')->toString();
			}
			else{
				$next_url = $url->set('pageid', '')->toString();
			}
			
			// 내용이 없으면 등록된 기본 양식을 가져온다.
			if(!$content->content){
				$content->content = $this->meta->default_content;
			}
			
			include KBOARD_DIR_PATH . "/skin/$this->skin/editor.php";
		}
	}
	
	/**
	 * 게시물 삭제 페이지를 생성한다. (완료 후 바로 리다이렉션)
	 */
	public function builderRemove(){
		$url = new Url();
		
		$content = new Content($this->board_id);
		$content->initWithUID($this->uid);
		
		if(!$this->board->isEditor($content->member_uid)){
			if($this->board->permission_write=='all'){
				if(!$this->board->isConfirm($content->password, $content->uid)){
					$confirm_view = true;
				}
			}
			else{
				die('<script>alert("권한이 없습니다.");history.go(-1);</script>');
			}
		}
		
		if($confirm_view){
			$skin_path = KBOARD_URL_PATH . "/skin/$this->skin";
			$board = $this->board;
				
			include KBOARD_DIR_PATH . "/skin/$this->skin/confirm.php";
		}
		else{
			$content->remove($url->set('mod', 'list')->toString());
		}
	}
	
	/**
	 * 최신 게시물 리스트를 생성한다.
	 * @return string
	 */
	public function createLatest(){
		ob_start();
		
		$url = new Url();
		$list = new ContentList($this->board_id);
		$list->rpp($this->rpp)->getList();
		
		$skin_path = KBOARD_URL_PATH . "/skin/$this->skin";
		$board = $this->board;
		$board_url = $this->url;
		
		include KBOARD_DIR_PATH . "/skin/$this->skin/latest.php";
		return ob_get_clean();
	}
}
?>