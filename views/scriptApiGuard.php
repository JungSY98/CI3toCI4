<script>
/* ==========================================================================
 * ApiGuard (in-flight dedupe + backoff + stale-guard)
 * ========================================================================== */
(function(App){
  'use strict';
  const inFlight = new Map();
  const sleep = ms => new Promise(r=> setTimeout(r, ms));

  App.ApiGuard = {
    async getJSON(key, url, data, opt={}){
      const { retry=2, base=260, jitter=120, timeout=12000, staleRev } = opt;
      if (inFlight.has(key)){ try{ inFlight.get(key).abort(); }catch(_){ } inFlight.delete(key); }
      let attempt=0;
      while(true){
        if (staleRev!=null && staleRev!==App.VIEW_REV) throw {__stale:true};
        const jq = $.ajax({ url, data, dataType:'json', method:'GET', timeout });
        inFlight.set(key, jq);
        try{
          const res = await jq; inFlight.delete(key);
          if (staleRev!=null && staleRev!==App.VIEW_REV) throw {__stale:true};
          return res;
        }catch(err){
          inFlight.delete(key);
          if (err==='abort' || err?.__stale) throw err;
          const status=(err&&err.status)|0, retriable=(!status||status>=500||status===429);
          if (attempt<retry && retriable){
            const delay = base*Math.pow(2,attempt) + Math.random()*jitter;
            attempt++; await sleep(delay); continue;
          }
          throw err;
        }
      }
    }
  };

  // Export (호환용)
  window.ApiGuard = App.ApiGuard;

})(window.App||{});
</script>
