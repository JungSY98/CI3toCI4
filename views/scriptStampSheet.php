<script>
/* ==========================================================================
 * StampSheet drag-to-resize (Offcanvas) – half(50vh) ↔ full(90vh)
 *	- iOS/Safari에서도 동작하도록 touch-action / passive 옵션 처리
 *	- 외부에서 window.openStampSheetHalf() 호출해 반오픈
 * ========================================================================== */
(function(){
	'use strict';

	const el = document.getElementById('stampSheet');
	if (!el) return;

	const grip	 = el.querySelector('.offcanvas-header');
	const header = el.querySelector('.offcanvas-header');

	let dragging=false, startY=0, startH=0, rafId=0;

	const H_MIN	= Math.round(window.innerHeight * 0.40);
	const H_HALF = Math.round(window.innerHeight * 0.50);
	const H_FULL = Math.round(window.innerHeight * 0.90);
	const clamp	= v => Math.max(H_MIN, Math.min(H_FULL, v));

	function onStart(e){
		const t = e.touches ? e.touches[0] : e;
		dragging = true;
		startY	 = t.clientY;
		startH	 = el.getBoundingClientRect().height;
		el.classList.add('no-transition');
	}

	function onMove(e){
		if (!dragging) return;
		e.preventDefault(); // ★ 브라우저 스크롤 제스처 차단 (iOS 필수)
		const t	= e.touches ? e.touches[0] : e;
		const dy = startY - t.clientY;				// 위로 올리면 +
		const nh = clamp(startH + dy);
		cancelAnimationFrame(rafId);
		rafId = requestAnimationFrame(()=>{ el.style.height = nh + 'px'; });
	}

	function onEnd(){
		if (!dragging) return;
		dragging = false;
		el.classList.remove('no-transition');
		const h = el.getBoundingClientRect().height;
		const target = (h > (H_HALF + (H_FULL - H_HALF)/3)) ? H_FULL : H_HALF; // 스냅 임계치
		el.style.height = target + 'px';
		if (target === H_FULL){ el.classList.remove('half'); } else { el.classList.add('half'); }
	}

	// 드래그 시작/이동/종료 바인딩 (중복 바인딩 방지)
	[grip, header].forEach(n=>{
		if (!n) return;
		n.addEventListener('touchstart', onStart, {passive:true});
		n.addEventListener('touchmove' , onMove , {passive:false}); // ★ passive:false
		n.addEventListener('touchend'	, onEnd	, {passive:true});
		n.addEventListener('mousedown' , onStart);
	});
	window.addEventListener('mousemove', onMove);
	window.addEventListener('mouseup'	, onEnd);

	// 드래그 중 sheet 내부 스크롤 막기 (터치)
	el.addEventListener('touchmove', (e)=>{ if(dragging) e.preventDefault(); }, {passive:false});

	// 열릴 때 반오픈으로 시작
	el.addEventListener('shown.bs.offcanvas', ()=> {
		el.classList.add('half');
		el.style.height = H_HALF + 'px';
	});

	// 외부에서 반오픈 호출
	window.openStampSheetHalf = function(){
		const inst = bootstrap.Offcanvas.getOrCreateInstance(el, {backdrop:true, scroll:true});
		el.classList.add('half'); el.classList.remove('full');
		el.style.height = H_HALF + 'px';
		inst.show();
	};
})();
</script>
<script>
/* ===== Layer Manager: 한 번에 하나만 보이기 ===== */
(function(){
	const ids = {
		stamp:  'stampSheet',
		place:  'mPlaceSheet',
		panel:  'resultPanel',
		pcPlace:'pcPlaceDetail',
		pcStamp:'pcStampDetail'   // ★ 추가
	};

	function hideId(id){
		if (!id) return;
		if (id === ids.panel) {
			$('#'+ids.panel).hide(); return; 
		}
		const el = document.getElementById(id);
		if (!el) return;
		try {
			bootstrap.Offcanvas.getInstance(el)?.hide();
		} catch(_){}
	}

	window.LayerManager = {
		openStampHalf(){
			// 스탬프 열 땐 나머지 닫기
			hideId(ids.place);
			hideId(ids.panel);
			hideId(ids.pcPlace);
			hideId(ids.pcStamp);
			const el = document.getElementById(ids.stamp);
			if (!el) return;
			el.classList.add('half');
			el.style.height = '50vh';
			bootstrap.Offcanvas.getOrCreateInstance(el, {backdrop:true, scroll:true}).show();
		},
		openPlace(){
			// 매장 열 땐 스탬프/패널 닫기
			hideId(ids.stamp);
			hideId(ids.place);
			hideId(ids.pcPlace);
			hideId(ids.pcStamp);
			const el = document.getElementById(ids.place);
			if (!el) return;
			bootstrap.Offcanvas.getOrCreateInstance(el, {backdrop:true, scroll:true}).show();
		},
		showPanel(){
			// 패널 표시 시 시트 닫기
			hideId(ids.stamp);
			hideId(ids.place);
			hideId(ids.pcPlace);
			hideId(ids.pcStamp);
			$('#'+ids.panel).show();
		},
		hideAll(){
			hideId(ids.stamp);
			hideId(ids.place);
			hideId(ids.panel);
			hideId(ids.pcPlace);
			hideId(ids.pcStamp);
		},
		openPcStamp() {
			/* pcStamp 열 때 */
			hideId(ids.stamp);
			hideId(ids.place);
			hideId(ids.panel);
			hideId(ids.pcPlace);
			bootstrap.Offcanvas.getOrCreateInstance('#pcStampDetail',{backdrop:false,scroll:true}).show();
		}
	};

	/* 시트가 열릴 때 서로 배제되도록 보조 바인딩 */
	['stampSheet','mPlaceSheet'].forEach(id=>{
		const el = document.getElementById(id);
		if (!el) return;
		el.addEventListener('show.bs.offcanvas', function(){
			if (id==='stampSheet'){ hideId(ids.place); hideId(ids.panel); }
			if (id==='mPlaceSheet'){ hideId(ids.stamp); hideId(ids.panel); }
		});
	});
})();
</script>
<script>
/* ==========================================================================
 * BottomSheet Drag Util (Offcanvas) – half ↔ full
 *	- enableBottomSheetDrag('stampSheet', {half:0.5, full:0.9, min:0.4});
 *	- enableBottomSheetDrag('mPlaceSheet', {half:0.6, full:0.9, min:0.4});
 * ========================================================================== */
