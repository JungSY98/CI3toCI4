<script>
let REF_POINT=null, __rowsCachePC=[], __rowsCacheMO=[];
function distanceKm(aLat,aLng,bLat,bLng){ const R=6371.0088,toRad=d=>d*Math.PI/180,dLat=toRad(bLat-aLat),dLng=toRad(bLng-aLng),s1=toRad(aLat),s2=toRad(bLat),a=Math.sin(dLat/2)**2+Math.sin(dLng/2)**2*Math.cos(s1)*Math.cos(s2); return Math.round((R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a)))*10)/10; }
function trySetUserLocation(cb){ if(!navigator.geolocation){ cb&&cb(false); return; } navigator.geolocation.getCurrentPosition(p=>{ REF_POINT={lat:p.coords.latitude,lng:p.coords.longitude}; cb&&cb(true); }, _=>{ cb&&cb(false); }, {enableHighAccuracy:true,timeout:5000,maximumAge:60000}); }
function setMapCenterAsRef(){
	if(!window.__MAP__||!__MAP__.map) return;
	if(__MAP__.provider==='kakao'&&window.kakao) {
		const c=__MAP__.map.getCenter();
		REF_POINT={lat:c.getLat(),lng:c.getLng()};
	} else if(__MAP__.provider==='google'&&window.google){
		const c=__MAP__.map.getCenter();
		REF_POINT={lat:c.lat(),lng:c.lng()};
	}
}
function prepareDistanceAndSortPC(rows){ if(!REF_POINT) return rows||[]; return (rows||[]).map(r=>{ r.__distKm=(r.mapLat&&r.mapLng)?distanceKm(REF_POINT.lat,REF_POINT.lng,+r.mapLat,+r.mapLng):null; return r; }).sort((a,b)=> (a.__distKm==null)-(b.__distKm==null) || (a.__distKm-b.__distKm)); }

function renderListMobile(rows){
	__rowsCacheMO = rows||[]; window.__rowsCacheMO=__rowsCacheMO;
	const ref=REF_POINT; const $ul=$('#resultList').empty();
	(__rowsCacheMO||[]).forEach(r=>{
		const name=IS_DOMESTIC?(r.sNameKR||r.sNameEN):(r.sNameEN||r.sNameKR);
		const dc = r.dcType==='%'?(r.dcAmount+'% OFF'):(IS_DOMESTIC?(r.dcAmount+'원 할인'):(r.dcAmount+' won OFF'));
		let dist=''; if(ref&&r.mapLat&&r.mapLng){ const km=distanceKm(ref.lat,ref.lng,+r.mapLat,+r.mapLng); dist=`<span class="dist">${km}km</span><span class="sep">·</span>`; }
		$ul.append(`<li class="list-group-item list-group-item-action d-flex align-items-center gap-3" data-no="${r.no}">
			<img src="/uploads/store/${r.sMainIMG_Source||'noimg.jpg'}" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:.5rem;">
			<div class="flex-grow-1"><div class="fw-semibold">${name||'-'}</div><div class="small text-muted">${dist}${dc}</div></div></li>`);
	});
}

function renderListPC(rows){
	__rowsCachePC = rows||[]; window.__rowsCachePC=__rowsCachePC;
	const data = prepareDistanceAndSortPC([...__rowsCachePC]);
	$('#pcPlaceCount').text(data.length.toLocaleString());
	const $ul=$('#pcResultList').empty();
	(data||[]).forEach(r=>{
		const name=IS_DOMESTIC?(r.sNameKR||r.sNameEN):(r.sNameEN||r.sNameKR);
		const img=r.sMainIMG_Source?(`/uploads/store/${r.sMainIMG_Source}`):'/uploads/store/noimg.jpg';
		const time=r.sOpenTime||''; const addr=IS_DOMESTIC?(r.sAddr1KR||''):(r.sAddr1EN||'');
		const dcTxt=r.dcType==='%'?(r.dcAmount+'%'):(IS_DOMESTIC?(r.dcAmount+'원'):(r.dcAmount+' won'));
		const distKm=(r.__distKm!=null)?`<span class="dist"><strong>${r.__distKm}km</strong></span><span class="sep"> · </span>`:'';
		$ul.append(`<li class="place-card" data-no="${r.no}">
			<div class="place-thumb"><img src="${img}" alt=""></div>
			<div class="place-body">
				<div class="place-title">${name||'-'}</div>
				<div class="place-meta">${time?`<span class="ok">${IS_DOMESTIC?'운영 중':''}</span><span class="sep"> · </span><span class="time">${time}</span>`:''}${distKm}${addr?`<span class="addr">${addr}</span>`:''}</div>
				<div class="chipline">${r.isStamp==='Y'?`<span class="chip"><span class="ico"></span> Stamp</span>`:''}${r.dcAmount?`<span class="chip dc"><i class="fa fa-arrow-down"></i> DC ${dcTxt}</span>`:''}</div>
			</div></li>`);
	});
}
</script>
