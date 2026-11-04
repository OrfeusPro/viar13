/* Lazy table scene sharing the editor's live geometry/maps; no second renderer. */
(function(root){
'use strict';
// One physical profile and one tiled, mitred face for both frame views.
function frameSource(card){var image=card&&card.querySelector('.img img');return card&&card.getAttribute('data-src')?(image&&(image.currentSrc||image.src)||card.getAttribute('data-src')):'';}
function frameProfile(card){
 var width=Number(card&&card.dataset.frameWidthMm)||25;
 var depth=Number(card&&card.dataset.frameDepthMm)||20;
 return {width:Math.max(1,width)/10,depth:Math.max(1,depth)/10};
}
function drawFrameFace(ctx,image,width,height,edge,cropProfile){
 if(!edge||!image)return;
 var iw=image.naturalWidth||image.width,ih=image.naturalHeight||image.height;
 // Preserve the aspect ratio of the source strip: repeat along, never stretch along a rail.
 // Imported samples include a mitred corner on the left and an exposed core on the right.
 // Use only the face between them. The corner extends diagonally by the sampled height;
 // stop at 60% of the strip, before the shortest photographed face ends.
 var sy=0,sh=cropProfile?ih*.80:ih;
 var sx=cropProfile?Math.max(iw*.35,sh+2):0;
 var sw=cropProfile?Math.max(1,iw*.60-sx):iw;
 // Mirror neighbouring samples: both sides of each join now share identical pixels.
 // One integer-sized repeat pattern also avoids gaps between fractional drawImage tiles.
 var tileWidth=Math.max(2,Math.round(edge*sw/sh)),tileHeight=Math.max(1,Math.ceil(edge));
 var strip=document.createElement('canvas');strip.width=tileWidth*2;strip.height=tileHeight;
 var stripCtx=strip.getContext('2d');
 stripCtx.drawImage(image,sx,sy,sw,sh,0,0,tileWidth,tileHeight);
 stripCtx.save();stripCtx.translate(tileWidth*2,0);stripCtx.scale(-1,1);
 stripCtx.drawImage(strip,0,0,tileWidth,tileHeight,0,0,tileWidth,tileHeight);stripCtx.restore();
 var facePattern=ctx.createPattern(strip,'repeat');
 function rail(length,polygon,tx,ty,angle){
  ctx.save();ctx.translate(tx,ty);ctx.rotate(angle);ctx.beginPath();
  polygon.forEach(function(p,i){if(i)ctx.lineTo(p[0],p[1]);else ctx.moveTo(p[0],p[1]);});ctx.closePath();ctx.clip();
  ctx.fillStyle=facePattern;ctx.fillRect(0,0,length,edge);
  var light=ctx.createLinearGradient(0,0,0,edge);light.addColorStop(0,'rgba(255,255,255,.12)');light.addColorStop(.75,'rgba(0,0,0,0)');light.addColorStop(1,'rgba(0,0,0,.23)');ctx.fillStyle=light;ctx.fillRect(0,0,length,edge);ctx.restore();
 }
 function shape(length){return [[0,0],[length,0],[length-edge,edge],[edge,edge]];}
 rail(width,shape(width),0,0,0);rail(height,shape(height),width,0,Math.PI/2);
 rail(width,shape(width),width,height,Math.PI);rail(height,shape(height),0,height,-Math.PI/2);
 ctx.save();ctx.strokeStyle='rgba(35,25,15,.22)';ctx.lineWidth=.65;
 [[0,0,edge,edge],[width,0,width-edge,edge],[width,height,width-edge,height-edge],[0,height,edge,height-edge]].forEach(function(p){ctx.beginPath();ctx.moveTo(p[0],p[1]);ctx.lineTo(p[2],p[3]);ctx.stroke();});ctx.restore();
}
root.CanvasRoom={install:function(editor){
 var host=document.querySelector('#generate.calculator-v2'), viewport=editor.canvas_ViewPort;
 if(!host)return;
 var ui=JSON.parse(document.getElementById('calculator-ui-text').textContent);
 var scene=null,camera=null,model=null,canvasMesh=null,frame=null,shadow=null,backdrop=null,wallShadow=null,source=null;
 var previewSizeKey='',originalRender=editor.render3D,frameKey='',frameTexture=null,frameGeneration=0;
 // Keep the previous studio view available via ?canvas_scene=classic.
 var sceneVariant=new URLSearchParams(location.search).get('canvas_scene')||'studio';
 var studioEnabled=sceneVariant==='studio',studioBox=null;
 var oakEnabled=sceneVariant!=='classic',ivoryConsole=sceneVariant!=='oak';
 var oakImage=new Image(),oakSurface=document.createElement('canvas'),oakTexture=null,oakKey='';
 var originalBackground=editor.scene.background;
 // Presentation-only sample: never inserts a customer upload or an order resource.
 var demoTexture=null;
 new THREE.TextureLoader().load('/theme/viar/images/canvas-demo-landscape-v1.webp',function(texture){demoTexture=texture;texture.minFilter=THREE.LinearFilter;editor.render3D();});
 if(oakEnabled&&!studioEnabled){oakImage.onload=function(){editor.render3D();};oakImage.src=ivoryConsole?'/theme/viar/images/canvas-ivory-console-v1.webp':'/theme/viar/images/canvas-light-oak-v1.webp';}
 function updateOakBackground(){
  if(!oakEnabled||(!studioEnabled&&(!oakImage.complete||!oakImage.naturalWidth))||!editor.box)return;
  editor.box.updateMatrixWorld(true);editor.camera.updateMatrixWorld(true);
  editor.box.geometry.computeBoundingBox();
  var bounds=editor.box.geometry.boundingBox;
  var edges=[bounds.min.y,bounds.max.y].map(function(y){return [[bounds.min.x,bounds.min.z],[bounds.max.x,bounds.min.z],[bounds.max.x,bounds.max.z],[bounds.min.x,bounds.max.z]].map(function(c){
   var p=new THREE.Vector3(c[0],y,c[1]).applyMatrix4(editor.box.matrixWorld).project(editor.camera);
   return {x:(p.x+1)/2,y:(1-p.y)/2};
  });});
  // The legacy geometry can have an inverted local Y axis.
  var points=edges.reduce(function(a,b){return a.reduce(function(sum,p){return sum+p.y;},0)>b.reduce(function(sum,p){return sum+p.y;},0)?a:b;});
  var bottom=Math.max.apply(null,points.map(function(p){return p.y;}));
  var contactTop=Math.min.apply(null,points.map(function(p){return p.y;}));
  var left=Math.min.apply(null,points.map(function(p){return p.x;})),right=Math.max.apply(null,points.map(function(p){return p.x;}));
  var w=Math.min(1600,Math.max(320,viewport.clientWidth)),h=Math.round(w*(viewport.clientHeight||600)/(viewport.clientWidth||800));
  var key=[w,h].concat(points.map(function(p){return p.x.toFixed(3)+','+p.y.toFixed(3);})).join(':');
  if(key!==oakKey){
   oakKey=key;oakSurface.width=w;oakSurface.height=h;var ctx=oakSurface.getContext('2d');
   var seam=Math.max(.1,Math.min(.89,contactTop-.045))*h,sourceSeam=Math.round(oakImage.naturalHeight*(ivoryConsole?.777:.805));
   if(studioEnabled){
    var wall=ctx.createLinearGradient(0,0,w,h);wall.addColorStop(0,'#deddd9');wall.addColorStop(.55,'#d2d1cd');wall.addColorStop(1,'#c5c4c0');ctx.fillStyle=wall;ctx.fillRect(0,0,w,h);
    var floor=ctx.createLinearGradient(0,seam,0,h);floor.addColorStop(0,'#f1ede4');floor.addColorStop(1,'#e2dcd0');ctx.fillStyle=floor;ctx.fillRect(0,seam,w,h-seam);
    var upper=edges[0]===points?edges[1]:edges[0];
    ctx.save();ctx.filter='blur(16px)';ctx.fillStyle='rgba(38,35,30,.30)';ctx.beginPath();
    var outline=[upper[0],upper[1],points[1],points[0]];
    outline.forEach(function(p,i){var x=p.x*w-24,y=p.y*h+5;if(i)ctx.lineTo(x,y);else ctx.moveTo(x,y);});ctx.closePath();ctx.fill();ctx.restore();
   }else{
   ctx.drawImage(oakImage,0,0,oakImage.naturalWidth,sourceSeam,0,0,w,seam);
   if(ivoryConsole){
    var sourceLip=Math.round(oakImage.naturalHeight*.85),lip=Math.min(h-2,Math.max(seam+8,(bottom+.025)*h));
    ctx.drawImage(oakImage,0,sourceSeam,oakImage.naturalWidth,sourceLip-sourceSeam,0,seam,w,lip-seam);
    ctx.drawImage(oakImage,0,sourceLip,oakImage.naturalWidth,oakImage.naturalHeight-sourceLip,0,lip,w,h-lip);
   }else ctx.drawImage(oakImage,0,sourceSeam,oakImage.naturalWidth,oakImage.naturalHeight-sourceSeam,0,seam,w,h-seam);
   }
   // Contact shadow follows the complete lower edge, including the receding corner.
   [12,4].forEach(function(blur){
    ctx.save();ctx.filter='blur('+blur+'px)';ctx.fillStyle=blur===12?'rgba(45,34,22,.22)':'rgba(45,34,22,.35)';
    ctx.beginPath();points.forEach(function(p,i){if(i===0)ctx.moveTo(p.x*w,p.y*h+2);else ctx.lineTo(p.x*w,p.y*h+2);});ctx.closePath();ctx.fill();
    ctx.lineWidth=3;ctx.strokeStyle=ctx.fillStyle;ctx.stroke();ctx.restore();
   });
   if(!oakTexture){oakTexture=new THREE.CanvasTexture(oakSurface);oakTexture.minFilter=THREE.LinearFilter;oakTexture.generateMipmaps=false;}oakTexture.needsUpdate=true;
  }
  editor.scene.background=oakTexture;
 }

 var personMeasure=document.createElement('div');personMeasure.className='calculator-person-height';personMeasure.hidden=true;personMeasure.innerHTML='<span>170 cm</span>';viewport.parentElement.appendChild(personMeasure);
 var frameRoomKind='living',frameRoomTextures={},roomKind='living', furniture=null,person=null,roomTextures={};
 var frameFace=null,frameFaceTexture=null,frameFaceKey='';
 // Pixel anchors measured in the generated 1254 x 1254 interior source images.
 // A single metres-per-pixel factor applies equally to X and Y.
 var roomCalibration={living:{left:294,right:976,floor:987,width:2.10},console:{left:352,right:877,floor:1032,width:1.40},office:{left:44,right:1210,floor:1150,width:1166*.80/385,minBottom:1.45},bedroom:{left:270,right:990,floor:1135,width:1.60,minBottom:1.28},dining:{left:195,right:1064,floor:1138,width:1.80},loft:{left:134,right:1108,floor:1095,width:1.80}};
 var cropHandle=document.createElement('div');cropHandle.className='calculator-room-crop';cropHandle.tabIndex=0;cropHandle.setAttribute('role','group');
 cropHandle.setAttribute('aria-label',ui.move_photo);
 cropHandle.title=cropHandle.getAttribute('aria-label');cropHandle.hidden=true;viewport.parentElement.appendChild(cropHandle);
 var cropDrag=null;
 function cropPoint(e){var r=cropHandle.getBoundingClientRect();return {x:Math.max(.001,Math.min(.999,(e.clientX-r.left)/r.width)),y:Math.max(.001,Math.min(.999,(e.clientY-r.top)/r.height))};}
 function cropEvent(type,p){editor.dispatch(type,{SinglePos:p});editor.render();}
 function finishCrop(){if(!cropDrag)return;var drag=cropDrag;cropDrag=null;cropEvent('mouseup',drag.point);cropHandle.classList.remove('is-dragging');}
 cropHandle.addEventListener('pointerdown',function(e){if(e.button!==0||cropDrag)return;e.preventDefault();e.stopPropagation();var p=cropPoint(e);var cell=editor.getCellByPoint(p);if(!cell||!cell.resource)return;cropHandle.focus({preventScroll:true});cropDrag={id:e.pointerId,point:p};cropHandle.setPointerCapture(e.pointerId);cropHandle.classList.add('is-dragging');cropEvent('mousedown',p);});
 cropHandle.addEventListener('pointermove',function(e){if(!cropDrag||e.pointerId!==cropDrag.id)return;e.preventDefault();cropDrag.point=cropPoint(e);cropEvent('mousemove',cropDrag.point);});
 ['pointerup','pointercancel','lostpointercapture'].forEach(function(type){cropHandle.addEventListener(type,finishCrop);});
 cropHandle.addEventListener('keydown',function(e){var delta={ArrowLeft:[-.04,0],ArrowRight:[.04,0],ArrowUp:[0,-.04],ArrowDown:[0,.04]}[e.key];if(!delta)return;e.preventDefault();var p={x:.5,y:.5},cell=editor.getCellByPoint(p);if(!cell||!cell.resource)return;cropEvent('mousedown',p);cropEvent('mousemove',{x:p.x+delta[0],y:p.y+delta[1]});cropEvent('mouseup',p);});
 var qa=new URLSearchParams(location.search).has('canvas_qa');
 var pixelRatio=Math.min(root.devicePixelRatio||1,1.5);editor.renderer.setPixelRatio(pixelRatio);
 function init(){
  if(scene)return;
  scene=new THREE.Scene();camera=new THREE.OrthographicCamera(-1.2,1.2,1,-.6,.01,100);
  backdrop=new THREE.Mesh(new THREE.PlaneGeometry(30,20),new THREE.MeshBasicMaterial({color:0xf4eee5}));
  backdrop.position.set(0,4,-1.2);scene.add(backdrop);
  furniture=new THREE.Group();scene.add(furniture);buildRoom();
  var shadowImage=document.createElement('canvas');shadowImage.width=128;shadowImage.height=64;
  var ctx=shadowImage.getContext('2d'),gradient=ctx.createRadialGradient(64,32,0,64,32,64);
  gradient.addColorStop(0,'rgba(35,29,22,.32)');gradient.addColorStop(.4,'rgba(35,29,22,.18)');gradient.addColorStop(1,'rgba(35,29,22,0)');ctx.fillStyle=gradient;ctx.fillRect(0,0,128,64);
  var shadowTexture=new THREE.CanvasTexture(shadowImage);
  shadow=new THREE.Mesh(new THREE.PlaneGeometry(1,1),new THREE.MeshBasicMaterial({map:shadowTexture,transparent:true,depthWrite:false}));shadow.position.set(0,.005,-.08);scene.add(shadow);
  var wallImage=document.createElement('canvas');wallImage.width=128;wallImage.height=128;
  var wallCtx=wallImage.getContext('2d');wallCtx.shadowColor='rgba(40,30,20,.30)';wallCtx.shadowBlur=10;wallCtx.fillStyle='rgba(40,30,20,.12)';wallCtx.fillRect(14,14,100,100);
  wallShadow=new THREE.Mesh(new THREE.PlaneGeometry(1,1),new THREE.MeshBasicMaterial({map:new THREE.CanvasTexture(wallImage),transparent:true,depthWrite:false}));wallShadow.position.z=-.15;scene.add(wallShadow);
  model=new THREE.Group();model.rotation.x=0;model.rotation.y=0;scene.add(model);
  frame=new THREE.Group();model.add(frame);
  for(var i=0;i<4;i++)frame.add(new THREE.Mesh(new THREE.BoxGeometry(1,1,1),new THREE.MeshBasicMaterial({color:0x635244})));
  frameFace=new THREE.Mesh(new THREE.PlaneGeometry(1,1),new THREE.MeshBasicMaterial({transparent:true,depthWrite:false}));model.add(frameFace);
 }
 // Background texture coordinates preserve furniture scale. Clamped border pixels
 // extend only the outer wall/floor margins when the viewport is wider than the photo.
 function buildRoom(){
  var calibration=roomCalibration[roomKind],unit=calibration.width/(calibration.right-calibration.left);
  var imageWidth=1254*unit,imageHeight=1254*unit;
  var texture=roomTextures[roomKind];
  if(!texture){texture=new THREE.TextureLoader().load('/theme/viar/images/room-'+roomKind+'-photo-v3.webp'+(roomKind==='office'?'?office80=2':''),function(){buildRoom();editor.render3D();});roomTextures[roomKind]=texture;}
  texture.wrapS=texture.wrapT=THREE.ClampToEdgeWrapping;texture.minFilter=THREE.LinearFilter;texture.generateMipmaps=false;
  texture.repeat.set(30/imageWidth,20/imageHeight);texture.offset.set(.5-15/imageWidth,.5-10/imageHeight);
  backdrop.material.color.set(texture.image?0xffffff:0xf3eee7);backdrop.material.map=texture.image?texture:null;backdrop.material.needsUpdate=true;
  backdrop.position.set((627-(calibration.left+calibration.right)/2)*unit,(calibration.floor-627)*unit,-1.2);
  if(!person){
   var personTexture=new THREE.TextureLoader().load('/theme/viar/images/room-person-photo-v2.webp',function(){editor.render3D();});
   // Actual hair-to-sole alpha bounds are y=34..1496; displayed height is 170 cm.
   var personUnit=1.70/(1496-34);
   person=new THREE.Mesh(new THREE.PlaneGeometry(1024*personUnit,1536*personUnit),new THREE.MeshBasicMaterial({map:personTexture,transparent:true,alphaTest:.9,depthWrite:false}));
   furniture.add(person);person.position.y=(1496-768)*personUnit;
  }
  person.position.x=roomCalibration[roomKind].width/2+.35;person.position.z=.5;
  person.visible=false;
 }
 function fit(camera,bounds,w,h,room,matrix){
  if(w<=0||h<=0||bounds.isEmpty())return;
  var center=bounds.getCenter(new THREE.Vector3());if(matrix)center.applyMatrix4(matrix);
  camera.aspect=w/h;
  var direction=new THREE.Vector3(room?.18:.12,room?.18:.08,1).normalize();
  var right=new THREE.Vector3().crossVectors(new THREE.Vector3(0,1,0),direction).normalize();
  var up=new THREE.Vector3().crossVectors(direction,right).normalize();
  var tanY=Math.tan(camera.fov*Math.PI/360),tanX=tanY*camera.aspect;
  // Fit all eight actual bounds corners, reserving space for controls and contact shadow.
  // Mobile controls already occupy CSS gutters outside the renderer.
  // Reserve only a small edge/shadow margin here, not another toolbar width.
  var mobile=root.innerWidth<1000;
  var fillX=mobile&&!room?.96:Math.max(.65,(w-80)/w),fillY=room?.82:mobile?.95:.92,distance=0;
  [bounds.min.x,bounds.max.x].forEach(function(x){[bounds.min.y,bounds.max.y].forEach(function(y){[bounds.min.z,bounds.max.z].forEach(function(z){
   var point=new THREE.Vector3(x,y,z);if(matrix)point.applyMatrix4(matrix);point.sub(center);var depth=point.dot(direction);
   distance=Math.max(distance,depth+Math.abs(point.dot(right))/(tanX*fillX),depth+Math.abs(point.dot(up))/(tanY*fillY));
  });});});
  camera.position.copy(center).add(direction.multiplyScalar(distance));
  camera.lookAt(center);camera.updateProjectionMatrix();
 }
 editor.fitPreview=function(w,h){
  if(!editor.box)return;
  if(studioEnabled&&studioBox!==editor.box){studioBox=editor.box;editor.box.rotation.x=-.10;editor.box.rotation.y=.60;}
  // Keep the display edge as readable as the 30 x 40 reference.
  // Only mesh presentation changes; physical depth and print UVs remain untouched.
  editor.box.scale.z=4.0/(editor.size_depth*Math.max(Number(root.size_centimeter_w)||30,Number(root.size_centimeter_h)||40));
  editor.box.updateMatrixWorld(true);
  editor.box.geometry.computeBoundingBox();
  fit(editor.camera,editor.box.geometry.boundingBox,w,h,false,editor.box.matrixWorld);
 };
 function update(){
  if(!editor.box)return;
  init();
  if(source!==editor.box){
   if(canvasMesh){model.remove(canvasMesh);[].concat(canvasMesh.material).forEach(function(m){m.dispose();});}
   source=editor.box;
   // Basic material keeps source photo values independent of room lighting.
   canvasMesh=new THREE.Mesh(source.geometry,[].concat(source.material).map(function(){return new THREE.MeshBasicMaterial({side:THREE.DoubleSide});}));
   canvasMesh.scale.copy(source.scale);canvasMesh.scale.z=editor.box.scale.z;model.add(canvasMesh);
  }
  [].concat(source.material).forEach(function(m,i){var target=canvasMesh.material[i];if(!target)return;if(target.map!==m.map){target.map=m.map;target.needsUpdate=true;}target.color.copy(m.color);});
  var w=editor.size_width,h=editor.size_height,d=editor.size_depth*editor.box.scale.z;
  var cmw=Number(root.size_centimeter_w)||30,cmh=Number(root.size_centimeter_h)||40;
  var scale=Math.max(cmw,cmh)/100;
  // Presentation scale only: order dimensions and frame profile calculations stay unchanged.
  var previewScale=scale;
  model.scale.set(previewScale,previewScale,previewScale);
  var active=host.querySelector('.calculator-demo-frame.is-preview')||host.querySelector('.ramu-item.active'),src=frameSource(active);
  var profile=frameProfile(active),hasFrame=!!src,thickness=hasFrame?profile.width/100/scale:0;
  frame.visible=hasFrame;
  if(src!==frameKey){
   frameKey=src;var generation=++frameGeneration;
   if(frameTexture){frameTexture.dispose();frameTexture=null;}
   frame.children.forEach(function(bar){bar.material.map=null;bar.material.color.set(0xe3ddd4);bar.material.needsUpdate=true;});
   if(src)new THREE.TextureLoader().load(src,function(texture){
    if(generation!==frameGeneration){texture.dispose();return;}
    frameTexture=texture;frameFaceKey='';editor.render3D();
   });
  }
  if(hasFrame){
   [[w+2*thickness,thickness,0,(h+thickness)/2],[w+2*thickness,thickness,0,-(h+thickness)/2],[thickness,h,(w+thickness)/2,0],[thickness,h,-(w+thickness)/2,0]].forEach(function(v,i){var bar=frame.children[i];bar.scale.set(v[0],v[1],Math.max(d,profile.depth/100/scale));bar.position.set(v[2],v[3],0);});
  }
  frameFace.visible=hasFrame&&!!frameTexture;
  if(hasFrame&&frameTexture){
   var faceKey=[src,w,h,thickness].join(':');
   if(faceKey!==frameFaceKey){
    frameFaceKey=faceKey;var face=document.createElement('canvas'),factor=1000/Math.max(w+2*thickness,h+2*thickness);
    face.width=Math.round((w+2*thickness)*factor);face.height=Math.round((h+2*thickness)*factor);
    drawFrameFace(face.getContext('2d'),frameTexture.image,face.width,face.height,thickness*factor,active&&active.dataset.profileSample==='true');
    if(frameFaceTexture)frameFaceTexture.dispose();frameFaceTexture=new THREE.CanvasTexture(face);
    frameFace.material.map=frameFaceTexture;frameFace.material.needsUpdate=true;
   }
   frameFace.scale.set(w+2*thickness,h+2*thickness,1);frameFace.position.z=Math.max(d,profile.depth/100/scale)/2+.0005;
  }
  model.position.set(0,0,0);model.updateMatrixWorld(true);
  var bounds=new THREE.Box3().setFromObject(hasFrame?model:canvasMesh);
  // Bottom rests on the console; the back clears the wall even after a size/frame change.
  model.position.y=Math.max(roomCalibration[roomKind].minBottom||1.02,1.55-(bounds.max.y-bounds.min.y)/2)-bounds.min.y;model.position.z=.04-bounds.min.z;model.updateMatrixWorld(true);
  bounds.setFromObject(hasFrame?model:canvasMesh);
  shadow.visible=false;shadow.scale.set((bounds.max.x-bounds.min.x)*1.17,.065,1);shadow.position.x=.025;
  wallShadow.scale.set((bounds.max.x-bounds.min.x)*1.18,(bounds.max.y-bounds.min.y)*1.12,1);
  wallShadow.position.set(.025,(bounds.max.y+bounds.min.y)/2-.015,-.15);
  // Fit the whole vignette on portrait screens; never stretch furniture or the photo.
  var aspect=(viewport.clientWidth||1)/(viewport.clientHeight||1);
  var withPerson=personToggle.checked&&!host.classList.contains('is-frame-selection');person.visible=withPerson;
  // Draw the height guide inside the scene so fullscreen and screenshots retain it.
  if(!person.userData.heightGuide){
   var guide=new THREE.Group(),lineMaterial=new THREE.LineBasicMaterial({color:0x405454,depthTest:false});
   var points=[new THREE.Vector3(0,0,0),new THREE.Vector3(0,1.70,0),new THREE.Vector3(-.035,0,0),new THREE.Vector3(.035,0,0),new THREE.Vector3(-.035,1.70,0),new THREE.Vector3(.035,1.70,0)];
   guide.add(new THREE.LineSegments(new THREE.BufferGeometry().setFromPoints(points),lineMaterial));
   var labelCanvas=document.createElement('canvas');labelCanvas.width=256;labelCanvas.height=96;
   var labelContext=labelCanvas.getContext('2d');labelContext.fillStyle='rgba(255,255,255,.94)';labelContext.fillRect(0,0,256,96);labelContext.fillStyle='#263b3b';labelContext.font='600 48px Arial';labelContext.textAlign='center';labelContext.textBaseline='middle';labelContext.fillText('170 cm',128,48);
   var labelMesh=new THREE.Mesh(new THREE.PlaneGeometry(.40,.15),new THREE.MeshBasicMaterial({map:new THREE.CanvasTexture(labelCanvas),transparent:true,depthTest:false}));labelMesh.position.set(.24,.85,.01);guide.add(labelMesh);scene.add(guide);person.userData.heightGuide=guide;
  }
  person.userData.heightGuide.position.set(person.position.x+.34,0,.6);
  person.userData.heightGuide.visible=withPerson;

  var sceneWidth=roomCalibration[roomKind].width+1.05;
  // Fixed room camera: selected print size must never rescale furniture.
  var viewHeight=3.15,viewWidth=Math.max(sceneWidth,3.15);
  if(viewWidth/viewHeight>aspect)viewHeight=viewWidth/aspect;else viewWidth=viewHeight*aspect;
  var centerY=1.475,centerX=0;
  if(host.classList.contains('is-frame-selection')){
   // Frame selection crops the room around the artwork, preserving its physical scale.
   var artWidth=bounds.max.x-bounds.min.x,artHeight=bounds.max.y-bounds.min.y;
   // Leave more of the interior visible on desktop (20% smaller artwork).
   var frameViewPadding=root.innerWidth>=1000?1.4:1.12;
   viewHeight=artHeight*frameViewPadding;viewWidth=viewHeight*aspect;
   if(viewWidth<artWidth*frameViewPadding){viewWidth=artWidth*frameViewPadding;viewHeight=viewWidth/aspect;}
   centerY=(bounds.max.y+bounds.min.y)/2;centerX=(bounds.max.x+bounds.min.x)/2;
   var ft=frameRoomTextures[frameRoomKind];
   if(!ft){ft=new THREE.TextureLoader().load('/theme/viar/images/frame-room-'+frameRoomKind+'-v1.webp',function(){editor.render3D();});frameRoomTextures[frameRoomKind]=ft;}
   ft.wrapS=ft.wrapT=THREE.ClampToEdgeWrapping;ft.minFilter=THREE.LinearFilter;ft.generateMipmaps=false;
   var imageHeight=Math.max(viewHeight,viewWidth*1.5),imageCenterX=.5,imageCenterY=.5;
   if(frameRoomKind==='living'){
    // Fit the complete outer frame into the clear wall above the chair (source-image coordinates).
    // Zoom the room with the close-up, keeping the artwork large without painting over furniture.
    imageHeight=Math.max(imageHeight,artHeight/.44,artWidth*1.5/.65);
    imageCenterX=.48;imageCenterY=.73;
   }
   var imageWidth=imageHeight/1.5;
   ft.repeat.set(30/imageWidth,20/imageHeight);ft.offset.set(imageCenterX-15/imageWidth,imageCenterY-10/imageHeight);
   backdrop.material.map=ft.image?ft:null;backdrop.material.color.set(ft.image?0xffffff:0xf3eee7);backdrop.material.needsUpdate=true;
   backdrop.position.set(centerX,centerY,-1.2);
   // Mobile fitting uses an uncluttered wall, without foreground furniture.
   if(root.innerWidth<1000){backdrop.material.map=null;backdrop.material.color.set(frameRoomKind==='loft'?0xd9d7d2:frameRoomKind==='console'?0xe5e1d6:0xf3eee7);backdrop.material.needsUpdate=true;}
  
  }
  camera.left=centerX-viewWidth/2;camera.right=centerX+viewWidth/2;camera.top=centerY+viewHeight/2;camera.bottom=centerY-viewHeight/2;
  camera.position.set(0,0,5);camera.lookAt(new THREE.Vector3(0,0,0));camera.updateProjectionMatrix();
  var displayScale=parseFloat(getComputedStyle(host).zoom)||1;
  var frontBounds=new THREE.Box3().setFromObject(canvasMesh),vr=viewport.getBoundingClientRect(),pr=viewport.parentElement.getBoundingClientRect();
  personMeasure.hidden=true;
  if(withPerson){
   personMeasure.style.left=(vr.left-pr.left+(person.position.x+.34-camera.left)/viewWidth*vr.width)/displayScale+'px';
   personMeasure.style.top=(vr.top-pr.top+(camera.top-1.70)/viewHeight*vr.height)/displayScale+'px';
   personMeasure.style.height=(1.70/viewHeight*vr.height)/displayScale+'px';
  }
  cropHandle.hidden=!host.querySelector('#imgs .is-load img');
  updatePhotoZoomButtons();
  cropHandle.style.left=(vr.left-pr.left+(frontBounds.min.x-camera.left)/viewWidth*vr.width)/displayScale+'px';
  cropHandle.style.top=(vr.top-pr.top+(camera.top-frontBounds.max.y)/viewHeight*vr.height)/displayScale+'px';
  cropHandle.style.width=((frontBounds.max.x-frontBounds.min.x)/viewWidth*vr.width)/displayScale+'px';
  cropHandle.style.height=((frontBounds.max.y-frontBounds.min.y)/viewHeight*vr.height)/displayScale+'px';
  editor.renderer.setClearColor(0xf3eee7,1);editor.renderer.render(scene,camera);
  var sizeLabel=host.querySelector('.calculator-room-dimensions');if(sizeLabel)sizeLabel.textContent=cmw+' × '+cmh+' cm · '+(roomKind==='living'?roomText.sofa:roomKind==='console'?roomText.consoleSize:roomKind==='office'?roomText.desk:roomText[roomKind+'Size'])+(withPerson?' · '+roomText.personSize:'');
  var upload=host.querySelector('.calculator-photo-overlay');if(upload){upload.style.setProperty('left',((bounds.min.x+bounds.max.x)/2-camera.left)/viewWidth*100+'%');upload.style.setProperty('top',(camera.top-(bounds.min.y+bounds.max.y)/2)/viewHeight*100+'%');}
  if(sizeLabel&&host.classList.contains('is-frame-selection'))sizeLabel.textContent=cmw+' × '+cmh+' cm';
  if(qa)viewport.dataset.roomQa=JSON.stringify({active:true,width:cmw,height:cmh,frame:active&&active.dataset.id,mode:editor.edge_mode,bottom:bounds.min.y,wallClearance:bounds.min.z+.35,memory:editor.renderer.info.memory,pixelRatio:pixelRatio,viewWidth:viewWidth,viewHeight:viewHeight});
 }
 host.addEventListener('frame-gallery-render',function(event){
  var canvas=event.detail.canvas,card=event.detail.card,src=frameSource(card),profile=frameProfile(card);
  var cmw=Number(root.size_centimeter_w)||30,cmh=Number(root.size_centimeter_h)||40;
  var scale=640/Math.max(cmw+(src?2*profile.width:0),cmh+(src?2*profile.width:0));
  var w=Math.round(cmw*scale),h=Math.round(cmh*scale),edge=src?Math.round(profile.width*scale):0;
  canvas.width=w+edge*2;canvas.height=h+edge*2;
  var ctx=canvas.getContext('2d');ctx.fillStyle='#dedbd5';ctx.fillRect(0,0,canvas.width,canvas.height);
  var loaded=!!host.querySelector('#imgs .is-load img');
  if(loaded&&editor.canvas){
   if(editor.edge_mode==='wrap'&&editor.getPrintComposition){var composition=editor.getPrintComposition(),front=editor.getCanvasConfiguration().print_layout.front;if(composition.width&&composition.height&&front.width&&front.height)ctx.drawImage(composition,front.x,front.y,front.width,front.height,edge,edge,w,h);}
   else if(editor.canvas.width&&editor.canvas.height)ctx.drawImage(editor.canvas,edge,edge,w,h);
  }else{ctx.fillStyle='#6b655e';ctx.textAlign='center';ctx.font='18px sans-serif';ctx.fillText(ui.upload_in_calculator,canvas.width/2,canvas.height/2,Math.max(1,w-24));}
  if(edge){var image=new Image();image.onload=function(){drawFrameFace(ctx,image,canvas.width,canvas.height,edge,card.dataset.profileSample==='true');};image.src=src;}
 });
 var framePreviewImage=null,framePreviewSource='';
 function updateFramePreview(){
  var canvas=host.querySelector('.calculator-frame-canvas');if(!host.classList.contains('is-frame-selection')||!canvas)return;
  var active=host.querySelector('.calculator-demo-frame.is-preview')||host.querySelector('.ramu-item.active'),src=frameSource(active);
  if(src!==framePreviewSource){framePreviewSource=src;framePreviewImage=null;if(src){var img=new Image();img.onload=function(){if(framePreviewSource===src){framePreviewImage=img;updateFramePreview();}};img.src=src;}}
  var cmw=Number(root.size_centimeter_w)||30,cmh=Number(root.size_centimeter_h)||40;
  var profile=frameProfile(active),scale=700/Math.max(cmw+(src?2*profile.width:0),cmh+(src?2*profile.width:0)),w=Math.round(cmw*scale),h=Math.round(cmh*scale),edge=src?Math.round(profile.width*scale):0;
  canvas.width=w+edge*2;canvas.height=h+edge*2;var ctx=canvas.getContext('2d');ctx.fillStyle='#dedbd5';ctx.fillRect(0,0,canvas.width,canvas.height);
  var loaded=!!host.querySelector('#imgs .is-load img');if(loaded&&editor.canvas){if(editor.edge_mode==='wrap'&&editor.getPrintComposition){var composition=editor.getPrintComposition(),layout=editor.getCanvasConfiguration().print_layout,front=layout.front;if(composition.width&&composition.height&&front.width&&front.height)ctx.drawImage(composition,front.x,front.y,front.width,front.height,edge,edge,w,h);}else if(editor.canvas.width&&editor.canvas.height)ctx.drawImage(editor.canvas,edge,edge,w,h);}
  if(edge&&framePreviewImage)drawFrameFace(ctx,framePreviewImage,canvas.width,canvas.height,edge,active&&active.dataset.profileSample==='true');
  if(qa)canvas.dataset.frameQa=JSON.stringify({width:cmw,height:cmh,profileWidthCm:src?profile.width:0,outerWidth:cmw+(src?2*profile.width:0),outerHeight:cmh+(src?2*profile.width:0),edgePx:edge,widthPx:canvas.width,frameId:active&&active.dataset.id});
  var name=active&&active.querySelector('.code');host.querySelector('.calculator-frame-preview-caption').textContent=cmw+' × '+cmh+' cm'+(name?' · '+name.textContent.trim():'')+(loaded?'':(' · '+ui.upload_in_calculator));
 }
 host.addEventListener('frame-preview-update',updateFramePreview);
 // Rounded preview geometry keeps original print geometry and UV calculations intact.
 var roundedSource=null,roundedKey='',roundedGeometry=null;
 function roundedPreviewGeometry(box){
  var g=box.geometry;
  var key=JSON.stringify([box.scale.z,g.vertices.map(function(v){return [v.x,v.y,v.z];}),g.faceVertexUvs[0],g.faces.map(function(f){return f.materialIndex;})]);
  if(roundedSource===g&&roundedKey===key)return roundedGeometry;
  if(roundedGeometry)roundedGeometry.dispose();
  roundedSource=g;roundedKey=key;roundedGeometry=new THREE.Geometry();g.computeBoundingBox();
  var min=g.boundingBox.min,max=g.boundingBox.max,center=min.clone().add(max).multiplyScalar(.5);
  var half=max.clone().sub(min).multiplyScalar(.5);half.z*=box.scale.z;
  var radius=Math.min(half.x,half.y,half.z)*.16;
  function add(a,b,c,ua,ub,uc,material,level){
   if(level){var ab=a.clone().add(b).multiplyScalar(.5),bc=b.clone().add(c).multiplyScalar(.5),ca=c.clone().add(a).multiplyScalar(.5);
    var uab=ua.clone().add(ub).multiplyScalar(.5),ubc=ub.clone().add(uc).multiplyScalar(.5),uca=uc.clone().add(ua).multiplyScalar(.5);
    add(a,ab,ca,ua,uab,uca,material,level-1);add(ab,b,bc,uab,ub,ubc,material,level-1);add(ca,bc,c,uca,ubc,uc,material,level-1);add(ab,bc,ca,uab,ubc,uca,material,level-1);return;}
   var start=roundedGeometry.vertices.length;
   [a,b,c].forEach(function(v){var q=v.clone().sub(center);q.z*=box.scale.z;
    var inner=new THREE.Vector3(Math.max(-half.x+radius,Math.min(half.x-radius,q.x)),Math.max(-half.y+radius,Math.min(half.y-radius,q.y)),Math.max(-half.z+radius,Math.min(half.z-radius,q.z)));
    q.sub(inner).normalize().multiplyScalar(radius).add(inner);q.z/=box.scale.z;q.add(center);roundedGeometry.vertices.push(q);});
   var face=new THREE.Face3(start,start+1,start+2);face.materialIndex=material;roundedGeometry.faces.push(face);roundedGeometry.faceVertexUvs[0].push([ua.clone(),ub.clone(),uc.clone()]);
  }
  g.faces.forEach(function(f,i){var uv=g.faceVertexUvs[0][i];add(g.vertices[f.a],g.vertices[f.b],g.vertices[f.c],uv[0],uv[1],uv[2],f.materialIndex,3);});
  roundedGeometry.computeFaceNormals();return roundedGeometry;
 }
 editor.render3D=function(){
  updateFramePreview();
  if(host.dataset.previewMode==='room'){if(!host.classList.contains('is-frame-selection')&&scene)buildRoom();update();return;}
  cropHandle.hidden=true;personMeasure.hidden=true;
  var key=[editor.size_width,editor.size_height,editor.size_depth].join(':');
  if(key!==previewSizeKey){previewSizeKey=key;editor.fitPreview(viewport.clientWidth||1,viewport.clientHeight||1);}
  var useOak=oakEnabled&&!host.classList.contains('is-frame-selection');
  if(useOak)updateOakBackground();else editor.scene.background=originalBackground;
  if(editor.ShadowPlane)editor.ShadowPlane.visible=!useOak;
  var demoMaterials=[];
  if(demoTexture&&editor.box&&!host.querySelector('#imgs .is-load img')){
   [].concat(editor.box.material).slice(0,1).forEach(function(material){demoMaterials.push([material,material.map]);material.map=demoTexture;material.needsUpdate=true;});
  }
  var printGeometry=editor.box&&editor.box.geometry;
  if(printGeometry)editor.box.geometry=roundedPreviewGeometry(editor.box);
  editor.renderer.setClearColor(0xFAF7F2,1);originalRender.call(editor);
  if(printGeometry)editor.box.geometry=printGeometry;
  demoMaterials.forEach(function(entry){entry[0].map=entry[1];entry[0].needsUpdate=true;});
  // Anchor the DOM upload control to the projected centre of the canvas face.
  // Viewport padding and the mobile tool rail must not shift it away from the artwork.
  var upload=host.querySelector('.calculator-photo-overlay');
  if(upload && editor.box && editor.camera){
   editor.box.geometry.computeBoundingBox();
   var bounds=editor.box.geometry.boundingBox;
   var centre=new THREE.Vector3((bounds.min.x+bounds.max.x)/2,(bounds.min.y+bounds.max.y)/2,bounds.max.z);
   centre.applyMatrix4(editor.box.matrixWorld).project(editor.camera);
   var screen=editor.renderer.domElement.getBoundingClientRect(),parent=upload.offsetParent;
   if(parent && screen.width && screen.height){
    var origin=parent.getBoundingClientRect(),displayScale=parseFloat(getComputedStyle(host).zoom)||1;
    upload.style.left=((screen.left-origin.left+(centre.x+1)*screen.width/2)/displayScale-parent.clientLeft)+'px';
    upload.style.top=((screen.top-origin.top+(1-centre.y)*screen.height/2)/displayScale-parent.clientTop)+'px';
   }
  }
 };
 // Interior is a presentation view. Editing gestures remain exclusive to the editor.
 ['mousedown','touchstart','pointerdown'].forEach(function(type){viewport.addEventListener(type,function(e){if(host.dataset.previewMode==='room')e.stopImmediatePropagation();},{capture:true,passive:true});});
 viewport.addEventListener('wheel',function(e){if(host.dataset.previewMode==='room')e.stopImmediatePropagation();},{capture:true,passive:true});
 // The preview uses one live object/state in both modes; legacy UI remains behind the flag.
 host.addEventListener('click',function(e){var tab=e.target.closest('.slider-tabs a[data-tabs]');if(!tab)return;
  e.preventDefault();e.stopImmediatePropagation();
  finishCrop();
  host.classList.remove('is-frame-selection');var frameButton=host.querySelector('.calculator-frame-open');frameButton.classList.remove('active');frameButton.setAttribute('aria-selected','false');
  host.dataset.previewMode=tab.dataset.tabs==='tabs-item2'?'room':'edit';
  if(tab.dataset.tabs==='tabs-item1')host.dispatchEvent(new Event('canvas-view-open'));
  host.querySelectorAll('.slider-tabs a[data-tabs]').forEach(function(a){a.classList.toggle('active',a===tab);a.setAttribute('aria-selected',String(a===tab));});
  editor.fitPreview(viewport.clientWidth||1,viewport.clientHeight||1);editor.render3D();
  if(host.classList.contains('calculator-fit'))requestAnimationFrame(function(){host.scrollIntoView({block:'start'});});
 },true);
 var russian=(document.documentElement.lang||'ru').indexOf('ru')===0;
 var roomText={label:ui.room_label,living:ui.living,console:ui.console,office:ui.office,person:ui.person,personSize:ui.person_size,sofa:ui.sofa,consoleSize:ui.console_size,desk:ui.desk,note:ui.scale_note};
 Object.assign(roomText,JSON.parse(document.getElementById('calculator-room-text').textContent));
 var roomTools=document.createElement('div');roomTools.className='calculator-room-picker';
 var roomSelect=document.createElement('select');roomSelect.className='jcf-ignore';roomSelect.setAttribute('aria-label',roomText.label);
 Object.keys(roomCalibration).forEach(function(key){var option=document.createElement('option');option.value=key;option.textContent=roomText[key];roomSelect.appendChild(option);});
 var personLabel=document.createElement('label');var personToggle=document.createElement('input');personToggle.type='checkbox';personToggle.className='jcf-ignore';personLabel.appendChild(personToggle);personLabel.appendChild(document.createTextNode(roomText.person));
 roomTools.appendChild(roomSelect);roomTools.appendChild(personLabel);roomTools.title=roomText.note;
 var photoZoom=document.createElement('div');photoZoom.className='calculator-room-photo-zoom';photoZoom.setAttribute('role','group');photoZoom.setAttribute('aria-label',ui.photo_zoom);
 var zoomButtons=[];
 [-1,1].forEach(function(direction){var button=document.createElement('button');button.type='button';button.textContent=direction<0?'−':'+';button.setAttribute('aria-label',direction<0?ui.zoom_out:ui.zoom_in);button.title=button.getAttribute('aria-label');button.disabled=true;button.addEventListener('click',function(){finishCrop();var cell=editor.getCellByPoint({x:.5,y:.5});if(!cell||!cell.resource)return;cell.photo_zoom(cell.resource.zoom*(direction<0?1/1.2:1.2));editor.render();});photoZoom.appendChild(button);zoomButtons.push(button);});
 function updatePhotoZoomButtons(){if(!zoomButtons)return;var cell=editor.getCellByPoint({x:.5,y:.5}),resource=cell&&cell.resource;zoomButtons[0].disabled=!resource||resource.zoom<=1.000001;zoomButtons[1].disabled=!resource||resource.zoom>=9.999999;}
 roomTools.appendChild(photoZoom);
 var roomNote=document.createElement('small');roomNote.className='calculator-room-note';roomNote.textContent=roomText.note+(' '+ui.drag_note);roomTools.appendChild(roomNote);
 host.querySelector('.slider-tabs>.tabs').after(roomTools);
 roomSelect.addEventListener('change',function(){roomKind=roomSelect.value;if(scene)buildRoom();editor.render3D();});personToggle.addEventListener('change',function(){editor.render3D();});
 var frameBackgrounds;
 function ensureFrameBackgrounds(){if(frameBackgrounds)return;frameBackgrounds=document.createElement('div');frameBackgrounds.className='calculator-frame-backgrounds';frameBackgrounds.setAttribute('role','group');frameBackgrounds.setAttribute('aria-label',roomText.label);
 var framePanel=host.querySelector('.calculator-frame-inline');framePanel.querySelector('h2').after(frameBackgrounds);
 ['neutral','living','console','loft'].forEach(function(key){var button=document.createElement('button');button.type='button';button.className='calculator-frame-background';button.setAttribute('aria-pressed',String(key===roomKind));button.textContent=key==='neutral'?host.querySelector('.slider-tabs a[data-tabs="tabs-item1"]').textContent:roomText[key];if(key!=='neutral'){var image=document.createElement('img');image.src='/theme/viar/images/frame-room-'+key+'-v1.webp';image.alt='';button.prepend(image);}button.addEventListener('click',function(){finishCrop();host.dataset.previewMode=key==='neutral'?'frame':'room';if(key!=='neutral'){frameRoomKind=key;}frameBackgrounds.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b===button));});editor.render3D();});frameBackgrounds.appendChild(button);});
 }
 host.addEventListener('frame-view-open',function(){ensureFrameBackgrounds();frameBackgrounds.querySelectorAll('button').forEach(function(b,i){b.setAttribute('aria-pressed',String(i===['living','console','loft'].indexOf(frameRoomKind)+1));});editor.render3D();});
 var sizeLabel=document.createElement('span');sizeLabel.className='calculator-room-dimensions';viewport.parentElement.appendChild(sizeLabel);
 var observer=new ResizeObserver(function(){var p=viewport.parentElement,w=p.clientWidth,h=p.clientHeight;if(w>0&&h>0){editor.setSize(h,w);editor.fitPreview(w,h);editor.render3D();}});observer.observe(viewport.parentElement);
}};
root.dispatchEvent(new Event('canvas-room-ready'));
})(window);
