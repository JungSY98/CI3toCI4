<script>
/* ==========================================================================
 * Map relayout (stable) + hard unlock + view restore on breakpoint
 * ========================================================================== */
(function(){
	let lastSize={w:0,h:0}, stable=0, raf=0, t=0;

	function getCenter(){
		if (!window.__MAP__ || !__MAP__.map) return null;
		if (__MAP__.provider==='kakao' && window.kakao){
			const c=__MAP__.map.getCenter(); return {lat:c.getLat(), lng:c.getLng()};
		} else if(__MAP__.provider==='google' && window.google){
			const c=__MAP__.map.getCenter(); return {lat:c.lat(), lng:c.lng()};
		}
		return null;
	}
	function setCenter(c){
		if (!c || !window.__MAP__ || !__MAP__.map) return;
		if (__MAP__.provider==='kakao' && window.kakao){
			__MAP__.map.setCenter(new kakao.maps.LatLng(c.lat, c.lng));
		} else if(__MAP__.provider==='google' && window.google){
			__MAP__.map.setCenter({lat:c.lat, lng:c.lng});
		}
	}
	function relayout(){
		if (!window.__MAP__ || !__MAP__.map) return;
		const c=getCenter();
		if (__MAP__.provider==='kakao' && window.kakao){
			if (typeof __MAP__.map.relayout==='function') __MAP__.map.relayout();
			setCenter(c);
		} else if(__MAP__.provider==='google' && window.google){
			if (google && google.maps && google.maps.event) google.maps.event.trigger(__MAP__.map,'resize');
			setCenter(c);
		}
		if (window.__rowsCachePC && __rowsCachePC.length) { window.renderMarkers?.(window.__rowsCachePC); }
		else if (window.__rowsCacheMO && __rowsCacheMO.length){ window.renderMarkers?.(window.__rowsCacheMO); }
	}
	function relayoutStable(){
		cancelAnimationFrame(raf);
		raf=requestAnimationFrame(function tick(){
			const el=document.getElementById('map'); if(!el) return;
			const w=el.clientWidth,h=el.clientHeight;
			if (w===lastSize.w && h===lastSize.h){ stable++; } else { stable=0; lastSize={w,h}; }
			if (stable>=2){ relayout(); stable=0; return; }
			raf=requestAnimationFrame(tick);
		});
	}
	function relayoutDebounced(){ clearTimeout(t); t=setTimeout(relayoutStable,120); }
	function onModeChange(e){
		if (e.matches){ window.ensurePcAllTile?.(); window.loadPcCategories?.(); }

		// 현재 모드/선택 카테고리 유지
		const cv	 = window.CURRENT_VIEW || {type:'all', code:''};
		const code = (cv.type==='category') ? (cv.code||'') : '';

		window.reloadByCategorySmart && window.reloadByCategorySmart(code);

		setTimeout(()=> window.hardUnlockUI ? window.hardUnlockUI('modeChange') : null, 120);
	}
	// breakpoints
	const mq=window.matchMedia('(min-width:1200px)');
	(mq.addEventListener?mq.addEventListener('change',onMode):mq.addListener(onMode));
	function onMode(e){
		if (e.matches){ window.ensurePcAllTile?.(); window.loadPcCategories?.(); }
		// 보기 복원: 최신 Smart 로더만 사용
		window.reloadByCategorySmart?.('');
		setTimeout(()=> window.hardUnlockUI ? window.hardUnlockUI('modeChange') : null, 120);
	}

	window.addEventListener('resize', relayoutDebounced);
	window.addEventListener('orientationchange', relayoutDebounced);
	if (window.visualViewport){
		visualViewport.addEventListener('resize', relayoutDebounced);
		visualViewport.addEventListener('scroll', relayoutDebounced);
	}
	const mapEl=document.getElementById('map');
	if (window.ResizeObserver && mapEl){ const ro=new ResizeObserver(relayoutDebounced); ro.observe(mapEl); }

	document.addEventListener('DOMContentLoaded', ()=> setTimeout(relayoutStable,200));

	// Hard unlock
	window.hardUnlockUI = function(){
		try{
			document.querySelectorAll('.offcanvas-backdrop, .modal-backdrop').forEach(n=> n.remove());
			document.body.classList.remove('modal-open'); document.body.style.overflow=''; document.body.style.paddingRight='';
		}catch(_){}
		relayoutDebounced();
	};

})();
</script>
