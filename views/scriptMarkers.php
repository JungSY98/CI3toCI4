<script>
/* ==========================================================================
 * Markers (buildMarkerIcon + renderMarkers + fitBounds/clear + click)
 * ========================================================================== */
(function(App){
	'use strict';

	App.MAP = window.__MAP__ || {};
	App.REF_POINT = App.REF_POINT || null;
	App.SORT_MODE_PC = App.SORT_MODE_PC || 'distance';
	App.__markers = App.__markers || [];	 // 모든 마커를 담는 전역 캐시
	App.__catIconMap	 = App.__catIconMap	 || null;	// code -> icon URL
	App.__catColorMap	= App.__catColorMap	|| null;	// code -> color HEX
	const iconCache = new Map();
	const loadImage = src => new Promise((res,rej)=>{ const img=new Image(); img.crossOrigin='anonymous'; img.onload=()=>res(img); img.onerror=rej; img.src=src; });

	// ---- buildMarkerIcon (canvas 합성) --------------------------------------
	App.buildMarkerIcon = async function(iconUrl, opt={}){
		const key = JSON.stringify({iconUrl, ...opt}); if(iconCache.has(key)) return iconCache.get(key);
		const size= +opt.size || 48, pinH=Math.round(size*1.35);
		const keyColor = App.cssVar('--key-green') || '#10A07E';
		const ringColor= opt.ringColor || keyColor;
		const pinColor = opt.pinColor	|| keyColor;
		const circle	 = opt.circleColor || '#fff';
		const ringThk	= (opt.ringThickness ?? 3);
		const gap			= (opt.gap ?? 6);
		const tailGap	= (opt.tailGap ?? 4);
		const tailW		= (opt.tailWidth ?? Math.round(size*0.28));

		const iconImg	= iconUrl ? await loadImage(iconUrl) : null;

		const canvas=document.createElement('canvas'); canvas.width=size; canvas.height=pinH;
		const ctx=canvas.getContext('2d');
		const cx=size/2, cy=size/2+2;
		const rOuter=size/2-1, rRingIn=rOuter-ringThk, rInner=Math.max(6,rRingIn-gap);

		// ring
		ctx.beginPath(); ctx.arc(cx,cy,rOuter,0,Math.PI*2); ctx.closePath(); ctx.fillStyle=ringColor; ctx.fill();
		// inner
		ctx.beginPath(); ctx.arc(cx,cy,rRingIn,0,Math.PI*2); ctx.closePath(); ctx.fillStyle=circle; ctx.fill();
		// tail
		const tailTopY=cy+rRingIn-tailGap, half=tailW/2, apexY=pinH-5;
		ctx.beginPath(); ctx.moveTo(cx-half, tailTopY); ctx.quadraticCurveTo(cx,apexY, cx+half,tailTopY); ctx.closePath(); ctx.fillStyle=pinColor; ctx.fill();

		// icon
		if(iconImg){
			const innerR=rInner-4, side=innerR*2;
			ctx.save(); ctx.beginPath(); ctx.arc(cx,cy,innerR,0,Math.PI*2); ctx.closePath(); ctx.clip();
			ctx.drawImage(iconImg, 0,0, iconImg.width,iconImg.height, cx-innerR, cy-innerR, side, side);
			ctx.restore();
		}
		const dataURL=canvas.toDataURL('image/png');

		const kakaoImg = (window.kakao && window.kakao.maps)
			? new kakao.maps.MarkerImage(dataURL, new kakao.maps.Size(size,pinH), {offset:new kakao.maps.Point(Math.round(size/2), pinH-6)})
			: null;

		const out={ dataURL, w:size, h:pinH, kakao:kakaoImg };
		iconCache.set(key,out); return out;
	};

	/* REPLACE: clearMarkers 정의 전체 */
	App.clearMarkers = function(){
		const list = App.__markers || [];
		if (!list.length) return;

		try{
			if (App.MAP && App.MAP.provider==='kakao' && window.kakao){
				list.forEach(m=> m && m.setMap && m.setMap(null));
			} else if (App.MAP && App.MAP.provider==='google' && window.google){
				list.forEach(m=> m && m.setMap && m.setMap(null));
			}
		}catch(_){ /* noop */ }

		App.__markers = [];	 // 비우기
	};

	/* REPLACE: fitBoundsFor 정의 전체 */
	App.fitBoundsFor = function(rows){
		if(!rows || !rows.length || !App.MAP || !App.MAP.map) return;

		if (App.MAP.provider==='kakao' && window.kakao){
			const b = new kakao.maps.LatLngBounds();
			rows.forEach(r=> b.extend(new kakao.maps.LatLng(+r.mapLat, +r.mapLng)));
			App.MAP.map.setBounds(b);
		} else if (App.MAP.provider==='google' && window.google){
			const b = new google.maps.LatLngBounds();
			rows.forEach(r=> b.extend(new google.maps.LatLng(+r.mapLat, +r.mapLng)));
			App.MAP.map.fitBounds(b);
		}
	};

	/* ADD: 행 → 카테고리 코드 추출(기존 추론 로직 재사용) */
	App.codeForRow = function(row){
		const code = String(row.cat_code||'').toLowerCase();
		if (code) return code;

		const s = String(row.category||'').toLowerCase();
		// 등록된 코드 키워드가 이름에 들어가면 그 코드로 추정
		if (App.__catIconMap){
		for (const k of Object.keys(App.__catIconMap)){
			if (k && s.includes(k)) return k;
		}
		}
		// 널리 쓰이는 예외(옵션): F&B/식음
		if (s.includes('f&b') || s.includes('식음')) return 'fnb';
		return '';
	};

	/* ADD: 행 → 색상 HEX (없으면 null) */
	App.colorForRow = function(row){
		const code = App.codeForRow(row);
		return (code && App.__catColorMap && App.__catColorMap[code]) || null;
	};

	App.iconForRow = function(row){
		const guess = s => {
			const v=(s||'').toLowerCase(); if(!App.__catIconMap) return null;
			for(const k of Object.keys(App.__catIconMap)){ if(k && v.includes(k)) return App.__catIconMap[k]; }
			if (v.includes('f&b') || v.includes('식음')) return App.__catIconMap['fnb']||null;
			return null;
		};
		const code=(row.cat_code||'').toLowerCase();
		return (App.__catIconMap && App.__catIconMap[code]) || guess(row.category);
	};

	/* REPLACE: 카테고리 메타 로더 (아이콘 + 색상 동시 적재) */
	App.loadCategoryIconMap = async function(){
		if (App.__catIconMap && App.__catColorMap) return App.__catIconMap;

		try{
			const rows = await (App.ApiGuard
				? App.ApiGuard.getJSON('cat:list','/api/category/list',{}, {retry:2, base:300})
				: $.getJSON('/api/category/list'));

			App.__catIconMap	= {};
			App.__catColorMap = {};

			(rows || []).forEach(c=>{
				const code = String(c.code||'').toLowerCase();
				if (!code) return;

				// 아이콘
				App.__catIconMap[code] = c.icon || null;

				// 색상: color / color_hex / hex / colorCode / color_code 등 다양한 필드 허용
				const raw = c.color || c.color_hex || c.hex || c.colorCode || c.color_code || '';
				if (typeof raw === 'string'){
					const m = raw.trim().match(/^#?[0-9a-fA-F]{6}$/);
					if (m){
						App.__catColorMap[code] = raw.startsWith('#') ? raw : ('#'+raw);
					}
				}
			});
		}catch(_){
			App.__catIconMap	= {};
			App.__catColorMap = {};
		}
		return App.__catIconMap;
	};

	App.handleMarkerClick = function(no){
		if (App.mqDesktop.matches) App.openPcDetail?.(no); else App.openMobileDetail?.(no);
		try{
			if (App.MAP.provider==='kakao' && App.MAP.map.setDraggable) App.MAP.map.setDraggable(true);
			if (App.MAP.provider==='google') App.MAP.map.setOptions({draggable:true, gestureHandling:'greedy'});
		}catch(_){}
	};

	/* REPLACE: App.renderMarkers(… ) 전체(핵심 부분만 표시) */
	App.renderMarkers = async function(rows){
		await App.loadCategoryIconMap?.();
		App.clearMarkers();										 // ← 반드시 App.clearMarkers()

		if(!rows || !rows.length) return;

		if (App.MAP.provider==='kakao' && window.kakao){
			for (const r of rows){
				//const ic	= await App.buildMarkerIcon(App.iconForRow(r), { size:48, ringThickness:4, gap:0, tailGap:0, tailWidth:14 });
				//const pos = new kakao.maps.LatLng(+r.mapLat, +r.mapLng);
				//const m	 = new kakao.maps.Marker({ map:App.MAP.map, position:pos, image:ic.kakao, title:(App.IS_DOMESTIC? r.sNameKR : r.sNameEN) });
				/* REPLACE: Kakao 마커 생성 루프 내부 */
				const col = App.colorForRow(r) || 'var(--key-green)'; // 분류 색상 or 기본색
				const ic	= await App.buildMarkerIcon(App.iconForRow(r), {
					size:48, ringThickness:4, gap:0, tailGap:0, tailWidth:14,
					ringColor: col,				// ← 테두리
					pinColor : col				 // ← 화살표/핀
				});
				const pos = new kakao.maps.LatLng(+r.mapLat, +r.mapLng);
				const m	 = new kakao.maps.Marker({ map:App.MAP.map, position:pos, image:ic.kakao,
													title:(App.IS_DOMESTIC? r.sNameKR:r.sNameEN) });
				kakao.maps.event.addListener(m,'click',()=> App.handleMarkerClick(r.no));
				App.__markers.push(m);							// ← 반드시 App.__markers에 push
			}
		} else if (App.MAP.provider==='google' && window.google){
			for (const r of rows){
				//const ic		= await App.buildMarkerIcon(App.iconForRow(r), { size:48, ringThickness:3, gap:0, tailGap:0, tailWidth:14 });
				//const gIcon = { url:ic.dataURL, scaledSize:new google.maps.Size(ic.w,ic.h), anchor:new google.maps.Point(Math.round(ic.w/2), ic.h-6) };
				//const m		 = new google.maps.Marker({ map:App.MAP.map, position:{lat:+r.mapLat, lng:+r.mapLng}, icon:gIcon, title:(App.IS_DOMESTIC? r.sNameKR : r.sNameEN) });
				/* REPLACE: Google 마커 생성 루프 내부 */
				const col	= App.colorForRow(r) || 'var(--key-green)';
				const ic	 = await App.buildMarkerIcon(App.iconForRow(r), {
					size:48, ringThickness:3, gap:0, tailGap:0, tailWidth:14,
					ringColor: col, pinColor: col
				});
				const gIcon = { url: ic.dataURL, scaledSize: new google.maps.Size(ic.w, ic.h),
								anchor: new google.maps.Point(Math.round(ic.w/2), ic.h-6) };
				const m = new google.maps.Marker({ map:App.MAP.map, position:{lat:+r.mapLat,lng:+r.mapLng},
													 icon:gIcon, title:(App.IS_DOMESTIC? r.sNameKR:r.sNameEN) });
				m.addListener('click',()=> App.handleMarkerClick(r.no));
				App.__markers.push(m);							// ← 여기도
			}
		}

		App.fitBoundsFor(rows);

		/* ADD: 최근 rows 저장 (리사이즈/오프캔버스 토글 후 재사용) */
		App.__lastRows = rows;


		/* ➌ PC에서 패널 폭 적용 등 레이아웃 안정 뒤 한 번 더 보정 */
		setTimeout(()=>{ 
		try{
			if (App.MAP.provider==='kakao' && App.MAP.map.relayout){
			App.MAP.map.relayout();
			App.fitBoundsFor(App.__lastRows);
			}else if(App.MAP.provider==='google' && window.google){
			google.maps.event.trigger(App.MAP.map,'resize');
			App.fitBoundsFor(App.__lastRows);
			}
		}catch(_){}
		}, 120);
	};
	// Export
	window.renderMarkers = App.renderMarkers;

})(window.App||{});
</script>
