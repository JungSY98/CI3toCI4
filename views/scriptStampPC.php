<script>
/* ==========================================================================
 * PC Stamp Detail Offcanvas (#pcStampDetail)
 *	- 모바일 #stampSheet 본문을 그대로 복사해 표시
 *	- 업체 상세(#pcPlaceDetail)와 독점적으로 교대 표시
 * ========================================================================== */
(function(App){
	'use strict';

	/* ADD: PC Stamp 오픈 함수 */
	function openPcStampDetail(){
		// 1) 소스(모바일 시트 본문) 확보
		const src = document.querySelector('#stampSheet .offcanvas-body');
		const $dst = $('#pcStampBody');
		if (!src || !$dst.length){
			console.warn('[pcStamp] source body not found');
			return;
		}

		// 2) 다른 패널 닫기 (업체 상세, 검색 패널 등)
		try { bootstrap.Offcanvas.getInstance(document.getElementById('pcPlaceDetail'))?.hide(); } catch(_){}
		try { $('#resultPanel').hide(); } catch(_){}

		// 3) 본문 채우기 (clone: 이미지/트랙 등 그대로)
		//$dst.html(src.innerHTML);

		// 4) 패널 열기 (backdrop:false)
		const inst = bootstrap.Offcanvas.getOrCreateInstance('#pcStampDetail', {backdrop:false, scroll:true});
		inst.show();
	}

	/* export */
	window.openPcStampDetail = openPcStampDetail;

	/* (선택) 오프캔버스 이벤트에서 지도 리사이즈 재보정 */
	const el = document.getElementById('pcStampDetail');
	if (el){
		const refit = ()=>{ try{
			if (App.MAP?.provider==='kakao' && App.MAP.map?.relayout){
				App.MAP.map.relayout();
				if (App.__lastRows?.length) App.fitBoundsFor(App.__lastRows);
			} else if (App.MAP?.provider==='google' && window.google){
				google.maps.event.trigger(App.MAP.map,'resize');
				if (App.__lastRows?.length) App.fitBoundsFor(App.__lastRows);
			}
		}catch(_){ }};
		el.addEventListener('shown.bs.offcanvas', ()=> setTimeout(refit, 150));
		el.addEventListener('hidden.bs.offcanvas', ()=> setTimeout(refit, 150));
	}

})(window.App||{});
</script>