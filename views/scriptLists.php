<script>
/* ==========================================================================
 * List renderers (PC / Mobile)
 * ========================================================================== */
(function(App){
	'use strict';

	App.__rowsCachePC = [];
	App.__rowsCacheMO = [];

	App.renderListMobile = function(rows){
		App.__rowsCacheMO = rows||[];
		const ref=App.REF_POINT, $ul=App.$list.empty();

		(App.__rowsCacheMO||[]).forEach(r=>{
			const name = App.IS_DOMESTIC ? (r.sNameKR||r.sNameEN) : (r.sNameEN||r.sNameKR);
			const dc	 = r.dcType==='%' ? (r.dcAmount+'% OFF') : (App.IS_DOMESTIC?(r.dcAmount+'원 할인'):(r.dcAmount+' won OFF'));
			let dist=''; if(ref && r.mapLat && r.mapLng){ const km=App.distanceKm(ref.lat,ref.lng,+r.mapLat,+r.mapLng); dist=`<span class="dist">${km}km</span><span class="sep">·</span>`; }
			$ul.append(`<li class="list-group-item list-group-item-action d-flex align-items-center gap-3" data-no="${r.no}">
				<img src="/uploads/store/${r.sMainIMG_Source||'noimg.jpg'}" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:.5rem;">
				<div class="flex-grow-1"><div class="fw-semibold">${name||'-'}</div><div class="small text-muted">${dist}${dc}</div></div></li>`);
		});
	};

	App.renderListPC = function(rows){
		App.__rowsCachePC = rows||[];
		const data = (App.SORT_MODE_PC==='distance' && App.REF_POINT)
			? ([...App.__rowsCachePC].map(r=>{ r.__distKm=(r.mapLat&&r.mapLng)?App.distanceKm(App.REF_POINT.lat,App.REF_POINT.lng,+r.mapLat,+r.mapLng):null; return r; })
				 .sort((a,b)=>(a.__distKm==null)-(b.__distKm==null) || (a.__distKm-b.__distKm)))
			: [...App.__rowsCachePC];

		$('#pcPlaceCount').text(data.length.toLocaleString());
		const $ul=App.$pcList.empty();

		(data||[]).forEach(r=>{
			const name	= App.IS_DOMESTIC ? (r.sNameKR||r.sNameEN) : (r.sNameEN||r.sNameKR);
			const img	 = r.sMainIMG_Source ? `/uploads/store/${r.sMainIMG_Source}` : '/uploads/store/noimg.jpg';
			const time	= r.sOpenTime||''; const addr= App.IS_DOMESTIC ? (r.sAddr1KR||'') : (r.sAddr1EN||'');
			const dcTxt = r.dcType==='%' ? (r.dcAmount+'%') : (App.IS_DOMESTIC?(r.dcAmount+'원'):(r.dcAmount+' won'));
			const distKm= (typeof r.__distKm==='number')?r.__distKm:(App.REF_POINT&&r.mapLat&&r.mapLng?App.distanceKm(App.REF_POINT.lat,App.REF_POINT.lng,+r.mapLat,+r.mapLng):null);
			const dist	= (distKm!=null)?`<span class="dist"><strong>${distKm}km</strong></span><span class="sep"> · </span>`:'';

			$ul.append(`<li class="place-card" data-no="${r.no}">
				<div class="place-thumb"><img src="${img}" alt=""></div>
				<div class="place-body">
					<div class="place-title">${name||'-'}</div>
					<div class="place-meta">
						${time?`<span class="ok">${App.IS_DOMESTIC?'운영 중':''}</span><span class="sep"> · </span><span class="time">${time}</span>`:''}
						${dist}${addr?`<span class="addr">${addr}</span>`:''}
					</div>
					<div class="chipline">
						${r.isStamp==='Y'?`<span class="chip"><span class="ico"></span> Stamp</span>`:''}
						${r.dcAmount?`<span class="chip dc"><i class="fa fa-arrow-down" aria-hidden="true"></i> DC ${dcTxt}</span>`:''}
					</div>
				</div></li>`);
		});
	};

	// Export
	window.renderListPC = App.renderListPC;
	window.renderListMobile = App.renderListMobile;

})(window.App||{});
</script>
