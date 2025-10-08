<script>
/* ==========================================================================
 * Coupons (square click → detail modal → use → result)
 * ========================================================================== */
(function(App){
  'use strict';

  // 모바일 탭 보장
  let tapStart=null;
  $(document).off('touchstart.coupon').on('touchstart.coupon','.coupon-square',function(e){
    const t=e.originalEvent.touches&&e.originalEvent.touches[0]; if(!t) return;
    tapStart={x:t.clientX,y:t.clientY,t:Date.now(),el:this};
  });
  $(document).off('touchend.coupon').on('touchend.coupon','.coupon-square',function(e){
    if(!tapStart || tapStart.el!==this) return;
    const t=e.originalEvent.changedTouches&&e.originalEvent.changedTouches[0]; if(!t){ tapStart=null; return; }
    const dx=Math.abs(t.clientX-tapStart.x), dy=Math.abs(t.clientY-tapStart.y), dt=Date.now()-tapStart.t;
    if(dx<10 && dy<10 && dt<400){ e.preventDefault(); e.stopPropagation(); $(this).trigger('coupon:tap'); }
    tapStart=null;
  });

  // 쿠폰 탭/클릭 → 상세 모달
  $(document).off('coupon:tap click.coupon','.coupon-square')
  .on('coupon:tap click.coupon','.coupon-square', function(){
    const couponNo=$(this).data('coupon')||''; const storeNo=$(this).data('store')||''; if(!storeNo) return;
    window.__currentStoreNo=storeNo; window.__currentCouponNo=couponNo;

    const cm=bootstrap.Modal.getOrCreateInstance(document.getElementById('couponModal'));

    if(!couponNo){
      const storeName=$('#mDetName').text()||$('#pcDetName').text()||'Store';
      $('#modalStoreName').html(storeName);
      $('#modalCouponTitle').text(App.IS_DOMESTIC?'상점 기본할인':'Store default discount');
      $('#ticketTopInfo').html(storeName);
      $('#modalCouponDesc').text(''); 
	  $('#modalBadgeTarget').text(App.IS_DOMESTIC?'내국인/외국인':'Domestic/Foreigner'); $('#modalBadgeValid').text(App.IS_DOMESTIC?'상시':'Always');
      $('#modalPrice').val(''); $('#modalCode').val(''); cm.show(); return;
    }

    $.getJSON('/api/coupon/detail/'+couponNo,function(c){
      const storeName=(App.IS_DOMESTIC?(c.storeD.sNameKR||c.storeD.sNameEN):(c.storeD.sNameKR||c.storeD.sNameEN))||'Store';
      $('#modalStoreName').html(storeName);
      const title=c.title||(App.IS_DOMESTIC?(c.title_kr||c.title_en):(c.title_en||c.title_kr))||'Coupon';
      $('#modalCouponTitle').text(title);
      $('#ticketTopInfo').html(storeName + "<br>" + title + " " + c.dc_amount + " " + c.dc_type);
      const desc=App.IS_DOMESTIC?(c.desc_kr||c.desc_en):(c.desc_en||c.desc_kr);
	  $('#modalCouponDesc').html(desc||'');
      const valid=(c.valid_from||c.valid_to)?`${c.valid_from||'~'} ~ ${c.valid_to||'~'}`:(App.IS_DOMESTIC?'상시':'Always');
      $('#modalBadgeValid').text(valid); $('#modalBadgeTarget').text(App.IS_DOMESTIC?'내국인/외국인':'Domestic/Foreigner');
      const S=c.storeD||{}; const addr=App.IS_DOMESTIC?[S.sAddr1KR,S.sAddr2KR,S.sAddr3KR].filter(Boolean).join(' '):[S.sAddr1EN,S.sAddr2EN,S.sAddr3EN].filter(Boolean).join(' ');
      $('#couponLocation').text(addr||'-'); $('#couponOpenTime').text(S.sOpenTime||'-');
      $('#modalPrice').val(''); $('#modalCode').val(''); cm.show();
    });
  });

  // 쿠폰 적용
  let usingInFlight=false;
  $(document).off('click.couponUse','#btnModalUse').on('click.couponUse','#btnModalUse',function(e){
    e.preventDefault(); if(usingInFlight) return;
    const storeNo=window.__currentStoreNo, couponNo=window.__currentCouponNo||'';
    const price=parseInt($('#modalPrice').val()||'0',10), code=($('#modalCode').val()||'').trim();
    if(!storeNo){ alert('상점 정보가 없습니다.'); return; }
    if(!price||price<=0){ alert(App.IS_DOMESTIC?'금액을 입력해주세요':'Enter price'); return; }
    if(!code){ alert(App.IS_DOMESTIC?'코드를 입력해주세요':'Enter code'); return; }

    try{ const mEl=document.getElementById('mPlaceSheet'); const m=mEl?bootstrap.Offcanvas.getInstance(mEl):null; if(m&&$(mEl).hasClass('show')) m.hide(); }catch(_){}
    usingInFlight=true; const $btn=$('#btnModalUse').prop('disabled',true); const old=$btn.html();
    $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>'+(App.IS_DOMESTIC?'적용 중…':'Applying…'));

    const payload={price,code,store_no:storeNo,coupon_no:couponNo}; payload[App.CSRF_NAME]=App.CSRF_HASH;
    $.ajax({url:'/api/coupon/use',type:'POST',dataType:'json',data:payload,timeout:15000})
    .done(function(resp){
      if(resp && resp[App.CSRF_NAME]) App.CSRF_HASH=resp[App.CSRF_NAME];
      if(!resp||!resp.ok){ alert(App.IS_DOMESTIC?'쿠폰 적용에 실패했습니다.':'Failed to use coupon.'); return; }

      const storeName=$('#pcDetName').text()||$('#mDetName').text()||'Store';
      const couponTitle=$('#modalCouponTitle').text()||(App.IS_DOMESTIC?'상점 기본할인':'Store default discount');
      $('#resultStoreName').text(storeName);
      $('#resultCouponTitle').html(storeName+"<br>"+couponTitle);
      const line2=App.IS_DOMESTIC
        ? `총 금액 ${price.toLocaleString()}원에서 <b>${resp.dcAmount}${resp.dcType==='%'?'%':'원'}</b> <br class="d-none d-xl-block">할인된 ${resp.pay.toLocaleString()}원을 결제해 주세요`
        : `Please, pay ${resp.pay.toLocaleString()} KRW, with a <b>${resp.dcAmount}${resp.dcType==='%'?'%':' won'}</b> discount from ${price.toLocaleString()} KRW.`;
      $('#resultLine2').html(line2); $('#resultPay').text('₩ '+resp.pay.toLocaleString());

      const cm=bootstrap.Modal.getOrCreateInstance(document.getElementById('couponModal'));
      const rm=bootstrap.Modal.getOrCreateInstance(document.getElementById('resultModal'));
      cm.hide(); setTimeout(()=> rm.show(), 150);
    })
    .fail(function(xhr){
      if(xhr.status===401){ App.requireLogin(); return; }
      let msg=App.IS_DOMESTIC?'쿠폰 적용 중 오류가 발생했습니다.':'Error while applying coupon.';
      try{ const r=xhr.responseJSON||{};
        if(r.error==='invalid_code') msg=App.IS_DOMESTIC?'코드가 일치하지 않습니다.':'Code mismatch.';
        else if(r.error==='limit_total_exceeded') msg=App.IS_DOMESTIC?'쿠폰 전체 사용 가능 횟수를 초과했습니다.':'Total usage limit exceeded.';
        else if(r.error==='limit_member_exceeded'){
          const unit=r.period==='day'?(App.IS_DOMESTIC?'하루':'day'):r.period==='week'?(App.IS_DOMESTIC?'1주':'week'):r.period==='month'?(App.IS_DOMESTIC?'1달':'month'):(App.IS_DOMESTIC?'평생':'lifetime');
          msg=App.IS_DOMESTIC?`회원당 사용가능 횟수(${unit} 기준 ${r.limit}회)를 초과했습니다.`:`Per-member usage limit (${r.limit} per ${unit}) exceeded.`;
        }
      }catch(_){}
      alert(msg);
    })
    .always(function(){ usingInFlight=false; $btn.prop('disabled',false).html(old); });
  });

})(window.App||{});
</script>
