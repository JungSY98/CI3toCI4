<script>
/* ==========================================================================
 * Mobile Detail (bottom sheet 60% → drag)
 * ========================================================================== */
(function(App){
  'use strict';

  $(document).off('click','#resultList li').on('click','#resultList li', function(){
    const no=$(this).data('no'); if(!no) return; App.openMobileDetail?.(no); App.$panel.hide();
  });

  App.openMobileDetail = function(no){
    App.requireLogin(function(){
      $.getJSON('/api/store/detail/'+no, function(r){
        const name=App.IS_DOMESTIC?(r.sNameKR||r.sNameEN):(r.sNameEN||r.sNameKR);
        const desc=App.IS_DOMESTIC?(r.sDescKR||r.sDescEN):(r.sDescEN||r.sDescKR);
        const hours=r.sOpenTime||'-';
        const addr =App.IS_DOMESTIC?([r.sAddr1KR,r.sAddr2KR,r.sAddr3KR].filter(Boolean).join(' '))
                                   :([r.sAddr1EN,r.sAddr2EN,r.sAddr3EN].filter(Boolean).join(' '));
        $('#mDetName').text(name||'-'); $('#mDetDesc').text(desc||'-'); $('#mDetHours').text(hours||'-'); $('#mDetAddr').text((addr||'').trim()||'-');

        $.getJSON('/api/store/photos/'+no, function(photos){
          const list=Array.isArray(photos)?photos:[]; const $slides=$('#mPhotoSlides').empty();
          if(!list.length){ const fb=r.sMainIMG_Source?('/uploads/store/'+r.sMainIMG_Source):'/uploads/store/noimg.jpg';
            $slides.append(`<div class="carousel-item active"><img src="${fb}" alt=""></div>`); }
          else list.forEach((p,i)=> $slides.append(`<div class="carousel-item ${i===0?'active':''}"><img src="${p.url}" alt=""></div>`));
          App.initCarouselOnce('mPhotoCarousel');
        });

        const $slides=$('#mCouponSlides').empty(), $dots=$('#mCouponDots').empty();
        $.getJSON('/api/coupon/list/'+no, function(cs){
          const arr=Array.isArray(cs)?cs:[]; const chunk3=a=>{const out=[]; for(let i=0;i<a.length;i+=3) out.push(a.slice(i,i+3)); return out;};
          if(!arr.length){
            $slides.append(`<div class="carousel-item active"><div class="p-2 text-muted small"><?= $is_domestic?'상점 기본할인 사용 가능':'Use store default discount' ?></div></div>`);
            $dots.append(`<button type="button" data-bs-target="#mCouponCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>`);
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
              $slides.append(`<div class="carousel-item ${gi===0?'active':''}"><div class="m-coupon-grid3">${cards}</div></div>`);
              $dots.append(`<button type="button" data-bs-target="#mCouponCarousel" data-bs-slide-to="${gi}" class="${gi===0?'active':''}" ${gi===0?'aria-current="true"':''}></button>`);
            });
            const multi=(groups.length>1); $('#mCouponCarousel .carousel-control-prev, #mCouponCarousel .carousel-control-next').toggle(multi); $('#mCouponDots').toggle(multi);
          }
          App.initCarouselOnce('mCouponCarousel');
        });

        //bootstrap.Offcanvas.getOrCreateInstance('#mPlaceSheet', {backdrop:true,scroll:true}).show();
		LayerManager.openPlace();
      });
    });
  };

  // Drag resize for #mPlaceSheet
  (function(){
    const el=document.getElementById('mPlaceSheet'); if(!el) return;
    let startY=0,startH=0,drag=false;
    const MIN_H=Math.round(window.innerHeight*0.40), HALF_H=Math.round(window.innerHeight*0.60), FULL_H=Math.round(window.innerHeight*0.90);
    const clamp=(v,min,max)=>Math.max(min,Math.min(max,v));
    const header=el.querySelector('.offcanvas-header');

    function onStart(e){ const t=e.touches?e.touches[0]:e; startY=t.clientY; startH=el.getBoundingClientRect().height; drag=true; el.classList.add('no-transition'); }
    function onMove (e){ if(!drag) return; const t=e.touches?e.touches[0]:e; const dy=startY-t.clientY; const h=clamp(startH+dy,MIN_H,FULL_H); el.style.height=h+'px'; }
    function onEnd  (){ if(!drag) return; drag=false; el.classList.remove('no-transition'); const h=el.getBoundingClientRect().height; const target=(h>(HALF_H+(FULL_H-HALF_H)/4))?FULL_H:HALF_H; el.style.height=target+'px'; }

    header.addEventListener('touchstart',onStart,{passive:true});
    header.addEventListener('touchmove' ,onMove ,{passive:false});
    header.addEventListener('touchend'  ,onEnd  ,{passive:true});
    header.addEventListener('mousedown' ,onStart);
    window.addEventListener('mousemove',onMove);
    window.addEventListener('mouseup'  ,onEnd);
    el.addEventListener('shown.bs.offcanvas', ()=> el.style.height=HALF_H+'px' );
  })();

  // Export
  window.openMobileDetail = App.openMobileDetail;

})(window.App||{});
</script>
