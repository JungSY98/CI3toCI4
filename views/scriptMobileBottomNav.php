<script>
/* ==========================================================================
 * Mobile & PC bottom/top nav (map | stamp | store)
 * ========================================================================== */
(function(App){
	'use strict';

	function setActive(action){
		$('.mb-nav-item').removeClass('active').attr('aria-current','false');
		$(`.mb-nav-item[data-action="${action}"]`).addClass('active').attr('aria-current','page');

		$('.pc-nav-item').removeClass('active').attr('aria-current','false');
		$(`.pc-nav-item[data-action="${action}"]`).addClass('active').attr('aria-current','page');
	}
	function hideStampSheet(){
		try{ 
			bootstrap.Offcanvas.getInstance(document.getElementById('pcStampDetail'))?.hide();
			bootstrap.Offcanvas.getInstance(document.getElementById('stampSheet'))?.hide();
		}catch(_){}
	}
	function openStampSheetHalf(){
		const el=document.getElementById('stampSheet'); if(!el) return;
		el.classList.add('half'); el.style.height='50vh';
		bootstrap.Offcanvas.getOrCreateInstance(el,{backdrop:true,scroll:true}).show();
	}

	async function showAllShops(){
		setActive('map'); 
		//hideStampSheet();
		LayerManager.hideAll()
		App.VIEW_MODE='all';
		App.bumpViewRev();
		$('#resultPanel').fadeIn();
		$("#pcCatGrid").find("button.catCard").eq(0).click();
		$("#moCatRow").find("button.mochip").eq(0).click();
		await App.reloadByCategorySmart('');
	}
	async function showStampShops(){
		setActive('stamp');
		App.VIEW_MODE='stamp';
		App.bumpViewRev();
		await App.reloadByCategorySmart('');
		$("#moCatRow").find("button.mochip").eq(0).click();
		//openStampSheetHalf();
		LayerManager.openStampHalf();
	}
	async function showStorePanel(){
		setActive('store');
		hideStampSheet();
		if ($('#resultList li').length){
			//$('#resultPanel').show(); 
			LayerManager.showPanel();
			return;
		}
		//await showAllShops();
	}
	/* REPLACE: PC에서 스탬프 버튼 누를 때 */
	async function showStampShopsPC(){
		setActive('stamp');
		App.VIEW_MODE = 'stamp';
		App.bumpViewRev();
		$("#pcCatGrid").find("button.catCard").eq(0).click();
		await App.reloadByCategorySmart('');      // 스탬프 매장으로 리스트/마커 갱신
		openPcStampDetail();                      // ← 별도 패널로 표시
	}
	// 모바일 하단 메뉴
	$(document).off('click.mbnav','.mb-nav-item').on('click.mbnav','.mb-nav-item', function(){
		const act=$(this).data('action');
		if (act==='map')	 return showAllShops();
		if (act==='stamp') return showStampShops();
		if (act==='store') return showStorePanel();
	});

	// PC 상단 메뉴
	$(document).off('click.pcnav','.pc-nav-item').on('click.pcnav','.pc-nav-item', function(){
		const act=$(this).data('action');
		if (act==='map')	 return showAllShops();
		if (act==='stamp') return showStampShopsPC();
		if (act==='store') return showStorePanel();
	});

	setActive('map'); // 초기 상태

})(window.App||{});
</script>
