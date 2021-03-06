<?php if(!defined('ABSPATH')) exit;?>
<div class="welcome-panel-content">
	<h3>코스모스팜 대시보드 입니다.</h3>
	<p class="about-description">최신버전 확인 및 운영관련 기능을 사용할 수 있습니다.</p>
	<div class="welcome-panel-column-container">
		<div class="welcome-panel-column">
			<h4>KBoard 버전</h4>
			<p>
				설치된 게시판 플러그인: <?=KBOARD_VERSION?> (최신: <?=$upgrader->getLatestVersion()->kboard?>)
				<?php if(KBOARD_VERSION < $upgrader->getLatestVersion()->kboard):?><br><a class="button" href="<?=KBOARD_UPGRADE_ACTION?>&action=kboard" onclick="return CF_oauthStatus(this.href);"><?=$upgrader->getLatestVersion()->kboard?> 버전으로 업그레이드</a> <a class="button" href="https://github.com/cosmosfarm/KBoard-wordpress-plugin/blob/master/plugins/kboard/history.md" onclick="window.open(this.href); return false;">히스토리</a><?php endif?>
			</p>
			<p>
				<?php if(defined('KBOARD_COMMNETS_VERSION')):?>
				설치된 댓글 플러그인: <?=KBOARD_COMMNETS_VERSION?> (최신: <?=$upgrader->getLatestVersion()->comments?>)
				<?php if(KBOARD_COMMNETS_VERSION < $upgrader->getLatestVersion()->comments):?><br><a class="button" href="<?=KBOARD_UPGRADE_ACTION?>&action=comments" onclick="return CF_oauthStatus(this.href);"><?=$upgrader->getLatestVersion()->comments?> 버전으로 업그레이드</a> <a class="button" href="https://github.com/cosmosfarm/KBoard-wordpress-plugin/blob/master/plugins/kboard-comments/history.md" onclick="window.open(this.href); return false;">히스토리</a><?php endif?>
				<?php else:?>
				<a href="http://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href); return false;">댓글 플러그인 홈페이지에서 다운로드</a><br>
				<a class="button" href="<?=KBOARD_UPGRADE_ACTION?>&action=comments" onclick="return CF_oauthStatus(this.href);"><?=$upgrader->getLatestVersion()->comments?> 버전으로 설치하기</a>
				<?php endif?>
			</p>
			<h4>KBoard 백업</h4>
			<ul>
				<li><a href="<?=KBOARD_BACKUP_ACTION?>" class="button">데이터 백업</a></li>
				<li><a href="<?=KBOARD_BACKUP_PAGE?>" class="button">데이터 복구</a></li>
			</ul>
		</div>
		<div class="welcome-panel-column">
			<h4>워드프레스 스토어</h4>
			<ul id="cf-wpstore-products">
				<li>등록된 KBoard 스킨이 없습니다.</li>
			</ul>
		</div>
		<div class="welcome-panel-column">
			<h4>코스모스팜 고객지원</h4>
			<ul>
				<li><a href="http://www.cosmosfarm.com/support" onclick="window.open(this.href); return false;">새로운 기능 및 오류 수정 기술지원 받기</a></li>
				<li><a href="http://www.cosmosfarm.com/threads" onclick="window.open(this.href); return false;">다른 사용자에게서 문제 해결 방법을 확인하기</a></li>
				<li><a href="http://blog.cosmosfarm.com/" onclick="window.open(this.href); return false;">최신 정보 및 새로운 사용법 알아보기</a></li>
			</ul>
			<h4>스토어 신규 등록</h4>
			<p>
				KBoard 스킨, 플러그인, 테마등 등록 접수 받습니다.<br>
				<a href="mailto:support@cosmosfarm.com" onclick="window.open(this.href); return false;" class="button">이메일로 등록 접수 및 제휴 문의하기</a>
			</p>
		</div>
	</div>
</div>

<script src="<?=plugins_url('cosmosfarm-apis.js', __FILE__)?>"></script>
<script>
COSMOSFARM.init('<?=KBOARD_WORDPRESS_APP_ID?>', '<?=$_SESSION['cosmosfarm_access_token']?>');
window.onload = function(){
	COSMOSFARM.getWpstoreProducts('kboard', 5, function(res){
		if(res.length > 0){
			var products = document.getElementById('cf-wpstore-products');
			products.innerHTML = '';
		}
		for(var i=0; i<res.length; i++){
			CF_addWpstoreProduct(res[i].title, res[i].created, res[i].link);
		}
	});
};
function CF_oauthStatus(upgrade_url){
	COSMOSFARM.oauthStatus(function(res){
		if(res.status == 'valid'){
			if(confirm('업그레이드전에 플러그인을 백업하세요. 모두 최신 파일로 교체됩니다. 계속 할까요?')){
				location.href = upgrade_url;
			}
		}
		else{
			if(confirm('access_token이 만료되어 재발급 받아야 합니다. 코스모스팜 홈페이지로 이동합니다.')){
				location.href = COSMOSFARM.getLoginUrl('<?=admin_url('/admin.php?page=kboard_dashboard')?>');
			}
		}
	}, function(res){
		if(confirm('업데이트를 진행 하시려면 코스모스팜에 로그인 해야 합니다. 코스모스팜 홈페이지로 이동합니다.')){
			location.href = COSMOSFARM.getLoginUrl('<?=admin_url('/admin.php?page=kboard_dashboard')?>');
		}
	});
	return false;
}
function CF_addWpstoreProduct(title, created, link){
	var products = document.getElementById('cf-wpstore-products');
	var a = document.createElement('a');
	a.innerHTML = title;
	a.setAttribute('href', link);
	a.onclick = function(){
		window.open(this.href); return false;
	}
	var li = document.createElement('li');
	li.appendChild(a);
	products.appendChild(li);
}
</script>