const {test}=require('node:test');
const assert=require('node:assert/strict');
const wrap=require('../../public/theme/viar/js/canvas-wrap.js');
test('photo matching the chosen ratio has no crop at default zoom',()=>{const c=wrap.crop(1200,1600,30,40,1,0);assert.equal(c.x,0);assert.equal(c.y,0);});
test('landscape photo crops horizontally in a portrait canvas and rotation restores fit',()=>{let c=wrap.crop(1600,1200,30,40,1,0);assert.ok(Math.abs(c.x-.4375)<1e-8);assert.equal(c.y,0);c=wrap.crop(1600,1200,30,40,1,Math.PI/2);assert.ok(c.x<1e-8&&c.y<1e-8);});
test('zoom keeps crop inside image bounds',()=>{const c=wrap.crop(1200,1600,30,40,2,0);assert.equal(c.x,.5);assert.equal(c.y,.5);assert.equal(wrap.crop(0,0,30,40,1,0),null);});
test('upload size matching never silently selects a physically smaller canvas',()=>{const current={w:40,h:50};const sizes=[{w:20,h:30},{w:40,h:60},{w:60,h:90}];assert.deepEqual(wrap.uploadSize(2/3,sizes,current),{w:40,h:60});});
test('folded edges remain inside the texture without distorting the front',()=>{const triangles=wrap.foldedUvs(.1,.1);assert.equal(triangles.length,10);for(const tri of triangles)for(const uv of tri)for(const n of uv)assert.ok(n>=0&&n<=1);assert.ok(triangles[0][0][0]>0&&triangles[0][0][0]<.5);});
