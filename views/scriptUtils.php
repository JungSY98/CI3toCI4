<script>
/* ==========================================================================
 * Utils (distance, cssVar, once carousel, bump)
 * ========================================================================== */
(function(App){
	'use strict';

	App.cssVar = function(name){
		try { return getComputedStyle(document.documentElement).getPropertyValue(name).trim(); }
		catch(_) { return ''; }
	};

	App.distanceKm = function(aLat,aLng,bLat,bLng){
		const R=6371.0088, toRad=d=>d*Math.PI/180;
		const dLat=toRad(bLat-aLat), dLng=toRad(bLng-aLng);
		const s1=toRad(aLat), s2=toRad(bLat);
		const a=Math.sin(dLat/2)**2 + Math.sin(dLng/2)**2 * Math.cos(s1)*Math.cos(s2);
		return Math.round((R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a)))*10)/10;
	};

	App.initCarouselOnce = function(id){
		const el = document.getElementById(id); if(!el) return null;
		const inst=bootstrap.Carousel.getInstance(el); if(inst) inst.dispose();
		return new bootstrap.Carousel(el,{interval:0, ride:false, touch:true, keyboard:true, pause:true, wrap:true});
	};

	App.bumpViewRev = function(){ App.VIEW_REV++; };

	// 위치/센터 유틸
	App.trySetUserLocation = function(cb){
		if(!navigator.geolocation){ cb&&cb(false); return; }
		navigator.geolocation.getCurrentPosition(
			pos=>{ App.REF_POINT={lat:pos.coords.latitude,lng:pos.coords.longitude}; cb&&cb(true); },
			_=>{ cb&&cb(false); }, {enableHighAccuracy:true, timeout:5000, maximumAge:60000}
		);
	};
	App.setMapCenterAsRef = function(){
		if(!App.MAP || !App.MAP.map) return;
		if(App.MAP.provider==='kakao'&&window.kakao){ const c=App.MAP.map.getCenter(); App.REF_POINT={lat:c.getLat(), lng:c.getLng()}; }
		else if(App.MAP.provider==='google'&&window.google){ const c=App.MAP.map.getCenter(); App.REF_POINT={lat:c.lat(), lng:c.lng()}; }
	};

})(window.App||{});
</script>
