(function(root){
    'use strict';
    function edgeUvs(rect,dx,dy) {
        var t=[[[1,1],[1+dx,0],[1,0]],[[1,1],[1+dx,1],[1+dx,0]],
            [[-dx,1],[0,0],[-dx,0]],[[-dx,1],[0,1],[0,0]],
            [[0,0],[1,-dy],[0,-dy]],[[0,0],[1,0],[1,-dy]],
            [[0,1+dy],[1,1],[0,1]],[[0,1+dy],[1,1+dy],[1,1]]];
        return t.map(function(tri){return tri.map(function(p){return [rect.x+p[0]*rect.w,1-rect.y-rect.h+p[1]*rect.h];});});
    }
    function install(editor,ui) {
        var panel=ui.querySelector('[data-ai-panel]');
        if(!panel || editor.updateAiEdges) return;
        var generate=panel.querySelector('[data-ai-generate]'),apply=panel.querySelector('[data-ai-apply]');
        var status=panel.querySelector('[data-ai-status]'),preview=panel.querySelector('[data-ai-preview]');
        var active=null,pending=null,busy=false,serial=0,ids=new WeakMap(),next=1;
        var requestId='',lastError=null,reset=false,traceStart=0,traces=[];
        function trace(stage,extra){
            panel.dataset.aiTrace=JSON.stringify(Object.assign({request_id:requestId,stage:stage,elapsed_ms:Date.now()-traceStart},extra||{}));
            traces.push(JSON.parse(panel.dataset.aiTrace));if(traces.length>60)traces.shift();
            panel.dataset.aiTraces=JSON.stringify(traces);
        }
        function fail(code,httpStatus){
            lastError=code;panel.dataset.aiError=code;
            message(code==='AI_AUTH_ERROR'||code==='AI_CSRF_ERROR'||code==='AI_SESSION_ERROR'?'login':
                code==='AI_LOCAL_QUOTA'?'quota':code==='AI_RATE_LIMIT'?'rate':code==='AI_TIMEOUT'?'timeout':code==='AI_PROVIDER_QUOTA'?'unavailable':'error');
            status.textContent+=' ['+code+']';
            trace('failed',{error_code:code,http_status:httpStatus||null});
        }
        function key(){
            var r=editor.grid[0]&&editor.grid[0].resource;
            if(!r)return null;
            if(!ids.has(r.img.original))ids.set(r.img.original,next++);
            // Display resolution changes when the result panel collapses. It is not a crop change.
            return JSON.stringify([ids.get(r.img.original),
                editor.size_depth,editor.size_width,editor.size_height,root.size_centimeter_w,root.size_centimeter_h,
                r.zoom,r.rotate,Math.round(r.offset_x*1e6)/1e6,Math.round(r.offset_y*1e6)/1e6,r.invert_x,r.invert_y]);
        }
        function message(name){status.textContent=panel.dataset[name]||panel.dataset.error;}
        function discardPending(){pending=null;apply.hidden=true;preview.hidden=true;preview.removeAttribute('src');}
        editor.updateAiEdges=function(){
            var current=key();
            if(active&&active.key!==current){active.texture.dispose();if(editor.box&&editor.box.material[2])editor.box.material[2].map=null;active=null;reset=true;message('changed');}
            if(pending&&pending.key!==current){discardPending();reset=true;message('changed');}
            panel.hidden=editor.edge_mode!=='ai';
            generate.disabled=busy||!current;
            if(!current&&!busy)message('upload');
            if(current&&!busy&&!active&&!pending&&status.textContent===panel.dataset.upload)message('ready');
            panel.dataset.aiState=busy?'generating':pending?'review':active&&editor.edge_mode==='ai'?'applied':'not-applied';
            panel.dataset.aiLifecycle=busy?'AI_GENERATING':pending?'AI_READY_FOR_REVIEW':active&&editor.edge_mode==='ai'?'AI_APPLIED':reset?'AI_RESET':lastError?(lastError==='AI_LOCAL_QUOTA'||lastError==='AI_RATE_LIMIT'?'AI_RATE_LIMITED':'AI_ERROR'):'AI_NOT_CREATED';
            if(!editor.box)return;
            var geo=editor.box.geometry,dx=editor.size_depth/editor.size_width,dy=editor.size_depth/editor.size_height;
            var coords=(editor.edge_mode==='wrap'?root.CanvasWrap.foldedUvs:root.CanvasWrap.uvs)(dx,dy);
            if(editor.edge_mode==='ai'&&active){
                if(!editor.box.material[2])editor.box.material.push(new THREE.MeshLambertMaterial({side:THREE.DoubleSide}));
                var mat=editor.box.material[2];
                if(mat.map!==active.texture){mat.map=active.texture;mat.needsUpdate=true;}
                coords=coords.slice(0,2).concat(edgeUvs(active.rect,dx,dy));
                geo.faces.forEach(function(f,i){f.materialIndex=i<2?0:2;});
            } else if(editor.edge_mode==='ai') {
                geo.faces.forEach(function(f,i){f.materialIndex=i<2?0:1;});
            }
            geo.faceVertexUvs[0]=coords.map(function(tri){return tri.map(function(p){return new THREE.Vector2(p[0],p[1]);});});
            geo.uvsNeedUpdate=true;geo.groupsNeedUpdate=true;
        };
        generate.addEventListener('click',async function(){
            traceStart=Date.now();requestId=Array.from({length:32},function(){return Math.floor(Math.random()*16).toString(16);}).join('');
            trace('generate_clicked');
            editor.render2D();var startKey=key();if(!startKey||busy)return;
            lastError=null;reset=false;panel.dataset.aiError='';trace('frontend_validated');
            busy=true;generate.disabled=true;message('waiting');var ticket=++serial;
            var controller=new AbortController(),timer=setTimeout(function(){controller.abort();},65000);
            try {
                // A long-open configurator may retain a token from before login.
                trace('session_request');
                var session=await fetch('/canvas-ai/session',{signal:controller.signal,credentials:'same-origin',headers:{'Accept':'application/json'},cache:'no-store'});
                if(!session.ok||session.redirected){fail(session.status===401||session.redirected?'AI_AUTH_ERROR':'AI_SESSION_ERROR',session.status);return;}
                var sessionData=await session.json();
                if(!sessionData.csrf){fail('AI_CSRF_ERROR');return;}
                var front=document.createElement('canvas'),scale=Math.min(1,768/Math.max(editor.canvas.width,editor.canvas.height));
                front.width=Math.max(32,Math.round(editor.canvas.width*scale));front.height=Math.max(32,Math.round(editor.canvas.height*scale));
                front.getContext('2d').drawImage(editor.canvas,0,0,front.width,front.height);
                var blob=await new Promise(function(resolve){front.toBlob(resolve,'image/png');});
                if(!blob)throw new Error('image');
                var data=new FormData();data.append('image',blob,'front.png');
                data.append('width_cm',root.size_centimeter_w);data.append('height_cm',root.size_centimeter_h);
                data.append('depth_x',editor.size_depth/editor.size_width);data.append('depth_y',editor.size_depth/editor.size_height);
                trace('request_sent');
                var response=await fetch('/canvas-ai/preview',{method:'POST',signal:controller.signal,credentials:'same-origin',
                    headers:{'Accept':'application/json','X-CSRF-TOKEN':sessionData.csrf,'X-Canvas-Request-ID':requestId},body:data});
                if(response.redirected){fail('AI_AUTH_ERROR',302);return;}
                if(!response.ok){
                    var failure=await response.json().catch(function(){return {};});
                    fail(/^AI_[A-Z_]+$/.test(failure.code||'')?failure.code:response.status===401?'AI_AUTH_ERROR':response.status===419?'AI_CSRF_ERROR':response.status===429?'AI_RATE_LIMIT':'AI_INTERNAL_ERROR',response.status);return;
                }
                var result=await response.json();
                if(!result || !/^[a-f0-9]{32}$/.test(result.id) ||
                    !Number.isSafeInteger(result.fee_eur) || result.fee_eur<0 ||
                    typeof result.url!=='string' || !result.url.includes('/canvas-ai/') ||
                    !result.rect || !['x','y','w','h'].every(function(k){return Number.isFinite(result.rect[k])&&result.rect[k]>0&&result.rect[k]<1;}) ||
                    result.rect.x+result.rect.w>=1 || result.rect.y+result.rect.h>=1)throw new Error('Invalid preview');
                trace('response_received',{http_status:response.status||200});
                var img=new Image();img.src=result.url;
                await new Promise(function(resolve,reject){
                    var abort=function(){reject(new DOMException('Timeout','AbortError'));};
                    if(controller.signal.aborted)return abort();
                    controller.signal.addEventListener('abort',abort,{once:true});
                    img.decode().then(resolve,function(){reject(new Error('image'));}).finally(function(){controller.signal.removeEventListener('abort',abort);});
                });
                if(ticket!==serial||startKey!==key()){message('changed');return;}
                pending={key:startKey,image:img,rect:result.rect,id:result.id,fee:result.fee_eur};preview.src=result.url;preview.hidden=false;apply.hidden=false;
                message('review');status.textContent+=' '+panel.dataset.fee.replace(':fee',result.fee_eur);
                trace('preview_shown');
            } catch(e){fail(e.name==='AbortError'?'AI_TIMEOUT':e.message==='image'?'AI_IMAGE_ERROR':e instanceof SyntaxError||e.message==='Invalid preview'?'AI_INVALID_RESPONSE':'AI_INTERNAL_ERROR');}
            finally{clearTimeout(timer);busy=false;editor.updateAiEdges();}
        });
        apply.addEventListener('click',function(){
            if(!pending||pending.key!==key()){discardPending();message('changed');return;}
            if(active)active.texture.dispose();
            active={key:pending.key,rect:pending.rect,id:pending.id,fee:pending.fee,texture:new THREE.Texture(pending.image)};
            active.texture.needsUpdate=true;active.texture.minFilter=THREE.LinearFilter;
            discardPending();editor.edge_mode='ai';editor.add_history();editor.updateAiEdges();editor.render3D();
            lastError=null;reset=false;trace('apply');
            if(active){message('applied');status.textContent+=' '+panel.dataset.fee.replace(':fee',active.fee);}
        });
        if(root.jQuery)root.jQuery.ajaxPrefilter(function(options){
            if(!/\/basket\/add$/.test(options.url)||!(options.data instanceof FormData))return;
            options.data.delete('canvas_ai_id');
            if(editor.edge_mode==='ai'&&active&&active.key===key()){options.data.append('canvas_ai_id',active.id);trace('cart');}
        });
        message('ready');
        editor.updateAiEdges();
    }
    root.CanvasOutpaint={install:install,edgeUvs:edgeUvs};
    if(root.dispatchEvent)root.dispatchEvent(new Event('canvas-outpaint-ready'));
    if(typeof module!=='undefined')module.exports=root.CanvasOutpaint;
})(typeof window!=='undefined'?window:globalThis);