(function(){
	'use strict';

	function enableBottomSheetDrag(sheetId, opts){
		const el = document.getElementById(sheetId);
		if (!el) return;

		// 기본 값(화면 비율)
		const MIN	= typeof opts?.min	=== 'number' ? opts.min	: 0.40;
		const HALF = typeof opts?.half === 'number' ? opts.half : 0.50;
		const FULL = typeof opts?.full === 'number' ? opts.full : 0.90;

		// px로 환산
		const H_MIN	= Math.round(window.innerHeight * MIN);
		const H_HALF = Math.round(window.innerHeight * HALF);
		const H_FULL = Math.round(window.innerHeight * FULL);

		// 드래그 시작 영역: 그립바가 있으면 그립+헤더, 없으면 헤더만
		const grip	 = el.querySelector('.sheet-grip');
		const header = el.querySelector('.offcanvas-header');
		const dragNodes = [grip, header].filter(Boolean);

		let dragging = false, startY = 0, startH = 0, rafId = 0;

		const clamp = (v) => Math.max(H_MIN, Math.min(H_FULL, v));

		function onStart(e){
			const t = e.touches ? e.touches[0] : e;
			dragging = true;
			startY	 = t.clientY;
			startH	 = el.getBoundingClientRect().height;
			el.classList.add('no-transition');
		}

		function onMove(e){
			if (!dragging) return;
			e.preventDefault(); // ★ 브라우저 스크롤 제스처 차단 (특히 iOS)
			const t	= e.touches ? e.touches[0] : e;
			const dy = startY - t.clientY;										 // 위로 올리면 +
			const nh = clamp(startH + dy);
			cancelAnimationFrame(rafId);
			rafId = requestAnimationFrame(()=> { el.style.height = nh + 'px'; });
		}

		function onEnd(){
			if (!dragging) return;
			dragging = false;
			el.classList.remove('no-transition');

			const h = el.getBoundingClientRect().height;
			// 절반 기준 + 약간의 임계치로 스냅
			const snap = (h > (H_HALF + (H_FULL - H_HALF)/3)) ? H_FULL : H_HALF;
			el.style.height = snap + 'px';
			if (snap === H_FULL) el.classList.remove('half'); else el.classList.add('half');
		}

		// 바인딩(중복 방지)
		dragNodes.forEach(n=>{
			n.removeEventListener('touchstart', onStart);
			n.removeEventListener('touchmove',	onMove);
			n.removeEventListener('touchend',	 onEnd);
			n.removeEventListener('mousedown',	onStart);

			n.addEventListener('touchstart', onStart, {passive:true});
			n.addEventListener('touchmove',	onMove , {passive:false}); // ★
			n.addEventListener('touchend',	 onEnd	, {passive:true});
			n.addEventListener('mousedown',	onStart);
		});
		window.addEventListener('mousemove', onMove);
		window.addEventListener('mouseup',	 onEnd);

		// 드래그 중 내부 스크롤 방지(시트가 스크롤을 먹지 않게)
		el.addEventListener('touchmove', e=>{ if(dragging) e.preventDefault(); }, {passive:false});

		// 열릴 때 항상 half로 시작
		el.addEventListener('shown.bs.offcanvas', ()=> {
			el.classList.add('half');
			el.style.height = H_HALF + 'px';
		});

		// 외부에서 반오픈으로 열기
		const openHalfFnName = sheetId === 'stampSheet' ? 'openStampSheetHalf' : 'openPlaceSheetHalf';
		window[openHalfFnName] = function(){
			const inst = bootstrap.Offcanvas.getOrCreateInstance(el, {backdrop:true, scroll:true});
			el.classList.add('half'); el.classList.remove('full');
			el.style.height = H_HALF + 'px';
			inst.show();
		};
	}

	// === 실제 적용 ===
	// stampSheet: 50% 시작, 최대 90%
	enableBottomSheetDrag('stampSheet', {half:0.50, full:0.90, min:0.40});
	// mPlaceSheet: 60% 시작, 최대 90% (요청사항)
	enableBottomSheetDrag('mPlaceSheet', {half:0.60, full:0.90, min:0.40});

})();
</script>
