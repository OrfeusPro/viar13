/* Opt-in front/edge rendering for the single-photo canvas configurator. */
(function (root) {
    'use strict';
    function crop(iw, ih, w, h, zoom, rotation) {
        var c = Math.abs(Math.cos(rotation || 0)), s = Math.abs(Math.sin(rotation || 0));
        var rw = iw * c + ih * s, rh = iw * s + ih * c;
        if (!(rw > 0 && rh > 0 && w > 0 && h > 0)) return null;
        var scale = Math.max(w / rw, h / rh) * Math.max(1, zoom || 1);
        return {x: Math.max(0, 1 - w / (rw * scale)), y: Math.max(0, 1 - h / (rh * scale)),
            ratio: rw / rh, extraX: rw * scale / w - 1, extraY: rh * scale / h - 1};
    }
    function uvs(dx, dy) {
        // Same vertex order as Canvas3D.Box. Back edges reflect inward from front.
        dx = Math.min(1, dx); dy = Math.min(1, dy);
        return [
            [[0,1],[1,0],[0,0]], [[0,1],[1,1],[1,0]],
            [[1,1],[1-dx,0],[1,0]], [[1,1],[1-dx,1],[1-dx,0]],
            [[dx,1],[0,0],[dx,0]], [[dx,1],[0,1],[0,0]],
            [[0,0],[1,dy],[0,dy]], [[0,0],[1,0],[1,dy]],
            [[0,1-dy],[1,1],[0,1]], [[0,1-dy],[1,1-dy],[1,1]]
        ];
    }
    function foldedUvs(dx,dy) {
        var x=dx/(1+2*dx),y=dy/(1+2*dy),l=x,r=1-x,t=1-y,b=y;
        return [ [[l,t],[r,b],[l,b]],[[l,t],[r,t],[r,b]],
            [[r,t],[1,b],[r,b]],[[r,t],[1,t],[1,b]],
            [[0,t],[l,b],[0,b]],[[0,t],[l,t],[l,b]],
            [[l,b],[r,0],[l,0]],[[l,b],[r,b],[r,0]],
            [[l,1],[r,t],[l,t]],[[l,1],[r,1],[r,t]] ];
    }
    function bestSize(ratio, sizes, currentLoss, current) {
        if (!current || !(current.w > 0 && current.h > 0)) return null;
        var best = null;
        sizes.forEach(function (size) {
            if (!(size.w > 0 && size.h > 0)) return;
            if (size.w < current.w || size.h < current.h ||
                (size.w === current.w && size.h === current.h)) return;
            var loss = 1 - Math.min(ratio / (size.w / size.h), (size.w / size.h) / ratio);
            if (loss + .02 < currentLoss && (!best || loss < best.loss - 1e-8 ||
                (Math.abs(loss-best.loss)<1e-8 && size.w*size.h<best.w*best.h))) best = {w:size.w,h:size.h,loss:loss};
        });
        return best;
    }
    function uploadSize(ratio, sizes, current) {
        if (!(ratio > 0 && current.w > 0 && current.h > 0)) return null;
        var shortSide=Math.min(current.w,current.h), longSide=Math.max(current.w,current.h);
        var best=null, bestLoss=1-Math.min(ratio/(current.w/current.h),(current.w/current.h)/ratio);
        sizes.forEach(function(size){
            if(!(size.w>0 && size.h>0) || Math.min(size.w,size.h)<shortSide || Math.max(size.w,size.h)<longSide) return;
            var loss=1-Math.min(ratio/(size.w/size.h),(size.w/size.h)/ratio);
            if(loss>bestLoss+1e-8) return;
            if(!best || loss<bestLoss-1e-8 || (Math.abs(loss-bestLoss)<1e-8 && size.w*size.h<best.w*best.h)) {
                best=size; bestLoss=loss;
            }
        });
        return best;
    }
    function install(editor, ui) {
        if (!ui || editor.independentFront) return;
        editor.independentFront = true;
        var workspace = ui.closest('.photo-content');
        if (workspace && !ui.closest('.calculator-v2')) {
            workspace.classList.add('canvas-workspace');
            var settings = workspace.querySelector('.photo-filter');
            settings.tabIndex = 0;
            var runway=document.createElement('div');
            runway.className='canvas-workspace-runway';
            workspace.parentNode.insertBefore(runway,workspace);runway.appendChild(workspace);
            var desktop=root.matchMedia('(min-width: 900px)'), inset=110;
            var boundary=0,boundaryGestures=0,lastWheel=0,lastMove=0,allowance=72,lastPanel=null;
            var scrollState='FREE',releaseDirection=0;
            function state(value){scrollState=value;workspace.dataset.scrollState=value;}
            state('FREE');
            function measureWorkspace(){
                if(!desktop.matches){runway.style.height='';workspace.style.removeProperty('--workspace-top');state('FREE');boundary=0;boundaryGestures=0;return;}
                var header=document.querySelector('header');
                inset=(header?header.getBoundingClientRect().height:86)+24;
                workspace.style.setProperty('--workspace-top',inset+'px');
                // No artificial page travel: only the panels move, not the backdrop.
                runway.style.height=workspace.offsetHeight+'px';
            }
            root.addEventListener('resize',measureWorkspace);
            function handleScroll(event){
                if(!desktop.matches||event.ctrlKey||!event.deltaY||Math.abs(event.deltaX)>Math.abs(event.deltaY))return;
                var delta=event.deltaY*(event.deltaMode===1?16:event.deltaMode===2?settings.clientHeight:1);
                var top=runway.getBoundingClientRect().top,distance=top-inset;
                var direction=delta>0?1:-1;
                // Focusing a scroll panel can move the document under the fixed header.
                // Keep an already locked workspace anchored before handling keyboard input.
                if(event.keyboard&&scrollState!=='FREE'&&scrollState!=='RELEASED'&&workspace.contains(event.target)&&Math.abs(distance)<workspace.offsetHeight/2){
                    if(Math.abs(distance)>2)root.scrollBy({top:distance,left:0,behavior:'instant'});
                    distance=0;
                }
                // A released gesture (including momentum) belongs to the document.
                // Re-enter only when returning across the workspace anchor.
                if(scrollState==='RELEASED'){
                    if(direction===releaseDirection){
                        // Panel overscroll containment must not trap the released gesture.
                        if(workspace.contains(event.target)){
                            event.preventDefault();root.scrollBy({top:delta,left:0,behavior:'instant'});
                        }
                        return;
                    }
                    if(Math.abs(distance)>2){
                        if((delta>0&&distance>0&&delta>=distance)||(delta<0&&distance<0&&delta<=distance)){
                            event.preventDefault();root.scrollBy({top:distance,left:0,behavior:'instant'});
                            boundary=0;boundaryGestures=0;lastWheel=Date.now();state('LOCKED');
                        }
                        return;
                    }
                    state('LOCKED');boundary=0;boundaryGestures=0;
                }
                // Legacy anchor navigation can land the form partly under the header.
                if(delta>0&&settings.scrollTop<=1&&workspace.contains(event.target)&&distance< -2&&distance> -workspace.offsetHeight/2){
                    event.preventDefault();root.scrollBy({top:distance,left:0,behavior:'instant'});
                    boundary=0;boundaryGestures=0;lastWheel=Date.now();state('LOCKED');return;
                }
                // Stop precisely when the form arrives below the header.
                if(Math.abs(distance)>2){
                    if((delta>0&&distance>0&&delta>=distance)||(delta<0&&distance<0&&delta<=distance)){
                        event.preventDefault();root.scrollBy({top:distance,left:0,behavior:'instant'});
                        boundary=0;boundaryGestures=0;lastWheel=Date.now();state('LOCKED');
                    }
                    return;
                }
                var preview=workspace.querySelector('.slider-tabs');
                // Scrolling outside the form always belongs to the page.
                if(!workspace.contains(event.target)){
                    releaseDirection=direction;state('RELEASED');return;
                }
                var panel=preview&&ui.contains(event.target)&&preview.scrollHeight>preview.clientHeight+1?preview:settings;
                var max=Math.max(0,panel.scrollHeight-panel.clientHeight);
                if(max<1)return;
                if(scrollState==='FREE')state('LOCKED');
                var now=Date.now(),fresh=event.keyboard ? !event.repeat : now-lastWheel>280,dir=delta>0?1:-1;
                lastWheel=now;
                if(lastPanel!==panel){boundary=0;boundaryGestures=0;lastPanel=panel;}
                if(boundary&&boundary!==dir){boundary=0;boundaryGestures=0;}
                event.preventDefault();
                var atEnd=dir>0?panel.scrollTop>=max-1:panel.scrollTop<=1;
                if(atEnd){
                    // Once the panel has reached its boundary, the next scroll
                    // continues the page, without extra gestures or a pause.
                    boundary=0;boundaryGestures=0;releaseDirection=dir;state('RELEASED');
                    root.scrollBy({top:delta,left:0,behavior:'instant'});
                    return;
                }
                boundary=0;boundaryGestures=0;state('LOCKED');
                // Token bucket limits fast wheels and trackpad bursts to 700 px/s.
                allowance=Math.min(72,allowance+Math.max(0,now-lastMove)*0.7);lastMove=now;
                var step=event.keyboard?Math.abs(delta):Math.min(Math.abs(delta)*0.55,allowance);
                if(!event.keyboard)allowance-=step;
                panel.scrollTop=Math.max(0,Math.min(max,panel.scrollTop+dir*step));
                if(dir>0?panel.scrollTop>=max-1:panel.scrollTop<=1)boundary=dir;
            }
            root.addEventListener('wheel',handleScroll,{passive:false});
            root.addEventListener('keydown',function(event){
                if(event.defaultPrevented||event.ctrlKey||event.metaKey||event.altKey)return;
                if(event.target.closest&&event.target.closest('input,textarea,select,button,a,[contenteditable="true"]'))return;
                var keys={ArrowDown:40,ArrowUp:-40,PageDown:settings.clientHeight*.8,PageUp:-settings.clientHeight*.8,' ':settings.clientHeight*.8*(event.shiftKey?-1:1)};
                if(!(event.key in keys))return;
                handleScroll({target:event.target,deltaY:keys[event.key],deltaX:0,deltaMode:0,keyboard:true,repeat:event.repeat,
                    preventDefault:function(){event.preventDefault();}});
            });
            if(root.ResizeObserver){
                var observer=new ResizeObserver(measureWorkspace);
                observer.observe(settings);observer.observe(workspace);
                settings.querySelectorAll('.filter-accordion,.filter-picture').forEach(function(el){observer.observe(el);});
            }
            measureWorkspace();
        }
        // The legacy scene has only a directional light: opposite edges go black.
        // Fill light belongs only to this opt-in editor, and is created once.
        editor.scene.children.forEach(function(light) {
            if (light instanceof THREE.DirectionalLight) {
                // Ambient .8 + directional .2 never exceeds unit illumination.
                light.color.set(0xffffff); light.intensity = .2;
            }
        });
        editor.scene.add(new THREE.AmbientLight(0xffffff, .8));
        editor.edge_mode = 'wrap'; editor.edge_color = '#eee9df';
        var oldBox = editor.Box, oldRender = editor.render2D;
        var wrapCanvas=document.createElement('canvas'),wrapTexture=new THREE.CanvasTexture(wrapCanvas),wrapLayout=null;
        function renderWrap(){
            var resource=editor.grid[0]&&editor.grid[0].resource;
            var dx=editor.size_depth/editor.size_width,dy=editor.size_depth/editor.size_height;
            var w=editor.canvas.width,h=editor.canvas.height,px=w*dx,py=h*dy;
            var fullW=Math.round(w+2*px),fullH=Math.round(h+2*py);
            if(wrapCanvas.width!==fullW)wrapCanvas.width=fullW;if(wrapCanvas.height!==fullH)wrapCanvas.height=fullH;
            var ctx=wrapCanvas.getContext('2d');ctx.fillStyle=editor.ColorCell||'#ddd';ctx.fillRect(0,0,fullW,fullH);
            wrapLayout={width:fullW,height:fullH,front:{x:px,y:py,width:w,height:h},depth_cm:editor.size_depth*Math.max(Number(root.size_centimeter_w),Number(root.size_centimeter_h))};
            if(resource){
                var img=resource.img.original,angle=resource.rotate||0,c=Math.abs(Math.cos(angle)),sn=Math.abs(Math.sin(angle));
                // Cover the entire unfolded rectangle with a single uniform scale.
                // The second bound also covers corners for non-right-angle rotation.
                var scale=Math.max((fullW*c+fullH*sn)/img.width,(fullW*sn+fullH*c)/img.height)*Math.max(1,resource.zoom||1);
                var rw=img.width*c+img.height*sn,rh=img.width*sn+img.height*c;
                var original=crop(img.width,img.height,w,h,resource.zoom,angle);
                var fx=original&&original.extraX>1e-8?resource.offset_x/original.extraX:.5;
                var fy=original&&original.extraY>1e-8?resource.offset_y/original.extraY:.5;
                // Translation is restricted in source axes so rotated corners stay covered.
                var shiftX=(.5-Math.max(0,Math.min(1,fx)))*(rw*scale-fullW),shiftY=(.5-Math.max(0,Math.min(1,fy)))*(rh*scale-fullH);
                var co=Math.cos(angle),si=Math.sin(angle),sx=shiftX*co+shiftY*si,sy=-shiftX*si+shiftY*co;
                var limitX=Math.max(0,(img.width*scale-fullW*c-fullH*sn)/2),limitY=Math.max(0,(img.height*scale-fullW*sn-fullH*c)/2);
                sx=Math.max(-limitX,Math.min(limitX,sx));sy=Math.max(-limitY,Math.min(limitY,sy));
                ctx.save();ctx.translate(fullW/2+sx*co-sy*si,fullH/2+sx*si+sy*co);ctx.rotate(angle);ctx.scale(scale*(resource.invert_x?-1:1),scale*(resource.invert_y?-1:1));ctx.drawImage(img,-img.width/2,-img.height/2);ctx.restore();
                wrapLayout.image={scale:scale,rotation:angle,center_x:fullW/2+sx*co-sy*si,center_y:fullH/2+sx*si+sy*co,invert_x:!!resource.invert_x,invert_y:!!resource.invert_y};
            }
            wrapTexture.needsUpdate=true;
        }
        editor.getPrintComposition=function(){editor.render2D();return editor.edge_mode==='wrap'?wrapCanvas:editor.canvas;};

        var oldHistory = editor.add_history, oldLoad = editor.load_history;
        var ids = new WeakMap(), nextId = 1, lastKey = null;
        var sampler = document.createElement('canvas'); sampler.width = sampler.height = 32;
        var sampleCtx = sampler.getContext('2d', {willReadFrequently:true});
        var warning = ui.querySelector('[data-crop-warning]');
        var radios = ui.querySelectorAll('input[name="canvas_edge_mode"]');
        // Test-host-only observability; no order data or image bytes are exposed.
        var qa = root.location && root.location.hostname === 'test.viarcanvas.com' &&
            new URLSearchParams(root.location.search).has('canvas_qa');
        var frontHash = null, lastMetrics = 0, frames = 0, disposedGeometries = 0, disposedMaterials = 0;
        function metrics() {
            if (!qa) return;
            var r = editor.grid[0] && editor.grid[0].resource;
            var info = r && crop(r.img.original.width,r.img.original.height,editor.canvas.width,editor.canvas.height,r.zoom,r.rotate);
            editor.canvas_ViewPort.dataset.canvasQa = JSON.stringify({frames:frames,
                textureVersion:editor.texture.version,frontHash:frontHash,
                memory:editor.renderer.info.memory,disposedGeometries:disposedGeometries,disposedMaterials:disposedMaterials,
                heapBytes:root.performance.memory ? root.performance.memory.usedJSHeapSize : null,
                scripts:root.performance.getEntriesByType('resource').filter(function(e){return /\/(canvas-wrap|3dcanvas|new_bot_scripts)\.js/.test(e.name);}).map(function(e){return {url:e.name,status:e.responseStatus,transfer:e.transferSize,duration:e.duration};}),
                failedResources:root.performance.getEntriesByType('resource').filter(function(e){return e.responseStatus>=400;}).map(function(e){return {url:e.name,status:e.responseStatus};}),
                mode:editor.edge_mode,color:editor.edge_color,printLayout:wrapLayout,uv:editor.box?editor.box.geometry.faceVertexUvs[0].map(function(t){return t.map(function(p){return [p.x,p.y];});}):null,
                width:editor.size_width,height:editor.size_height,zoom:r?r.zoom:null,rotation:r?r.rotate:null,
                image:r?{width:r.img.original.width,height:r.img.original.height}:null,crop:info});
        }
        var oldRender3D = editor.render3D;
        editor.render3D = function () {
            oldRender3D.call(this);frames++;
            if (qa && Date.now()-lastMetrics>500) {lastMetrics=Date.now();metrics();}
        };
        var wrapNotice=document.createElement('p');wrapNotice.className='calculator-wrap-notice';wrapNotice.textContent=ui.dataset.wrapNotice;wrapNotice.hidden=true;var tabs=ui.closest('#generate').querySelector('.slider-tabs>.tabs');tabs.after(wrapNotice);
        function sync() {
            wrapNotice.hidden=true;
            var description=ui.querySelector('[data-edge-description="'+editor.edge_mode+'"]');
            var output=ui.querySelector('#edge-mode-description');
            if(description && output && output.textContent!==description.textContent) output.textContent=description.textContent;
            radios.forEach(function (radio) {radio.checked = radio.value === editor.edge_mode;});
            if (!editor.box) return;
            editor.box.material[1].color.set(editor.edge_color);
            var map=editor.edge_mode==='wrap'?wrapTexture:editor.texture;
            if(editor.box.material[0].map!==map){editor.box.material[0].map=map;editor.box.material[0].needsUpdate=true;}
            editor.box.geometry.faces.forEach(function (face, i) { face.materialIndex = i < 2 || editor.edge_mode === 'mirror' || editor.edge_mode === 'wrap' ? 0 : 1; });
            var mapping=editor.edge_mode==='wrap'?foldedUvs:uvs;
            editor.box.geometry.faceVertexUvs[0]=mapping(editor.size_depth/editor.size_width,editor.size_depth/editor.size_height).map(function(tri){return tri.map(function(p){return new THREE.Vector2(p[0],p[1]);});});
            editor.box.geometry.uvsNeedUpdate=true;
            editor.box.geometry.groupsNeedUpdate = true;
            if(editor.updateAiEdges)editor.updateAiEdges();
            // r118 caches Vector2 references in Geometry.__directGeometry. Replacing
            // faceVertexUvs alone leaves the GPU rendering the previous (mirror) UVs.
            // Rebuild the flattened geometry after every edge mapping transition.
            editor.box.geometry.elementsNeedUpdate = true;
            metrics();
        }
        editor.add_history = function () {
            oldHistory.call(this);
            var state = this.history[this.history_pos - 1];
            state.edge_mode = this.edge_mode; state.edge_color = this.edge_color;
        };
        editor.load_history = function () {
            var state = this.history[this.history_pos - 1];
            this.edge_mode = state.edge_mode || 'wrap'; this.edge_color = state.edge_color || '#eee9df';
            oldLoad.call(this);
            // Legacy load_history aliases resource objects; keep snapshots immutable.
            this.grid.forEach(function(cell){if(cell.resource)cell.resource=Object.assign({},cell.resource);});
            sync();
        };
        editor.Box = function (w,h,d) {
            var previous = this.box;
            var rotation = previous && previous.rotation.clone();
            oldBox.call(this,w,h,d);
            if (previous) {
                previous.geometry.dispose();
                disposedGeometries++;
                [].concat(previous.material).forEach(function (m) {m.dispose();disposedMaterials++;});
                this.box.rotation.copy(rotation);
            }
            // Fixed QA views let the test site inspect every edge without changing product controls.
            if (qa) {
                var views={front:[0,0],left:[-10,35],right:[-10,-35],top:[35,25],bottom:[-35,-25]};
                var view=views[new URLSearchParams(root.location.search).get('canvas_qa_view')];
                if(view){this.box.rotation.x=view[0]*Math.PI/180;this.box.rotation.y=view[1]*Math.PI/180;}
            }
            this.offset_cell_x = this.offset_cell_y = 0;
            this.isOffset = false;
            this.box.geometry.faceVertexUvs[0] = uvs(d/w,d/h).map(function (triangle) {
                return triangle.map(function (p) {return new THREE.Vector2(p[0],p[1]);});
            });
            this.box.geometry.uvsNeedUpdate = true;
            this.box.material = [this.box.material, new THREE.MeshLambertMaterial({color:this.edge_color,side:THREE.DoubleSide})];
            sync(); lastKey = null;
        };
        editor.PosTo3dBoxPos = function (p) {
            if (!this.box || !this.camera) return;
            var ray = new THREE.Raycaster();
            ray.setFromCamera(new THREE.Vector2(p.x*2-1,1-p.y*2),this.camera);
            var hits = ray.intersectObject(this.box);
            if (hits.length && hits[0].faceIndex < 2) return {x:hits[0].uv.x,y:1-hits[0].uv.y};
        };
        function availableSizes() {
            return Array.from(document.querySelectorAll('.calcSize[data-size]')).filter(function (el) {
                return !el.classList.contains('disabled') && el.getAttribute('aria-disabled') !== 'true';
            }).map(function (el) {
                var m = (el.getAttribute('onclick') || '').match(/setSize\(\s*([\d.]+)\s*,\s*([\d.]+)/);
                return m ? {w:Number(m[2]),h:Number(m[1])} : {w:0,h:0};
            });
        }
        // Choose once per decoded upload; manual choices, zoom and history stay in control.
        var autoFormImages = new WeakSet(), manualSizeRevision=0;
        document.addEventListener('click',function(event){
            if(event.isTrusted && event.target.closest('.calcForm,.calcSize')) manualSizeRevision++;
        },true);
        function suggestUploadForm(img) {
            // The calculator has an explicit 40 × 50 default; uploads must not replace it or a customer choice.
            if(document.querySelector('#generate.calculator-v2'))return;
            if (autoFormImages.has(img) || !(img.width > 0 && img.height > 0)) return;
            autoFormImages.add(img);
            var sizes=[];
            document.querySelectorAll('.accordion__sizes-block[data-id] .calcSize[data-size]').forEach(function(size){
                if(size.classList.contains('disabled') || size.getAttribute('aria-disabled')==='true') return;
                var m=(size.getAttribute('onclick') || '').match(/setSize\(\s*([\d.]+)\s*,\s*([\d.]+)/);
                if(m) sizes.push({w:+m[2],h:+m[1],element:size,form:size.closest('.accordion__sizes-block').getAttribute('data-id')});
            });
            var best=uploadSize(img.width/img.height,sizes,{w:Number(root.size_centimeter_w),h:Number(root.size_centimeter_h)});
            if(!best) return;
            var revision=manualSizeRevision;
            root.setTimeout(function(){
                var current=editor.grid[0] && editor.grid[0].resource;
                if(!current || current.img.original!==img || revision!==manualSizeRevision) return;
                var choice=document.querySelector('.calcForm[data-id="'+best.form+'"]');
                if(!choice) return;
                if(!choice.classList.contains('active')) choice.click();
                // Existing setSize cancels the form's delayed default-size selection.
                best.element.click();
            },0);
        }
        editor.render2D = function () {
            var cell = this.grid[0], resource = cell && cell.resource, info = null;
            if (resource) {
                var img = resource.img.original;
                suggestUploadForm(img);
                info = crop(img.width,img.height,this.canvas.width,this.canvas.height,resource.zoom,resource.rotate);
                var fitKey = [this.canvas.width,this.canvas.height,resource.rotate].join(':');
                if (resource.frontFitKey !== fitKey && info) {
                    resource.offset_x = info.extraX/2; resource.offset_y = info.extraY/2;
                    resource.frontFitKey = fitKey;
                }
                if (info) {
                    resource.offset_x = Math.max(0,Math.min(info.extraX,resource.offset_x));
                    resource.offset_y = Math.max(0,Math.min(info.extraY,resource.offset_y));
                }
                if (!ids.has(img)) ids.set(img,nextId++);
            }
            var currentSize = {w:Number(root.size_centimeter_w),h:Number(root.size_centimeter_h)};
            var key = JSON.stringify([this.canvas.width,this.canvas.height,this.ColorCell,currentSize,this.edge_mode,this.size_depth,
                resource && [ids.get(resource.img.original),resource.zoom,resource.rotate,resource.offset_x,resource.offset_y,resource.invert_x,resource.invert_y]]);
            if (key !== lastKey) {
                // Selection/hover outlines belong to editor UI, never to print textures.
                var overlay = this.onEndScene;
                this.onEndScene = null;
                try { oldRender.call(this); } finally { this.onEndScene = overlay; }
                if(this.edge_mode==='wrap')renderWrap();
                lastKey = key;
                if (resource) {
                    try {
                        sampleCtx.clearRect(0,0,32,32); sampleCtx.drawImage(this.canvas,0,0,32,32);
                        var pixels = sampleCtx.getImageData(0,0,32,32).data, totals=[0,0,0], count=0;
                        if (qa) {
                            var hash=2166136261;
                            for(var byte=0;byte<pixels.length;byte++) hash=Math.imul(hash^pixels[byte],16777619);
                            frontHash=(hash>>>0).toString(16);
                        }
                        for (var y=0;y<32;y++) for(var x=0;x<32;x++) {
                            if(x>0 && x<31 && y>0 && y<31) continue;
                            var p=(y*32+x)*4; for(var k=0;k<3;k++) totals[k]+=pixels[p+k]; count++;
                        }
                        this.edge_color='#'+totals.map(function(v){return Math.round(v/count).toString(16).padStart(2,'0');}).join('');
                    } catch (e) { this.edge_color='#eee9df'; }
                } else {this.edge_color='#eee9df';frontHash=null;}
                sync();
                // No explanatory messages below edge controls; keep crop calculations internal.
                warning.hidden = true;
                warning.textContent = '';
            }
        };
        var oldSetSizeBox = editor.setSizeBox;
        editor.setSizeBox = function () {
            var args=Array.prototype.slice.call(arguments);
            var cm=Math.max(Number(root.size_centimeter_w),Number(root.size_centimeter_h));
            if(cm>0)args[2]=3.5/cm;
            oldSetSizeBox.apply(this,args);
            // Update advice even when the animation loop is paused/offscreen.
            this.render2D();
        };
        editor.getCanvasConfiguration = function () {
            this.render2D();
            var r=this.grid[0] && this.grid[0].resource;
            return {version:2,edge_mode:this.edge_mode,edge_color:this.edge_color,print_layout:this.edge_mode==='wrap'?wrapLayout:null,
                front:{fit:'cover',zoom:r?r.zoom:1,rotation:r?r.rotate:0,offset_x:r?r.offset_x:0,offset_y:r?r.offset_y:0,
                    invert_x:r?!!r.invert_x:false,invert_y:r?!!r.invert_y:false,
                    source_width:r?r.img.original.width:null,source_height:r?r.img.original.height:null},
                aspect_ratio:this.size_width/this.size_height,depth_ratio:this.size_depth/this.size_width};
        };
        // Prepared contract only: backend print pipeline must explicitly adopt it.
        editor.canvas_ViewPort.getCanvasConfiguration = editor.getCanvasConfiguration.bind(editor);
        var uploader = document.getElementById('imgs');
        if (uploader) uploader.addEventListener('click', function(event) {
            if (!event.target.closest('.abs-close, span.delete')) return;
            event.preventDefault(); event.stopPropagation();
            if(editor.resetCanvasUpload)editor.resetCanvasUpload();
            if (Array.isArray(root.imgs)) root.imgs.forEach(function(item) {item.isLoad=false;item.img=null;});
            uploader.querySelectorAll('button').forEach(function(button) {button.classList.remove('is-load');});
            uploader.querySelectorAll('button img').forEach(function(img) {img.removeAttribute('src');img.removeAttribute('style');});
            uploader.querySelectorAll('input[type=file]').forEach(function(input) {input.value='';});
            uploader.querySelectorAll('.abs-close,.js-file-upload,.js-file-multiple').forEach(function(el) {el.style.display='none';});
            uploader.querySelectorAll('.js-file-preview').forEach(function(el) {el.style.display='';});
            document.querySelectorAll('.additional-image').forEach(function(el) {el.classList.add('additional-image_pending');el.classList.remove('additional-image_loaded');});
            editor.selection = undefined;
            editor.clearPhotos(); editor.render();
            if(editor.onchange)editor.onchange();
        }, true);
        // Edge selection changes the material only, never the page position.
        var edgeScrollPosition=null;
        ui.addEventListener('pointerdown',function(e){
            if(!e.target.closest('.edge-choice'))return;
            edgeScrollPosition={x:root.scrollX,y:root.scrollY};
            var input=e.target.closest('.edge-choice').querySelector('input');
            if(input)input.focus({preventScroll:true});
        },true);
        radios.forEach(function (radio) {radio.addEventListener('change',function () {
            if (!radio.checked) return;
            editor.edge_mode=radio.value; editor.add_history(); editor.render2D(); sync(); editor.render3D();
            if(editor.onchange)editor.onchange({edge:true});
            var position=edgeScrollPosition;edgeScrollPosition=null;
            if(position){
                root.scrollTo({left:position.x,top:position.y,behavior:'instant'});
                root.requestAnimationFrame(function(){root.scrollTo({left:position.x,top:position.y,behavior:'instant'});});
            }
        });});
        editor.history.forEach(function(state){state.edge_mode='wrap';state.edge_color=editor.edge_color;});
        if(root.CanvasOutpaint)root.CanvasOutpaint.install(editor,ui);
        else root.addEventListener('canvas-outpaint-ready',function(){root.CanvasOutpaint.install(editor,ui);},{once:true});
    }
    root.CanvasWrap = {uploadSize:uploadSize,crop:crop,uvs:uvs,foldedUvs:foldedUvs,bestSize:bestSize,install:install};
    if (typeof module !== 'undefined') module.exports = root.CanvasWrap;
})(typeof window !== 'undefined' ? window : globalThis);
