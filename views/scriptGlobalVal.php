<script>
/* ==========================================================================
 * Global namespace & server-injected values
 * ========================================================================== */
(function(){
	'use strict';

	// ---- Namespace -----------------------------------------------------------
	window.App = window.App || {};

	// ---- Server injected values ---------------------------------------------
	App.SHOULD_SHOW	 = <?= $should_show_splash ? 'true' : 'false'; ?>;
	App.SPLASH_TTL		= <?= (int)$splash_ttl; ?>;
	App.IS_LOGGED_IN	= <?= $is_logged_in ? 'true' : 'false'; ?>;
	App.IS_DOMESTIC	 = <?= $is_domestic ? 'true' : 'false'; ?>;
	App.KAKAO_KEY		 = <?= json_encode($kakao_key); ?>;
	App.GOOGLE_KEY		= <?= json_encode($google_key); ?>;
	App.LOGIN_URL		 = <?= json_encode($login_url); ?>;
	App.CSRF_NAME		 = <?= json_encode($csrf_name); ?>;
	App.CSRF_HASH		 = <?= json_encode($csrf_hash); ?>;
	App.__lastRows = [];

	// ---- View state ----------------------------------------------------------
	App.VIEW_MODE		 = window.VIEW_MODE || 'all';	// 'all' | 'stamp'
	App.VIEW_REV			= window.VIEW_REV	|| 0;			// bump되면 이전 요청은 stale

	// ---- Media/DOM cache -----------------------------------------------------
	App.mqDesktop		 = window.matchMedia('(min-width:1200px)');
	App.$panel				= $('#resultPanel');
	App.$list				 = $('#resultList');
	App.$pcList			 = $('#pcResultList');
	App.$pcCatGrid		= $('#pcCatGrid');
	App.$moCatRow		 = $('#moCatRow');

	// ---- Ajax CSRF -----------------------------------------------------------
	$.ajaxSetup({
		headers: { 'X-Requested-With': 'XMLHttpRequest' },
		beforeSend: function(_, s){
			if (s.type && s.type.toUpperCase()==='POST'){
				const pair = encodeURIComponent(App.CSRF_NAME)+'='+encodeURIComponent(App.CSRF_HASH);
				s.data = s.data ? (s.data+'&'+pair) : pair;
			}
		}
	});

	// ---- Login guard ---------------------------------------------------------
	App.requireLogin = function(next){
		if (App.IS_LOGGED_IN){ next && next(); return; }
		alert(App.IS_DOMESTIC ? '로그인이 필요합니다.' : 'Please log in first.');
		location.href = App.LOGIN_URL + '?redirect=' + encodeURIComponent(location.href);
	};

	// ---- Export (호환용) -----------------------------------------------------
	window.CSRF_NAME = App.CSRF_NAME;
	window.CSRF_HASH = App.CSRF_HASH;

	// Splash
	(function(){
		const now=Math.floor(Date.now()/1000);
		const last=parseInt((document.cookie.match('(^|;)\\s*sp_last_seen_ts\\s*=\\s*([^;]+)')||[])[2]||'0',10);
		if (!App.SHOULD_SHOW || (now-last)<App.SPLASH_TTL) return;
		$('#splashOverlay').fadeIn(120);
		setTimeout(function(){
			$('#splashOverlay').fadeOut(200);
			document.cookie='sp_last_seen_ts='+now+';path=/;SameSite=Lax';
			$.post('/ajax/splash-seen', {}, resp=>{ if(resp && resp[App.CSRF_NAME]) App.CSRF_HASH=resp[App.CSRF_NAME]; }, 'json');
		}, 3000);
	})();
})();
</script>
