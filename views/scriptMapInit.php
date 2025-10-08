<script>
/* ==========================================================================
 * Map init (Kakao / Google) + initial load once
 * ========================================================================== */
(function(App){
	'use strict';

	function loadScript(src, cb, onerr){
		if (document.querySelector(`script[src="${src}"]`)) return;
		const s=document.createElement('script'); s.src=src; s.async=true; s.defer=true;
		if(cb) s.onload=cb; if(onerr) s.onerror=onerr; document.head.appendChild(s);
	}

	if (App.IS_DOMESTIC){
		if(!window.__KAKAO_SDK_LOADING){ window.__KAKAO_SDK_LOADING=true;

			loadScript(
				'https://dapi.kakao.com/v2/maps/sdk.js?appkey='+encodeURIComponent(App.KAKAO_KEY)+'&autoload=false&libraries=services,clusterer,drawing',
				function(){
					if (!document.getElementById('map')) return;
					kakao.maps.load(function(){
						const map = new kakao.maps.Map(document.getElementById('map'), {center:new kakao.maps.LatLng(37.5665,126.9780), level:5});
						window.__MAP__ = App.MAP = { provider:'kakao', map };
						if (window.__MAP__ && __MAP__.map && __MAP__.provider==='kakao' && window.kakao){
							kakao.maps.event.addListener(__MAP__.map, 'click', ()=> LayerManager.hideAll());
						}

						/* ✅ 최초 1회 로드: 지도 생성 직후 */
						if (!App.__INITIAL_SMART_LOADED){
							App.__INITIAL_SMART_LOADED = true;
							App.reloadByCategorySmart?.('');
						}

						// idle: 캐시 재렌더만(재요청 금지)
						kakao.maps.event.addListener(App.MAP.map,'idle', function(){
							App.setMapCenterAsRef?.();
							if (App.mqDesktop.matches && App.__rowsCachePC?.length) App.renderListPC(App.__rowsCachePC);
							if (!App.mqDesktop.matches && App.__rowsCacheMO?.length && App.$panel.is(':visible')) App.renderListMobile(App.__rowsCacheMO);
						});

						App.trySetUserLocation?.(ok=>{
							if(!ok) App.setMapCenterAsRef?.();
							if (App.mqDesktop.matches && App.__rowsCachePC.length) App.renderListPC(App.__rowsCachePC);
							if (!App.mqDesktop.matches && App.__rowsCacheMO.length && App.$panel.is(':visible')) App.renderListMobile(App.__rowsCacheMO);
						});

						if (App.mqDesktop.matches){ App.ensurePcAllTile?.(); App.loadPcCategories?.(); }

						/* ADD: 지도 relayout + 최근 바운즈 재적용 */
						function relayoutAndRefit(){
							if (!App.MAP || !App.MAP.map) return;
							try{
								if (App.MAP.provider==='kakao' && App.MAP.map.relayout){
									App.MAP.map.relayout();								 // 컨테이너 폭 반영
								}
								if (App.__lastRows && App.__lastRows.length){
									App.fitBoundsFor(App.__lastRows);			 // 최근 rows 기준으로 다시 fitBounds
								}
							}catch(_){}
						}

						/* ADD: 윈도우 리사이즈/회전 → 보정 */
						window.addEventListener('resize',					 ()=> setTimeout(relayoutAndRefit, 120));
						window.addEventListener('orientationchange',()=> setTimeout(relayoutAndRefit, 120));

						/* ADD: PC 좌측/우측 오프캔버스 토글 때 → 보정 */
						['pcMenuStore','pcPlaceDetail'].forEach(id=>{
							const el = document.getElementById(id);
							if(!el) return;
							el.addEventListener('shown.bs.offcanvas', ()=> setTimeout(relayoutAndRefit, 150));
							el.addEventListener('hidden.bs.offcanvas',()=> setTimeout(relayoutAndRefit, 150));
						});

					});
				},
				function(){ console.error('[Map] Kakao SDK load failed'); }
			);


		}
	} else {
		if(!window.__GOOGLE_SDK_LOADING){ window.__GOOGLE_SDK_LOADING=true;

			window.initGMap=function(){
				if (!document.getElementById('map')) return;
				const map=new google.maps.Map(document.getElementById('map'),{center:{lat:37.5665,lng:126.9780},zoom:14});
				window.__MAP__=App.MAP={provider:'google', map};
				if (window.__MAP__ && __MAP__.map && __MAP__.provider==='google' && window.google){
					__MAP__.map.addListener('click', ()=> LayerManager.hideAll());
				}
				if (!App.__INITIAL_SMART_LOADED){ App.__INITIAL_SMART_LOADED=true; App.reloadByCategorySmart?.(''); }

				App.trySetUserLocation?.(ok=>{
					if(!ok) App.setMapCenterAsRef?.();
					if (App.mqDesktop.matches && App.__rowsCachePC.length) App.renderListPC(App.__rowsCachePC);
					if (!App.mqDesktop.matches && App.__rowsCacheMO.length && App.$panel.is(':visible')) App.renderListMobile(App.__rowsCacheMO);
				});

				map.addListener('idle', function(){
					App.setMapCenterAsRef?.();
					if (App.mqDesktop.matches && App.__rowsCachePC.length) App.renderListPC(App.__rowsCachePC);
					if (!App.mqDesktop.matches && App.__rowsCacheMO.length && App.$panel.is(':visible')) App.renderListMobile(App.__rowsCacheMO);
				});

				if (App.mqDesktop.matches){ App.ensurePcAllTile?.(); App.loadPcCategories?.(); }

				/* ADD: 구글용 보정 */
				function relayoutAndRefit(){
					if (!App.MAP || !App.MAP.map) return;
					try{
					if (window.google && google.maps?.event){
						google.maps.event.trigger(App.MAP.map, 'resize'); // 컨테이너 폭 반영
					}
					if (App.__lastRows && App.__lastRows.length){
						App.fitBoundsFor(App.__lastRows);								 // 다시 fitBounds
					}
					}catch(_){}
				}

				window.addEventListener('resize',					 ()=> setTimeout(relayoutAndRefit, 120));
				window.addEventListener('orientationchange',()=> setTimeout(relayoutAndRefit, 120));
				['pcMenuStore','pcPlaceDetail'].forEach(id=>{
					const el = document.getElementById(id);
					if(!el) return;
					el.addEventListener('shown.bs.offcanvas', ()=> setTimeout(relayoutAndRefit, 150));
					el.addEventListener('hidden.bs.offcanvas',()=> setTimeout(relayoutAndRefit, 150));
				});

			};
			loadScript('https://maps.googleapis.com/maps/api/js?key='+encodeURIComponent(App.GOOGLE_KEY)+'&callback=initGMap', null, function(){ console.error('[Map] Google SDK load failed'); });
		}
	}

})(window.App||{});
</script>
