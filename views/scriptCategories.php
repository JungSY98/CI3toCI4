<script>
/* ==========================================================================
 * Categories (mobile chips / PC grid)
 * ========================================================================== */
(function(App){
	'use strict';

	// 모바일 칩 HTML
	App.moChipHtml = function(c, active){
		const color=(c.color||'#12ac8a').trim();
		const icon = c.icon ? `<img src="${c.icon}" alt="${c.name}">` : ''; /* <span class="mochip__dot"></span> */
		var html = icon == '' ? 
		`<button class="mochip ${active?'active':''}" data-code="${c.code||''}" type="button" style="--chip-color:${color}"><span class="mochip__label">${c.name}</span></button>` :
		`<button class="mochip ${active?'active':''}" data-code="${c.code||''}" type="button" style="--chip-color:${color}"><span class="mochip__icon">${icon}</span><span class="mochip__label">${c.name}</span></button>`;
		return html
	};

	// 모바일 카테고리 로드
	App.loadMobileCategories = function(){
		if(!App.$moCatRow.length) return;
		$.getJSON('/api/category/list', function(rows){
			const cats=Array.isArray(rows)?rows:[]; App.$moCatRow.empty().append(App.moChipHtml({code:'',name:'ALL',icon:null},true));
			cats.forEach(c=>{
				const label=(App.IS_DOMESTIC?(c.name_en||c.name_kr):(c.name_en||c.name_kr))||(c.code||'').toUpperCase();
				App.$moCatRow.append(App.moChipHtml({code:(c.code||'').toLowerCase(), name:label, icon:c.icon, color:c.color}, false));
			});
		});
	};

	// 모바일 칩 클릭 → Smart 로더
	$(document).off('click','#moCatRow .mochip').on('click','#moCatRow .mochip', function(){
		$('#moCatRow .mochip').removeClass('active'); $(this).addClass('active');
		const code = String($(this).data('code')||'').toLowerCase();
		App.reloadByCategorySmart?.(code);
		$("#resultPanel").removeClass("d-none");
	});

	// PC ALL 타일 보장
	App.ensurePcAllTile = function(){
		if(!App.$pcCatGrid.length) return;
		if(!App.$pcCatGrid.find('[data-code=""]').length){
			App.$pcCatGrid.prepend(`<button class="catCard active" type="button" data-code="" style="--cat-color:#12ac8a"><span class="catIcon"><i class="fa fa-list" aria-hidden="true" style="color:#12ac8a"></i></span><span class="catLabel">ALL</span></button>`);
		} else { App.$pcCatGrid.find('[data-code=""]').addClass('active'); }
	};

	App.loadPcCategories = function(){
		if(!App.$pcCatGrid.length) return;
		$.getJSON('/api/category/list', function(rows){
			const cats=Array.isArray(rows)?rows:[];
			App.$pcCatGrid.find('.catCard[data-code!=""]').remove();

			cats.forEach(c=>{
				const code	= (c.code||'').toLowerCase();
				const label = (App.IS_DOMESTIC?(c.name_en||c.name_kr):(c.name_en||c.name_kr)) || code.toUpperCase();
				const color = (c.color||'#e2e8f0').trim();
				const icon	= c.icon ? `<img src="${c.icon}" alt="${label}">` : '';
				App.$pcCatGrid.append(
				`<button class="catCard" type="button" data-code="${code}" style="--cat-color:${color}">
					 <span class="catIcon">${icon}</span><span class="catLabel">${label}</span>
				 </button>`
				);
			});

			App.ensurePcAllTile();	 // ✅ 리스트 재요청 금지 (클릭 때만 Smart 호출)
		});
	};


/* REPLACE: PC 카테고리 클릭 핸들러 */
	$(document).off('click','#pcCatGrid .catCard').on('click','#pcCatGrid .catCard', function(){

		// 1) 비주얼 활성 토글
		$('#pcCatGrid .catCard').removeClass('active');
		$(this).addClass('active');

		// 2) code 추출
		const code = String($(this).data('code')||'').toLowerCase();

		// 3) (선택) 카테고리 전환을 '뷰 변경'으로 본다면 bump
		// App.bumpViewRev?.();

		// 4) ✅ 현재 VIEW_MODE(all | stamp)에 맞춰 자동 라우팅
		App.reloadByCategorySmart?.(code);
	});

	/* REPLACE: 모바일 칩 클릭 핸들러 */
	$(document).off('click','#moCatRow .mochip').on('click','#moCatRow .mochip', function(){
		$('#moCatRow .mochip').removeClass('active'); $(this).addClass('active');
		const code = String($(this).data('code')||'').toLowerCase();
		App.reloadByCategorySmart?.(code);
	});
	// Init
	App.loadMobileCategories?.();

	// Export (호환)
	window.ensurePcAllTile = App.ensurePcAllTile;
	window.loadPcCategories = App.loadPcCategories;

})(window.App||{});
</script>
