<script>
/* ==========================================================================
 * Smart loader (ALL / STAMP + category) with token guard
 * ========================================================================== */
(function(App){
	'use strict';

	/* 토큰: 마지막 요청만 화면에 반영 */
	App.LATEST_SMART_TOKEN = null;

	/* REPLACE: 전체 함수 교체 */
	App.reloadByCategorySmart = async function(code){
		const rev = App.VIEW_REV;								 // ❗ 여기서는 bump 금지
		const url = (App.VIEW_MODE === 'stamp')
			? ('/api/store/stamp' + (code ? ('?cat=' + encodeURIComponent(code)) : ''))
			: (code ? '/api/store/bycat/' + encodeURIComponent(code) : '/api/store/list');

		const token = `${Date.now()}|${App.VIEW_MODE}|${code||'all'}`;
		App.LATEST_SMART_TOKEN = token;

		console.log('[smart:req]', token, url);

		try{
			const rows = await (App.ApiGuard
				? App.ApiGuard.getJSON('smart:'+App.VIEW_MODE+':'+(code||'all'), url, {}, {retry:2, base:260, staleRev:rev})
				: $.getJSON(url));

			if (App.LATEST_SMART_TOKEN !== token){ console.info('[smart:skip]', token); return; }
			console.log('[smart:apply]', token, 'rows=', (rows||[]).length);

			if (App.mqDesktop.matches && $('#pcResultList').length){
				App.renderListPC(rows||[]);
			} else {
				App.renderListMobile(rows||[]);
				$('#resultPanel').toggle(!!rows && rows.length>0);
			}
			await App.renderMarkers(rows||[]);
		}catch(e){
			if (e && e.__stale) return;
			console.error('[reloadByCategorySmart] failed:', e);
			alert('목록을 가져오지 못했습니다.');
		}
	};

	/* (선택) 레거시 호환 */
	window.reloadByCategorySmart = App.reloadByCategorySmart;

})(window.App||{});

</script>
