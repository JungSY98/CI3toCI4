<script>
/* ==========================================================================
 * PC Detail (right offcanvas)
 * ========================================================================== */
(function(App){
	'use strict';

	$(document).off('click','#pcResultList li').on('click','#pcResultList li', function(){
		const no=$(this).data('no'); if(!no) return; App.openPcDetail?.(no);
	});

	App.openPcDetail = function(no){
		App.requireLogin(function(){
			$.getJSON('/api/store/detail/'+no, function(r){
				const name = App.IS_DOMESTIC ? (r.sNameKR||r.sNameEN) : (r.sNameEN||r.sNameKR);
				const desc = App.IS_DOMESTIC ? (r.sDescKR||r.sDescEN) : (r.sDescEN||r.sDescKR);
				const hours= r.sOpenTime || '-';
				const addr = App.IS_DOMESTIC ? ([r.sAddr1KR,r.sAddr2KR,r.sAddr3KR].filter(Boolean).join(' '))
																		 : ([r.sAddr1EN,r.sAddr2EN,r.sAddr3EN].filter(Boolean).join(' '));
				$('#pcDetName').text(name||'-'); $('#pcDetDesc').text(desc||'-'); $('#pcDetHours').text(hours||'-'); $('#pcDetAddr').text((addr||'').trim()||'-');

				// 사진
				$.getJSON('/api/store/photos/'+no, function(photos){
					const list=Array.isArray(photos)?photos:[]; const $slides=$('#pcPhotoSlides').empty();
					if(!list.length){ const fb=r.sMainIMG_Source?('/uploads/store/'+r.sMainIMG_Source):'/uploads/store/noimg.jpg';
						$slides.append(`<div class="carousel-item active"><img src="${fb}" alt=""></div>`);
					} else { list.forEach((p,i)=> $slides.append(`<div class="carousel-item ${i===0?'active':''}"><img src="${p.url}" alt=""></div>`)); }
					App.initCarouselOnce('pcPhotoCarousel');
				});

				// 쿠폰(3장 캐러셀)
				const $slides=$('#pcCouponSlides').empty(), $dots=$('#pcCouponDots').empty();
				$.getJSON('/api/coupon/list/'+no, function(cs){
					const arr=Array.isArray(cs)?cs:[]; const chunk3=a=>{const out=[]; for(let i=0;i<a.length;i+=3) out.push(a.slice(i,i+3)); return out;};
					if(!arr.length){
						$slides.append(`<div class="carousel-item active"><div class="p-2 text-muted small"><?= $is_domestic?'상점 기본할인 사용 가능':'Use store default discount' ?></div></div>`);
						$dots.append(`<button type="button" data-bs-target="#pcCouponCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>`);
					} else {
						const groups=chunk3(arr);
						groups.forEach((group,gi)=>{
							const cards = group.map(c=>{
								const title=c.title||(App.IS_DOMESTIC?(c.title_kr||c.title_en):(c.title_en||c.title_kr))||'Coupon';
								const dcTxt=(c.dc_type&&c.dc_amount)?(c.dc_type==='%'?(c.dc_amount+'%'):(App.IS_DOMESTIC?(c.dc_amount+'원'):(c.dc_amount+' won'))):(App.IS_DOMESTIC?'상점 기본':'Default');
								const thumb=c.main_img?(`/uploads/coupon/${c.main_img}`):'/assets/images/noimg_4x3.png';
								const priceHtml=c.price?`<div class="coupon-price">${Number(c.price).toLocaleString()}원</div>`:'';
								return `<div><div class="coupon-square" style="background-image:url('${thumb}')" role="button" data-coupon="${c.coupon_no}" data-store="${no}" title="${title}"></div>
												<div class="coupon-meta"><div class="coupon-title">${title}</div><div class="coupon-sub">${App.IS_DOMESTIC?'할인':'DC'} ${dcTxt}</div>${priceHtml}</div></div>`;
							}).join('');
							$slides.append(`<div class="carousel-item ${gi===0?'active':''}"><div class="coupon-grid3">${cards}</div></div>`);
							$dots.append(`<button type="button" data-bs-target="#pcCouponCarousel" data-bs-slide-to="${gi}" class="${gi===0?'active':''}" ${gi===0?'aria-current="true"':''}></button>`);
						});
						const multi=(groups.length>1); $('#pcCouponCarousel .carousel-control-prev, #pcCouponCarousel .carousel-control-next').toggle(multi); $('#pcCouponDots').toggle(multi);
					}
					App.initCarouselOnce('pcCouponCarousel');
				});

				const det = bootstrap.Offcanvas.getOrCreateInstance('#pcPlaceDetail', {backdrop:false, scroll:true});
				det.show();
			});
		});
	};

	// Export
	window.openPcDetail = App.openPcDetail;

})(window.App||{});

/* ADD: PC Stamp 패널 렌더 */
function openPcStampPanel(){
	const srcBody = document.querySelector('#stampSheet .offcanvas-body');
	if (!srcBody){ console.warn('[stamp] source not found'); return; }

	// 제목
	$('#pcDetName').text('Stamp Tour Event');

	// 내용(모바일 시트 본문을 그대로 삽입)
	const html = `
		<div class="p-0 d-flex flex-column" style="height:100%; overflow:auto;">
			<div class="p-0">${srcBody.innerHTML}</div>
		</div>`;
	$('#pcPlaceDetail .offcanvas-body').html(html);

	// PC 오프캔버스는 backdrop:false (덮임 방지)
	const det = bootstrap.Offcanvas.getOrCreateInstance('#pcPlaceDetail', {backdrop:false, scroll:true});
	det.show();
}

/* (선택) export */
window.openPcStampPanel = openPcStampPanel;
// 정사각 보장 폴백(오프캔버스 열릴 때/리사이즈 시 다시 계산)
(function(){
	const supportsAR = (window.CSS && CSS.supports && CSS.supports('aspect-ratio: 4 / 3'));
	if (!supportsAR) document.body.classList.add('no-aspectratio');

	function squarePcPhoto(){
		if (supportsAR) return;								 // 최신 브라우저는 CSS로 끝
		const $inner = $('#pcPhotoCarouselWrap .carousel-inner');
		if (!$inner.length) return;
		// 패널 너비 = 정사각 높이
		const w = $inner.width();
		$inner.css('--pcPhotoH', w + 'px').css('height', w + 'px');
	}

	// 오프캔버스 open/resize 시 재계산
	$(window).on('resize', squarePcPhoto);
	const pd = document.getElementById('pcPlaceDetail');
	if (pd) {
		pd.addEventListener('shown.bs.offcanvas', squarePcPhoto);
		pd.addEventListener('hidden.bs.offcanvas', squarePcPhoto);
	}

	// 이미지/슬라이드 주입 직후에도 한번
	squarePcPhoto();
})();
</script>
