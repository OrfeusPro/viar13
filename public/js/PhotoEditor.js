var Events = function () {
	this.Events = {};
}

Events.prototype.addEventListener = function (type, callback,useCapture) {
	if (!this.Events[type]) {
		this.Events[type] = [];
	}

	if (useCapture)
	{
		this.Events[type].unshift(callback);
	}else
	{
		this.Events[type].push(callback);	
	}
    
}


Events.prototype.removeEventListener = function (type, callback) {
	if (this.Events[type]) {
		var list = this.Events[type];


		var counter = 0;

		while (counter < list.length) {


			if (list[counter] == callback) {
				list.splice(counter, 1);
				break;
			}

			counter++;
		}

		if (list.length == 0) {
			delete this.Events[type];
		}


	}


}

Events.prototype.dispatch = function (type, obj) {
	if (this.Events[type]) {
		for (var key in this.Events[type]) if(this.Events[type][key](obj))return true;
	}
}



var MouseEvents = function(dom,trigger)
{
	this.dom = dom;
	this.trigger = trigger;
	this.isDown = false;

	var mousedown = function(e)
	{
		this.isDown = true;	
		var data = this.getData(e);	
		this.trigger.dispatch("mousedown",data);		
	}.bind(this);
	
	
	
	var touchstart = function(e)
	{
		this.isDown = true;	
		var data = this.getDataTouch(e);	
		this.trigger.dispatch("mousedown",data);		
	}.bind(this);
	
	var touchmove = function(e)
	{
		var data = this.getDataTouch(e);
		if (data.is || this.isDown) this.trigger.dispatch("mousemove",data);
	}.bind(this);
	
	
	var touchend = function(e)
	{
		if (this.isDown)
		{
			var data = this.getDataTouch(e);
			this.isDown = false;		
			this.trigger.dispatch("mouseup",data);	
			
		}	
			
			
	}.bind(this);
	

	var mousemove = function(e)
	{
		var data = this.getData(e);
		if (data.is || this.isDown) this.trigger.dispatch("mousemove",data);
	}.bind(this);

	var mouseup = function(e)
	{
		if (this.isDown)
		{
			var data = this.getData(e);	
			this.trigger.dispatch("mouseup",data);	
			this.isDown = false;	
		}	
	}.bind(this);


	var wheel = function(e)
	{
		var data = this.getData(e);
		data.deltaY = e.deltaY || e.detail || e.wheelDelta;	
		if(this.trigger.dispatch("wheel",data))e.preventDefault();
		
	}.bind(this);
	
	
	
	




	this.on = function()
	{
		document.addEventListener('mousemove', mousemove);	
		document.addEventListener('mouseup', mouseup);	
		dom.addEventListener('mousedown', mousedown);
		dom.addEventListener("wheel", wheel);
		
		dom.addEventListener('touchstart',touchstart);
		dom.addEventListener('touchmove',touchmove);
		dom.addEventListener('touchend',touchend);
		
	}


	this.off = function()
	{
		document.removeEventListener('mousemove', mousemove);	
		document.removeEventListener('mouseup', mouseup);	
		dom.removeEventListener('mousedown', mousedown);
		dom.removeEventListener("wheel", wheel);
		dom.removeEventListener('touchstart',touchstart);	
		dom.removeEventListener('touchmove',touchmove);
		dom.removeEventListener('touchend',touchend);
	}
}


MouseEvents.prototype.getData = function(domEvent)
{
	var rect = this.dom.getBoundingClientRect();

	var data = {Pos:{},SinglePos:{}};

	data.Pos.x = domEvent.clientX - rect.left;
	data.Pos.y = domEvent.clientY - rect.top;


	data.SinglePos.x =(1/this.dom.clientWidth)*data.Pos.x;
	data.SinglePos.y =(1/this.dom.clientHeight)*data.Pos.y;


	data.is = (data.SinglePos.x >= 0 && data.SinglePos.y >= 0 && data.SinglePos.x <= 1 && data.SinglePos.y <= 1);
    

	return data;	
}


MouseEvents.prototype.getDataTouch = function(domEvent)
{
	var rect = this.dom.getBoundingClientRect();

	var data = {Pos:{},SinglePos:{}};

	data.Pos.x = domEvent.changedTouches[0].clientX - rect.left;
	data.Pos.y = domEvent.changedTouches[0].clientY - rect.top;


	data.SinglePos.x =(1/this.dom.clientWidth)*data.Pos.x;
	data.SinglePos.y =(1/this.dom.clientHeight)*data.Pos.y;


	data.is = (data.SinglePos.x >= 0 && data.SinglePos.y >= 0 && data.SinglePos.x <= 1 && data.SinglePos.y <= 1);
    

	return data;	
}








var m3 = {
	
	multiply: function(a, b) {
		var a00 = a[0 * 3 + 0];
		var a01 = a[0 * 3 + 1];
		var a02 = a[0 * 3 + 2];
		var a10 = a[1 * 3 + 0];
		var a11 = a[1 * 3 + 1];
		var a12 = a[1 * 3 + 2];
		var a20 = a[2 * 3 + 0];
		var a21 = a[2 * 3 + 1];
		var a22 = a[2 * 3 + 2];
		var b00 = b[0 * 3 + 0];
		var b01 = b[0 * 3 + 1];
		var b02 = b[0 * 3 + 2];
		var b10 = b[1 * 3 + 0];
		var b11 = b[1 * 3 + 1];
		var b12 = b[1 * 3 + 2];
		var b20 = b[2 * 3 + 0];
		var b21 = b[2 * 3 + 1];
		var b22 = b[2 * 3 + 2];
 
		return [
			b00 * a00 + b01 * a10 + b02 * a20,
			b00 * a01 + b01 * a11 + b02 * a21,
			b00 * a02 + b01 * a12 + b02 * a22,
			b10 * a00 + b11 * a10 + b12 * a20,
			b10 * a01 + b11 * a11 + b12 * a21,
			b10 * a02 + b11 * a12 + b12 * a22,
			b20 * a00 + b21 * a10 + b22 * a20,
			b20 * a01 + b21 * a11 + b22 * a21,
			b20 * a02 + b21 * a12 + b22 * a22,
		];
	},
  
	multiplyPoint: function(a, b) {
  	
		var a00 = a[0 * 3 + 0];
		var a01 = a[0 * 3 + 1];
		var a02 = a[0 * 3 + 2];
		var a10 = a[1 * 3 + 0];
		var a11 = a[1 * 3 + 1];
		var a12 = a[1 * 3 + 2];
		var a20 = a[2 * 3 + 0];
		var a21 = a[2 * 3 + 1];
		var a22 = a[2 * 3 + 2];
    
 
  	
  	
		var r = {};
		r.x = b.x * a00 + b.y * a10 + a20;
		r.y = b.x * a01 + b.y * a11 + a21;
  	  
  	  
		return r;
	},
	
	translation: function(tx, ty) {
		return [
			1, 0, 0,
			0, 1, 0,
			tx, ty, 1,
		];
	},
 
	rotation: function(angleInRadians) {
		var c = Math.cos(angleInRadians);
		var s = Math.sin(angleInRadians);
		return [
			c,-s, 0,
			s, c, 0,
			0, 0, 1,
		];
	},
 
	scaling: function(sx, sy) {
		return [
			sx, 0, 0,
			0, sy, 0,
			0, 0, 1,
		];
	},
};


var Cell = function(editor)
{
	this.editor = editor;
	
	//p1+++++++++++++++
	//+++++++++++++++++
	//+++++++++++++++++
	//+++++++++++++++++
	//+++++++++++++++++
	//+++++++++++++++++
	//+++++++++++++++p2
	
	this.p1 = {x:0,y:0};
	this.p2 = {x:1,y:1};
	
	this.anim_begintime = new Date().getTime() - Cell.anim_length;
}


Cell.min_size = 0.05;

Cell.anim_length = 300;


Cell.prototype.h = function() //высота
{
	return this.p2.y - this.p1.y;	
}

Cell.prototype.w = function()//ширина
{
	return this.p2.x - this.p1.x;		
}

Cell.prototype.anim = function()//координаты при использовани анимации
{
	if (this.anim_old)
	{
		var single = Math.min(1, (1 / Cell.anim_length) * (new Date().getTime() - this.anim_begintime));

		var obj = {p1:{},p2:{}};
		
		obj.p1.x = this.anim_old.p1.x + (this.p1.x - this.anim_old.p1.x)*single;
		obj.p1.y = this.anim_old.p1.y + (this.p1.y - this.anim_old.p1.y)*single;
		obj.p2.x = this.anim_old.p2.x + (this.p2.x - this.anim_old.p2.x)*single;
		obj.p2.y = this.anim_old.p2.y + (this.p2.y - this.anim_old.p2.y)*single;

		return obj;		
	
	}else
	{	
		return {p1:Object.assign({},this.p1),p2:Object.assign({},this.p2)};	
	}	
	
}



Cell.prototype.BeginAnimation = function()//переместить анимацию в начало
{
	this.anim_old = this.anim();	
	this.anim_begintime = new Date().getTime();	
}



Cell.prototype.cells_top = function()
{
	
	var list = [];	
	
	var mx = 0;	
	
	for (var key in this.editor.grid)	
	{      
		var cell2 = this.editor.grid[key];		
	
		if(this.p1.y >= cell2.p2.y && ((this.p1.x > cell2.p1.x && this.p1.x < cell2.p2.x) || (this.p2.x > cell2.p1.x && this.p2.x < cell2.p2.x)))
		{
			mx = Math.max(mx,cell2.p2.y);	
		}
	}	


	for (var key in this.editor.grid)	
	{      
		var cell2 = this.editor.grid[key];	

		if (this.p1.x <= cell2.p1.x && this.p2.x >= cell2.p2.x  && this.p1.y >= cell2.p2.y && mx <= cell2.p1.y)
		{
			list.push(cell2);	
		}
	}

	return list;	
}


Cell.prototype.cells_bottom = function()
{
	
	
	
	var list = [];	
	
	var mn = 1;	
	
	for (var key in this.editor.grid)	
	{      
		var cell2 = this.editor.grid[key];		
	
		if(this.p2.y <= cell2.p1.y && ((this.p1.x > cell2.p1.x && this.p1.x < cell2.p2.x) || (this.p2.x > cell2.p1.x && this.p2.x < cell2.p2.x))) 
		{
			mn = Math.min(mn,cell2.p1.y);
		}
	}	
	


	for (var key in this.editor.grid)	
	{      
		var cell2 = this.editor.grid[key];	

		if (this.p1.x <= cell2.p1.x && this.p2.x >= cell2.p2.x  && this.p2.y <= cell2.p1.y && mn >= cell2.p2.y)
		{
			list.push(cell2);	
		}
	}

	return list;	
}



Cell.prototype.cells_left = function()
{
	
	var list = [];	
	
	var mx = 0;	
	
	for (var key in this.editor.grid)	
	{      
		var cell2 = this.editor.grid[key];		
	
		if(this.p1.x >= cell2.p2.x && ((this.p1.y > cell2.p1.y && this.p1.y < cell2.p2.y) || (this.p2.y > cell2.p1.y && this.p2.y < cell2.p2.y))) 
		{
			mx = Math.max(mx,cell2.p2.x);	
		}
	}	


	for (var key in this.editor.grid)	
	{      
		var cell2 = this.editor.grid[key];	

		if (this.p1.y <= cell2.p1.y && this.p2.y >= cell2.p2.y  && this.p1.x >= cell2.p2.x && mx <= cell2.p1.x)
		{
			list.push(cell2);	
		}
	}

	return list;		
	
	
	
}



Cell.prototype.cells_right = function()
{
	
	var list = [];	
	
	var mn = 1;	
	
	for (var key in this.editor.grid)	
	{      
		var cell2 = this.editor.grid[key];		
	
		if(this.p2.x <= cell2.p1.x && ((this.p1.y > cell2.p1.y && this.p1.y < cell2.p2.y) || (this.p2.y > cell2.p1.y && this.p2.y < cell2.p2.y))) 
		{
			mn = Math.min(mn,cell2.p1.x);
	
		}


	}	


	for (var key in this.editor.grid)	
	{      
		var cell2 = this.editor.grid[key];	

		if (this.p1.y <= cell2.p1.y && this.p2.y >= cell2.p2.y  && this.p2.x <= cell2.p1.x && mn >= cell2.p2.x)
		{
			list.push(cell2);	
		}
	}

	return list;	
}






Cell.prototype.add_vertical = function(top,is_animation)
{
	

	
	var list1 = this.cells_top();	
	var list2 = this.cells_bottom();
	
	var magnet = this.editor.Magnet();
	

	var h = this.h();
	
	var n = new Cell(this.editor);
	n.id = this.editor.counter++;
	
	if (top)
	{
		n.p1.x = this.p1.x;//начало для анимации
		n.p1.y = this.p1.y;	
		n.p2.x = this.p2.x;
		n.p2.y = this.p1.y;		
	}else
	{
		n.p1.x = this.p1.x;
		n.p1.y = this.p2.y;	
		n.p2.x = this.p2.x;
		n.p2.y = this.p2.y;
	}
	
	 
	var list3 = list1.concat(list2).concat([n]).concat([this]);
	
	if (is_animation)list3.forEach(function(cell){cell.BeginAnimation();});
	
	
	list2.forEach(function(cell){
			cell.p1.y += h;
			cell.p2.y += h;		
		});
	
	
	if (top)
	{
		this.p1.y += h;
		this.p2.y += h;		
	}
	
	
   

	if (top)
	{
		n.p1.x = this.p1.x;
		n.p1.y = this.p1.y-h;	
		n.p2.x = this.p2.x;
		n.p2.y = this.p1.y;		
	}else
	{
		n.p1.x = this.p1.x;
		n.p1.y = this.p2.y;	
		n.p2.x = this.p2.x;
		n.p2.y = this.p2.y+h;
	}

	this.editor.grid.push(n);
	
	
	


	var mn = 1;
	var mx = 0;
	
	
	
	list3.forEach(function(cell){
			mn = Math.min(mn,Math.min(cell.p1.y,cell.p2.y));
			mx = Math.max(mx,Math.max(cell.p1.y,cell.p2.y));
		});

	var s = mx -  mn; 

	var  m =  m3.multiply(m3.translation(0,mn),
		m3.multiply(m3.scaling(1,(s - h)/s),
			m3.translation(0,-mn)));
          
    
	list3.forEach(function(cell){
			cell.p1 = m3.multiplyPoint(m,cell.p1);
			cell.p2 = m3.multiplyPoint(m,cell.p2);
			
			
			cell.p1.y = magnet.y(cell.p1.y,0.01);
			cell.p2.y = magnet.y(cell.p2.y,0.01); 
				
		});
		
	this.editor.DoChange("cell");	
	return n;
	
}



Cell.prototype.add_horizontal = function(left,is_animation)
{
	
	
	var list1 = this.cells_left();	
	var list2 = this.cells_right();
	
	var magnet = this.editor.Magnet();
	

	var w = this.w();
	
	var n = new Cell(this.editor);
	n.id = this.editor.counter++;
	    
	if (left)
	{
		n.p1.y = this.p1.y;//начало для анимации 
		n.p1.x = this.p1.x;	
		n.p2.y = this.p2.y;
		n.p2.x = this.p1.x;		
	}else
	{
		n.p1.y = this.p1.y;
		n.p1.x = this.p2.x;	
		n.p2.y = this.p2.y;
		n.p2.x = this.p2.x;
	}
	    
	    
	    
	var list3 = list1.concat(list2).concat([n]).concat([this]);
	    
	if (is_animation)list3.forEach(function(cell){cell.BeginAnimation();});
	    
	
	
	list2.forEach(function(cell){
			cell.p1.x += w;
			cell.p2.x += w;		
		});
	
	
	if (left)
	{
		this.p1.x += w;
		this.p2.x += w;		
	}
	
	
	
   
	if (left)
	{
		n.p1.y = this.p1.y;
		n.p1.x = this.p1.x-w;	
		n.p2.y = this.p2.y;
		n.p2.x = this.p1.x;		
	}else
	{
		n.p1.y = this.p1.y;
		n.p1.x = this.p2.x;	
		n.p2.y = this.p2.y;
		n.p2.x = this.p2.x+w;
	}

	this.editor.grid.push(n);
	
	
	


	var mn = 1;
	var mx = 0;
	
	
	list3.forEach(function(cell){
			mn = Math.min(mn,Math.min(cell.p1.x,cell.p2.x));
			mx = Math.max(mx,Math.max(cell.p1.x,cell.p2.x));
		});



	var s = mx -  mn; 

	var  m =  m3.multiply(m3.translation(mn,0),
		m3.multiply(m3.scaling((s - w)/s,1),
			m3.translation(-mn,0)));
          
    
	list3.forEach(function(cell){
		
		
			cell.p1 = m3.multiplyPoint(m,cell.p1);
			cell.p2 = m3.multiplyPoint(m,cell.p2);	
			
			cell.p1.x = magnet.x(cell.p1.x,0.01);
			cell.p2.x = magnet.x(cell.p2.x,0.01);
			
			
		});
		
	this.editor.DoChange("cell");	
	return n;	
	
}

Cell.prototype.delete = function(is_animation)
{
	
	
	var list_left = this.cells_left();	
	var list_right = this.cells_right();
	
	var magnet = this.editor.Magnet();


	if (list_left.length !== 0 || list_right.length !== 0)
	{
	
		var w = this.w();
		
		
		
		var list3 = list_left.concat(list_right);
		if(is_animation)list3.forEach(function(cell){cell.BeginAnimation();});
		

		for (var key in list_right)
		{
			var cell = list_right[key];
			cell.p1.x -= w;
			cell.p2.x -= w;	
		}	
	
	
		var mn = 1;
		var mx = 0;


		


		list3.forEach(function(cell){
				mn = Math.min(mn,Math.min(cell.p1.x,cell.p2.x));
				mx = Math.max(mx,Math.max(cell.p1.x,cell.p2.x));
			});	
		
		
		var s = mx -  mn; 

		var  m =  m3.multiply(m3.translation(mn,0),
			m3.multiply(m3.scaling((s + w)/s,1),
				m3.translation(-mn,0)));
          
    
		list3.forEach(function(cell){
				cell.p1 = m3.multiplyPoint(m,cell.p1);
				cell.p2 = m3.multiplyPoint(m,cell.p2);		
				
				cell.p1.x = magnet.x(cell.p1.x,0.01);
				cell.p2.x = magnet.x(cell.p2.x,0.01);
			});		
	
		
	
		this.editor.grid.splice(this.editor.grid.indexOf(this), 1);	
	
	}else
	{
		var list_top = this.cells_top();	
		var list_bottom = this.cells_bottom();	


		if (list_top.length !== 0 || list_bottom.length !== 0)
		{
	
			var h = this.h();
			
			var list3 = list_top.concat(list_bottom);
			if(is_animation)list3.forEach(function(cell){cell.BeginAnimation();});
			

			for (var key in list_bottom)
			{
				var cell = list_bottom[key];
				cell.p1.y -= h;
				cell.p2.y -= h;	
			}	
	
	
			var mn = 1;
			var mx = 0;


			

			list3.forEach(function(cell){
					mn = Math.min(mn,Math.min(cell.p1.y,cell.p2.y));
					mx = Math.max(mx,Math.max(cell.p1.y,cell.p2.y));
				});	
		
		
			var s = mx -  mn; 

			var  m =  m3.multiply(m3.translation(0,mn),
				m3.multiply(m3.scaling(1,(s + h)/s),
					m3.translation(0,-mn)));
          
    
			list3.forEach(function(cell){
					cell.p1 = m3.multiplyPoint(m,cell.p1);
					cell.p2 = m3.multiplyPoint(m,cell.p2);
					
					
					cell.p1.y = magnet.y(cell.p1.y,0.01);
					cell.p2.y = magnet.y(cell.p2.y,0.01);
			        
			         	
				});		
	
		
			this.editor.grid.splice(this.editor.grid.indexOf(this), 1);	
	
		}
	
	
	
	}
	this.editor.DoChange("cell");
}

Cell.prototype.RectOut = function()//область вывода
{
	var r = {};	
	var anim = this.anim();
	var mn = Math.min(this.editor.canvas.width,this.editor.canvas.height); 
	r.x = anim.p1.x * this.editor.canvas.width +  (this.editor.between * mn);
	r.y = anim.p1.y * this.editor.canvas.height +  (this.editor.between * mn);
	r.w = Cell.prototype.w.call(anim)*this.editor.canvas.width - (this.editor.between*2*mn);
	r.h = Cell.prototype.h.call(anim)*this.editor.canvas.height - (this.editor.between*2*mn);   	
	return r;	
}

Cell.prototype.deleteImg = function()
{
	this.resource = undefined;	
	this.editor.DoChange("cell");	
}
 


Cell.prototype.setImg = function(img)
{
	if (!this.resource || this.resource.img !== img)
	{
		this.resource = {};
		this.resource.img = img;
		this.resource.zoom = 1;
		this.resource.offset_x = 0;
		this.resource.offset_y = 0;
		this.resource.rotate = 0.0;
		this.resource.invert_x = false;
		this.resource.invert_y = false;
		this.editor.DoChange("cell");
	}
}


Cell.prototype.photo_zoom = function(value)
{
	if (this.resource)
	{
		value = Math.max(1,value);
		value = Math.min(10,value);
		
		var rect = this.RectOut();
		var s = (1  / this.resource.zoom)*value;
		this.resource.offset_x =((0.5 + this.resource.offset_x) * s)-0.5;
		this.resource.offset_y =((0.5 + this.resource.offset_y) * s)-0.5;		
		this.resource.zoom = value;	
		this.editor.DoChange("cell");
		
		return true;	
	}else
	return false;
}



Cell.prototype.photo_rotate = function(value)
{
	if (this.resource)
	{
		this.resource.rotate = value;
		this.editor.DoChange("cell");
		return true;	
	}else
	return false;	
}	


Cell.prototype.photo_invert_x = function(value)
{
	if (this.resource)
	{
		this.resource.invert_x = value;
		this.editor.DoChange("cell");
		return true;	
	}else
	return false;	
}

Cell.prototype.photo_invert_y = function(value)
{
	if (this.resource)
	{
		this.resource.invert_y = value;
		this.editor.DoChange("cell");
		return true;	
	}else
	return false;	
}






var Layer = function(editor){this.editor = editor;}

Layer.prototype.c_x = function()
{
	return this.x + (this.w/2);
}

Layer.prototype.c_y = function()
{
	return this.y + (this.h/2);
}

Layer.prototype.rect = function()
{
	var points = [];

	var x = this.x * this.editor.canvas.width;
	var y = this.y * this.editor.canvas.height;
	var w = this.w * this.editor.canvas.width;
	var h = this.h * this.editor.canvas.height;

	points.push({x:x,y:y}); 
	points.push({x:x + w,y: y});    
	points.push({x:x + w,y: y + h}); 
	points.push({x:x,y: y + h}); 


	var m = m3.translation(-x - (w / 2),-y - (h / 2)); 
	m = m3.multiply(m3.rotation(-this.rotate),m);
	m = m3.multiply(m3.translation(x + (w / 2),y + (h / 2)),m); 
 
 
	for (var i = 0; i < points.length; i++)
	points[i] = 	m3.multiplyPoint(m,points[i]);

	var mn_x = Math.min(...points.map((p)=>p.x)) / this.editor.canvas.width;
	var mn_y = Math.min(...points.map((p)=>p.y)) / this.editor.canvas.height
	var mx_x = Math.max(...points.map((p)=>p.x)) / this.editor.canvas.width;
	var mx_y = Math.max(...points.map((p)=>p.y)) / this.editor.canvas.height;

	return {x:mn_x,y:mn_y,w:mx_x - mn_x,h:mx_y - mn_y};
}






	


var DragAndDrop = function(type,img)
{
	DragAndDrop.type = type;	
	DragAndDrop.img = img;
	
	var n;

	function mousemove(e)
	{
		if (!n)
		{
			n = document.createElement("img");
			n.src = img.icon.src;
			n.className = "DragAndDrop";
			document.body.appendChild(n);
		}	
		
		n.style.top = e.clientY+"px";
		n.style.left = e.clientX+"px";
		if (DragAndDrop.onmousemove)DragAndDrop.onmousemove(e);
		e.stopImmediatePropagation();
	}   
	
	function touchmove(e)
	{
		if (!n)
		{
			n = document.createElement("img");
			n.src = img.icon.src;
			n.className = "DragAndDrop";
			document.body.appendChild(n);
		}	
		
		n.style.top = e.changedTouches[0].clientY+"px";
		n.style.left = e.changedTouches[0].clientX+"px";
		if (DragAndDrop.ontouchmove)DragAndDrop.ontouchmove(e);
		e.stopImmediatePropagation();
	}
	
	
	
	function touchend(e)
	{
		document.body.removeChild(n);
		document.removeEventListener("mousemove", mousemove,true);	
		document.removeEventListener("mouseup", mouseup,true);
		document.removeEventListener("touchmove", touchmove,true);	
		document.removeEventListener("touchend", touchend,true);
		if (DragAndDrop.ontouchend)DragAndDrop.ontouchend(e);
		e.stopImmediatePropagation();	
	}
	
	
	function mouseup(e)
	{
		document.body.removeChild(n);
		document.removeEventListener("mousemove", mousemove,true);	
		document.removeEventListener("mouseup", mouseup,true);
		document.removeEventListener("touchmove", touchmove,true);	
		document.removeEventListener("touchend", touchend,true);
		if (DragAndDrop.onmouseup)DragAndDrop.onmouseup(e);
		e.stopImmediatePropagation();
	}
 
 
	document.addEventListener("mousemove", mousemove,true);
	document.addEventListener("mouseup", mouseup,true);	
	document.addEventListener("touchmove", touchmove,true);	
	document.addEventListener("touchend", touchend,true);
 
}


function DragAndDrop_img(obj,type)
{
	obj.element.ontouchstart  = function()
	{	
		if (obj.isLoad)	
		{
			function touchmove(e)
			{
				var rect = obj.element.getBoundingClientRect();
			
				var single_y = (1 / obj.element.clientHeight) * (e.changedTouches[0].clientY - rect.top);
				var single_x = (1 / obj.element.clientWidth) * (e.changedTouches[0].clientX - rect.left);	

				if (single_y < 0 || single_y > 1 || single_x < 0 || single_x > 1)
				{
					remove();
					new DragAndDrop(type,obj.img);
				}	
			}

			function remove()
			{
				document.removeEventListener("touchmove", touchmove);
				document.removeEventListener("touchend", remove);	
			}

			document.addEventListener("touchmove", touchmove);
			document.addEventListener("touchend", remove);
				
		}
	}
	
	obj.element.onmousedown = function()
	{	
		if (obj.isLoad)	
		{
			function mousemove(e)
			{
				var rect = obj.element.getBoundingClientRect();
			
				var single_y = (1 / obj.element.clientHeight) * (e.clientY - rect.top);
				var single_x = (1 / obj.element.clientWidth) * (e.clientX - rect.left);	

				if (single_y < 0 || single_y > 1 || single_x < 0 || single_x > 1)
				{
					remove();
					new DragAndDrop(type,obj.img);
				}	
			}
			
			
			function touchmove(e)
			{
				var rect = obj.element.getBoundingClientRect();
			
				var single_y = (1 / obj.element.clientHeight) * (e.changedTouches[0].clientY - rect.top);
				var single_x = (1 / obj.element.clientWidth) * (e.changedTouches[0].clientX - rect.left);	

				if (single_y < 0 || single_y > 1 || single_x < 0 || single_x > 1)
				{
					remove();
					new DragAndDrop(type,obj.img);
				}	
	        	
	        	
			}

			function remove()
			{
				document.removeEventListener("mousemove", mousemove);	
				document.removeEventListener("touchmove", touchmove);
				document.removeEventListener("mouseup", remove);	
				document.removeEventListener("touchend", remove);	
			}

		
			document.addEventListener("mousemove", mousemove);
			document.addEventListener("touchmove", touchmove);
			document.addEventListener("mouseup", remove);
			document.addEventListener("touchend", remove);
				
		}
		
		return false;
	}
}


var DragAndDropEvents = function(dom,trigger)
{
	
	var getDataTouch = function(domEvent)
	{
		var rect = dom.getBoundingClientRect();

		var data = {Pos:{},SinglePos:{}};

		data.Pos.x = domEvent.changedTouches[0].clientX - rect.left;
		data.Pos.y = domEvent.changedTouches[0].clientY - rect.top;


		data.SinglePos.x =(1/dom.clientWidth)*data.Pos.x;
		data.SinglePos.y =(1/dom.clientHeight)*data.Pos.y;
		data.DragAndDrop = DragAndDrop;

		data.is = (data.SinglePos.x >= 0 && data.SinglePos.y >= 0 && data.SinglePos.x <= 1 && data.SinglePos.y <= 1);
    

		return data;	
	}
	
	
	var getData = function(domEvent)
	{
		var rect = dom.getBoundingClientRect();

		var data = {Pos:{},SinglePos:{}};

		data.Pos.x = domEvent.clientX - rect.left;
		data.Pos.y = domEvent.clientY - rect.top;


		data.SinglePos.x =(1/dom.clientWidth)*data.Pos.x;
		data.SinglePos.y =(1/dom.clientHeight)*data.Pos.y;
		data.DragAndDrop = DragAndDrop;

		data.is = (data.SinglePos.x >= 0 && data.SinglePos.y >= 0 && data.SinglePos.x <= 1 && data.SinglePos.y <= 1);
    

		return data;	
	}
	
	
	var old_onmousemove = DragAndDrop.onmousemove;
	DragAndDrop.onmousemove = function(e)
	{
		var data = getData(e);
		trigger.dispatch("DragAndDrop_mousemove",data);
		if (old_onmousemove)old_onmousemove(e);	
	}	
	
	
	var old_ontouchmove = DragAndDrop.ontouchmove;
	DragAndDrop.ontouchmove = function(e)
	{
		var data = getDataTouch(e);
		trigger.dispatch("DragAndDrop_mousemove",data);
		if (old_ontouchmove)old_ontouchmove(e);	
	}	
	
	
	
	
	
	var old_onmouseup = DragAndDrop.onmouseup; 
	DragAndDrop.onmouseup = function(e)	 
	{
		var data = getData(e);
		trigger.dispatch("DragAndDrop_mouseup",data);	
		if (old_onmouseup)old_onmouseup(e);		
	}
	
	
	
	var old_ontouchend = DragAndDrop.ontouchend; 
	DragAndDrop.ontouchend = function(e)	 
	{
		var data = getDataTouch(e);
		trigger.dispatch("DragAndDrop_mouseup",data);	
		if (old_ontouchend)old_ontouchend(e);		
	}


	
}





var Selection = function(editor,element)
{
	this.editor = editor;
	
	if (element instanceof Cell)
	{
		this.cell = element;
	}else
	if (element instanceof Layer)
	{
		this.layer = element;
	}
	
	this.element = element;	
}




Selection.prototype.delete = function()
{
	this.editor.selection = undefined;	
	
	if (this.element instanceof Cell)
	{
		
		this.element.delete(true);	
		this.editor.DoChange('cell');
	}else
	if (this.element instanceof Layer)
	{   
		this.editor.layers.splice(this.editor.layers.indexOf(this.element), 1);	
		this.editor.DoChange('layer');	
	}
}


Selection.prototype.rect = function()
{
	
	if (this.element instanceof Cell)
	{	
		var anim = this.element.anim();
		return {x:anim.p1.x,y:anim.p1.y,w:Cell.prototype.w.call(anim),h:Cell.prototype.h.call(anim)};
	}else
	if (this.element instanceof Layer)
	{
		return this.element.rect();	
	}	
}


function Animation_smilemoved(src,start,end,time)
{
var el = document.createElement("div");
    el.className = "Animation_smilemoved";
    el.style.setProperty("background-image","url('"+src+"')");
    el.style.setProperty("--start_x",start.x+'px');
    el.style.setProperty("--start_y",start.y+'px');
    el.style.setProperty("--end_x",end.x+'px');
    el.style.setProperty("--end_y",end.y+'px');
    el.style.setProperty("--time",(time/1000)+'s');
    
document.body.appendChild(el);	
setTimeout(function(){document.body.removeChild(el)},time);
}


var PhotoEditor = function(options)
{
	
	this.optionsDefault = {
		isMouseEvents:true,
		isDragAndDropEvents:true,
		isAct_selection:true,
		isAct_LayerOperations:true,
		isAct_cellresize:true,
		isAct_cellAdd:true,
		isAct_PhotoOperations:true,
		isAct_OpenBackground:true,
		isAct_smileAdd:true,
		isCellDiv : true,
		ColorCell : '#6a9586',
		isOutPhoto : true
	};
	
	this.options = Object.assign(this.optionsDefault,options);
	
	Events.call(this);
	
	
	var self = this;	
	this.between = 0.01;
	this.radius = 5;
	this.counter = 0;
	this.grid = [];	
	this.is_drawing = false;
	this.canvas = this.options.canvas;
	this.ctx = this.canvas.getContext('2d');	
	this.history = [];
	this.history_pos = 0;
	this.layers = [];
	
	
	this.offset_cell_x = 0.0;
	this.offset_cell_y = 0.0;
	
	this.Background_color = "#ffffff";	
	
	this.ColorCell = this.options.ColorCell; 
	
	this.isCellDiv = this.options.isCellDiv; 
	
	this.isOutPhoto = this.options.isOutPhoto;
	
	this.addEventListener("mousemove", function(){self.setCursor("default");});

	if (this.options.isAct_LayerOperations) this.initAction_LayerOperations();
	if (this.options.isAct_selection) this.initAction_selection();//инициализируем выделения
	if (this.options.isAct_cellresize) this.initAction_cellresize();//инициализируем растягивания
	if (this.options.isAct_cellAdd) this.initAction_cellAdd();//инициализируем добавления
	if (this.options.isAct_PhotoOperations) this.initAction_PhotoOperations();//инициализируем,операции с фото, вытащить фото с ячейки,двигать внутри
	if (this.options.isAct_OpenBackground) this.initAction_OpenBackground();
	if (this.options.isAct_smileAdd) this.initAction_smileAdd();
	
	
	
	
	
	
	this.new();
	this.add_history();
	
	if (this.options.isMouseEvents)
	new MouseEvents(this.canvas,this).on();
	if (this.options.isDragAndDropEvents)	 
	DragAndDropEvents(this.canvas,this);
		 
}


PhotoEditor.prototype = Object.assign({},Events.prototype);

PhotoEditor.prototype.setCursor = function(cursor)
{
	this.canvas.style.cursor = cursor;	
}

PhotoEditor.prototype.is_Undo = function()
{
	return (this.history_pos > 2);	
}

PhotoEditor.prototype.is_Redo = function()
{
	return (this.history_pos < this.history.length);	
}


PhotoEditor.prototype.Undo = function()
{
	if (this.is_Undo())
	{
		this.history_pos--;	
		this.load_history();	
	}
}

PhotoEditor.prototype.Redo = function()
{
	if (this.is_Redo())
	{
		this.history_pos++;	
		this.load_history();	
	}	
}



PhotoEditor.prototype.load_history = function()
{
	var state = this.history[this.history_pos-1];	
	
	this.grid = [];
	
	for (var key in state.grid)
	{
		var item = state.grid[key];	
		var n = new Cell(this);
		n.p1 = Object.assign({},item.p1);
		n.p2 = Object.assign({},item.p2);
		n.id = item.id;
		
		n.resource = item.resource;
		
		this.grid.push(n);
	}
	
	this.layers = []; 
	
	for (var key in state.layers)
	{
		var layer = state.layers[key];
	 	
		this.layers.push(Object.assign(new Layer(this),layer));
	}
	
	this.selection	= undefined;
  
	this.DoChange('load_history');
}



PhotoEditor.prototype.add_history = function()
{
	this.history_pos ++;
	var state = {};
	state.grid = [];
	
	for (var key in this.grid)
	{
		var cell = this.grid[key];
		var n = {};	
		n.p1 = Object.assign({},cell.p1);
		n.p2 = Object.assign({},cell.p2);
		n.id = cell.id;
		if (cell.resource)n.resource = Object.assign({},cell.resource);
		state.grid.push(n);	
	}
	 
	state.layers = [];
	 
	for (var key in this.layers)
	{
		var layer = this.layers[key];
		state.layers.push(Object.assign({},layer));
	}
	
	


	this.history = this.history.slice(0,this.history_pos-1);
	this.history.push(state);
}


PhotoEditor.prototype.DoChange2 = function(types)
{
	
	if (types["cell"] || types["layer"])	
	{
		this.add_history();	
	}	
	

	if (this.onchange)this.onchange(types);	
}


PhotoEditor.prototype.DoChange = function(type)
{
	var self = this;
	if (!this.change_stack)
	{
		this.change_stack = {};	
		this.change_stack.types = {};
		setTimeout(function(){
				self.DoChange2(self.change_stack.types);
				self.change_stack = undefined;	
			},100);
	}

	this.change_stack.types[type] = true;	
}

PhotoEditor.prototype.setBackground = function(img)
{
	this.Background = img;
	this.DoChange('editor');	
}






PhotoEditor.prototype.new = function()
{
	this.grid = [];	
	this.counter = 0;	
	var cell = new Cell(this);
	cell.id = this.counter++;
	cell.p1.x = 0;
	cell.p1.y = 0;
	cell.p2.x = 1;
	cell.p2.y = 1;
	this.grid.push(cell);	
	this.parent = cell;
	this.DoChange('cell');
}




PhotoEditor.prototype.clear = function()
{
	this.new();	
	this.layers = [];
	this.selection = undefined;
	this.DoChange('selection');
	this.DoChange('layer');
}

PhotoEditor.prototype.clearPhotos = function()
{
	this.grid.forEach(function(cell){cell.resource = undefined;});	
	this.DoChange('cell');
}

PhotoEditor.prototype.Magnet = function()
{
	var obj = {
		poss:[],
		x:function(inputX,mn)	
		{
			var r = inputX;	
	
			for (var key in this.poss)	
			{      
				var p = this.poss[key];	
				var d = Math.abs(p.x - inputX);

				if (d < mn)
				{
					r = p.x;	
					mn = d;	
				} 
	
	
			}

			return r;	
		},
		y:function(inputY,mn)
		{
			var r = inputY;	
	
			for (var key in this.poss)	
			{      
				var p = this.poss[key];	
				var d = Math.abs(p.y - inputY);

				if (d < mn)
				{
					r = p.y;	
					mn = d;	
				} 
	
	
			}
			
			
			return r;	
		}	
	};

	for (var key in this.grid)	
	{      
		var cell = this.grid[key];	
		obj.poss.push(Object.assign({},cell.p1));
		obj.poss.push(Object.assign({},cell.p2));
	}
	
	
	
	
	return obj;	
	
}


PhotoEditor.prototype.ClientPosToSinglePos = function(inputPos)
{
	var rect = this.canvas.getBoundingClientRect();
		
	var x = inputPos.x - rect.left;
	var y = inputPos.y - rect.top;
		
	var r = {};
	r.y = (1 / this.canvas.clientHeight) * y;
	r.x = (1 / this.canvas.clientWidth) * x;
		
	return r;		
	
} 

PhotoEditor.prototype.getCellByPoint = function(point)
{
	     
	return this.grid.find(function(cell){
			if (cell.p1.x <= point.x && cell.p2.x >= point.x &&
				cell.p1.y <= point.y && cell.p2.y >= point.y)return true;});			     


	
}



PhotoEditor.prototype.getLineByPoint = function(point)
{
	
	var mn = (1 / Math.max(this.canvas.width,this.canvas.height)) * 10;
	
	
	
	if (point.x > mn && point.y > mn && point.x < 1-mn && point.y < 1-mn)//не разрешается брать родителя с краю 
	{
		
		var line,cell;
		
		this.grid.forEach(function(cell2){ //ищим линию у самой длиной ячейки 
			
				if (cell2.p1.x <= point.x && cell2.p2.x >= point.x)//находится ли точка внутри по x
				{
				
				
					if (!line || ((cell2.p2.x - cell2.p1.x) > (line.p2.x - line.p1.x)))//если линии не существует или новая длиннее старой
					{
						if (Math.abs(cell2.p1.y - point.y) < mn)//проверить достаточно ли близко точка к линии 
						{   
							line = {};
							line.p1 = {x:cell2.p1.x,y:cell2.p1.y};	
							line.p2 = {x:cell2.p2.x,y:cell2.p1.y};	
							cell = cell2;
						}else
						if (Math.abs(cell2.p2.y - point.y) < mn)
						{	line = {};
							line.p1 = {x:cell2.p1.x,y:cell2.p2.y};	
							line.p2 = {x:cell2.p2.x,y:cell2.p2.y};	
							cell = cell2;
						}  
					}
				}else
				if (cell2.p1.y <= point.y && cell2.p2.y >= point.y)
				{
					if (!line || ((cell2.p2.y - cell2.p1.y) > (line.p2.y - line.p1.y)))	
					{
						if (Math.abs(cell2.p1.x - point.x) < mn)
						{
							line = {};
							line.p1 = {x:cell2.p1.x,y:cell2.p1.y};	
							line.p2 = {x:cell2.p1.x,y:cell2.p2.y};
							cell = cell2;	
						}else	
						if (Math.abs(cell2.p2.x - point.x) < mn)
						{   
							line = {};
							line.p1 = {x:cell2.p2.x,y:cell2.p1.y};	
							line.p2 = {x:cell2.p2.x,y:cell2.p2.y};
							cell = cell2;	
						}	
					}	
				
				}
			});
		
		
		if (line)
		{
		
		
		
			if (line.p1.y == line.p2.y)
			{
				
				if (this.grid.find(function(cell2){ 
							if (cell !== cell2 && 
								line.p1.x == cell2.p1.x && 
								((line.p1.y == cell2.p1.y) || 
									(line.p1.y == cell2.p2.y)))return true;
						})&&
					this.grid.find(function(cell2){ 
							if (cell !== cell2 && 
								line.p2.x == cell2.p2.x && 
								((line.p1.y == cell2.p1.y) || 
									(line.p1.y == cell2.p2.y)))return true;
						}))return line;
					
				var mx = 0;
				var mn = 1;
			
				for (var key in this.grid)	
				{      
					var cell2 = this.grid[key];	//получаем всю длину линии
					
					
					if (line.p1.y > cell2.p1.y && line.p1.y < cell2.p2.y)
					
					{
						if (line.p2.x <= cell2.p1.x)
						{
							mn = Math.min(mn,cell2.p1.x);
						}	
						
						
						if (line.p1.x >= cell2.p2.x)
						{
							mx = Math.max(mx,cell2.p2.x);	
						}	
					}
				}	
              	
				line.p1.x = mx;
				line.p2.x = mn;
			}else
			if (line.p1.x == line.p2.x)
			{
				if (this.grid.find(function(cell2){ 
							if (cell !== cell2 && 
								line.p1.y == cell2.p1.y && 
								((line.p1.x == cell2.p1.x) || 
									(line.p1.x == cell2.p2.x)))return true;
						})&&
					this.grid.find(function(cell2){ 
							if (cell !== cell2 && 
								line.p2.y == cell2.p2.y && 
								((line.p1.x == cell2.p1.x) || 
									(line.p1.x == cell2.p2.x)))return true;
						}))return line;
					
				
				var mx = 0;
				var mn = 1;
			
				for (var key in this.grid)	
				{      
					var cell2 = this.grid[key];	
					
					
					if (line.p1.x > cell2.p1.x && line.p1.x < cell2.p2.x)
					
					{
						if (line.p2.y <= cell2.p1.y)
						{
							mn = Math.min(mn,cell2.p1.y);
						}	
						
						
						if (line.p1.y >= cell2.p2.y)
						{
							mx = Math.max(mx,cell2.p2.y);	
						}	
					}
				}	
              	
				line.p1.y = mx;
				line.p2.y = mn;
			}	
			
			
			
			return line;	
			
			
		}
		
	}
}


PhotoEditor.prototype.initAction_cellresize = function()
{
	var self = this;

	var over;
	
	var selected;
	
	
	
	function mousedown(e)
	{
		selected = undefined;
		
		var p = e.SinglePos;
			
		var parent = self.grid[0];

		var	line = self.getLineByPoint(p);
	   
		if (line)
		{
	   		
			selected = {};	
	   		
			selected.line =	line;
				
			selected.magnet = self.Magnet();
	   		
			
			selected.points = [];
		
			selected.mx = 1;
			selected.mn = 0;
	   
			if (line.p1.x == line.p2.x)
			{	 
	   
	   
	     	
				self.grid.forEach(function(cell){
						if (line.p1.y <= cell.p1.y && line.p2.y >= cell.p2.y)
						{
							if (cell.p1.x == line.p1.x)
							{
								selected.mx = Math.min(selected.mx,cell.p2.x);	
								selected.points.push(cell.p1);	
							}	
							else
							if (cell.p2.x == line.p1.x)
							{
								selected.mn = Math.max(selected.mn,cell.p1.x);		
								selected.points.push(cell.p2);	
							}
						}
					});
	   		
	   		
			}else
			{
				self.grid.forEach(function(cell){
	   		
						if (line.p1.x <= cell.p1.x && line.p2.x >= cell.p2.x)
						{
							if (cell.p1.y == line.p1.y)
							{
								selected.mx = Math.min(selected.mx,cell.p2.y);		
								selected.points.push(cell.p1);	
							}	
							else
							if (cell.p2.y == line.p1.y)
							{
								selected.mn = Math.max(selected.mn,cell.p1.y);		
								selected.points.push(cell.p2);	
							}
						}
					});
	   	
	   	
	   	
			}
	   
	   
			return true;
			
		}
	
		
		
		
	}
	

	
	function mousemove(e)
	{
		
		
		var p = e.SinglePos;
		
		if (!selected)
		{
			over = self.getLineByPoint(p);
				
			if (over)
			{
				if (over.p1.x == over.p2.x)
				{
					self.setCursor("col-resize");
					
				}else
				{
					self.setCursor("row-resize");
					
				}	
			}
				
		}
		else
		{	
			if (selected.line.p1.x == selected.line.p2.x)	
			{
					
				p.x = selected.magnet.x(p.x,0.02);
		
				if (p.x > selected.mx-Cell.min_size) p.x = selected.mx-Cell.min_size;
				if (p.x < selected.mn+Cell.min_size) p.x = selected.mn+Cell.min_size;	
					
					
			
				if (over) over.p1.x = over.p2.x	= p.x;
				selected.points.forEach(function(p2){
						p2.x = p.x;
					});	
			}else
			{
				p.y = selected.magnet.y(p.y,0.02);
					
				if (p.y > selected.mx-Cell.min_size) p.y = selected.mx-Cell.min_size;
				if (p.y < selected.mn+Cell.min_size) p.y = selected.mn+Cell.min_size;	
			
			
				if (over) over.p1.y = over.p2.y	= p.y;
				selected.points.forEach(function(p2){
						p2.y = p.y;
					});		
			}	
			
		}
		
		
	}	
	
	
	function mouseup(e)
	{
		if (selected)
		{   self.DoChange("cell");
			selected = undefined;	
		}
	}
	


	var old_onEndScene = this.onEndScene;

	this.onEndScene = function()
	{
		if (over)
		{
			self.ctx.save();
			self.ctx.lineWidth = 2;
			self.ctx.setLineDash([2, 1]);
			self.ctx.strokeStyle = "#1c02fd";


			self.ctx.beginPath();       
			self.ctx.moveTo(over.p1.x  * self.canvas.width, over.p1.y * self.canvas.height);   
			self.ctx.lineTo(over.p2.x  * self.canvas.width, over.p2.y * self.canvas.height);   
			self.ctx.stroke(); 




			self.ctx.restore();	
		}

	
		if (old_onEndScene)	old_onEndScene();
	}
	
	
	this.addEventListener("mousemove", mousemove);
	this.addEventListener("mouseup", mouseup);
	
	
	this.addEventListener("mousedown", mousedown);
}



PhotoEditor.prototype.getCellAddToPoint = function(p)
{
	var cell = this.getCellByPoint(p);

	var offset = 0.2;

	if (cell)
	{
	
		if (this.isCellDiv && cell.p1.x < p.x && cell.p2.x > p.x &&
			cell.p1.y < p.y && cell.p1.y + cell.h() * offset  > p.y)
		{
			return {side:'top',cell:cell,rect:{p1:{x:cell.p1.x,y:cell.p1.y},p2:{x:cell.p2.x,y:cell.p1.y + cell.h() * offset}}};
		}else
		if (this.isCellDiv && cell.p1.x < p.x && cell.p2.x > p.x &&
			cell.p2.y - cell.h() * offset  < p.y && cell.p2.y > p.y)
		{
			return {side:'bottom',cell:cell,rect:{p1:{x:cell.p1.x,y:cell.p2.y -cell.h() * offset},p2:{x:cell.p2.x,y:cell.p2.y}}};
		}else
		if (this.isCellDiv && cell.p1.y < p.y && cell.p2.y > p.y &&
			cell.p1.x < p.x && cell.p1.x + cell.w() * offset  > p.x)
		{
			return {side:'left',cell:cell,rect:{p1:{y:cell.p1.y,x:cell.p1.x},p2:{y:cell.p2.y,x:cell.p1.x + cell.w() * offset}}};

		}else
		if (this.isCellDiv && cell.p1.y < p.y && cell.p2.y > p.y &&
			cell.p2.x - cell.w() * offset  < p.x && cell.p2.x > p.x)
		{
			return {side:'right',cell:cell,rect:{p1:{y:cell.p1.y,x:cell.p2.x -cell.w() * offset},p2:{y:cell.p2.y,x:cell.p2.x}}};
		}else return {side:'center',cell:cell,rect:{p1:{y:cell.p1.y,x:cell.p1.x},p2:{y:cell.p2.y,x:cell.p2.x}}};	
	}	
}



PhotoEditor.prototype.initAction_cellAdd = function()
{
	var self = this;	
	
	var cell_to_add;	
	
	var DragAndDrop_mousemove = function(e)
	{
		if(e.DragAndDrop.type =="img")
		{
			var p = e.SinglePos;
			
			cell_to_add = self.getCellAddToPoint(p);
		}
	}
	
	
	self.addEventListener("DragAndDrop_mousemove", DragAndDrop_mousemove);
		
	var DragAndDrop_mouseup = function(e)	 
	{
		if (e.DragAndDrop.type =="img" && cell_to_add)
		{
			var target = cell_to_add.cell;
			
			
			if (self.isCellDiv)
			{
				switch (cell_to_add.side) 
				{
					case 'top':
					target = cell_to_add.cell.add_vertical(true,true);
					break;
					case 'bottom':
					target = cell_to_add.cell.add_vertical(false,true);
					break;
					case 'left':
					target = cell_to_add.cell.add_horizontal(true,true);
					break;
					case 'right':
					target = cell_to_add.cell.add_horizontal(false,true);
					break;
    
				}
			}	
			target.setImg(DragAndDrop.img);	
			
			self.selection = new Selection(self,target);
			self.DoChange("selection");	
		
			cell_to_add = undefined;
			
			
		}
			
		
	}
	
	
	self.addEventListener("DragAndDrop_mouseup", DragAndDrop_mouseup);
	
	
	
	var old_onEndScene = this.onEndScene;
	
	this.onEndScene = function()
	{
		if (cell_to_add)
		{
			self.ctx.save();	
			self.ctx.lineWidth = 2;
			self.ctx.setLineDash([2, 1]);
			self.ctx.strokeStyle = "#1c02fd";	
			self.ctx.strokeRect(cell_to_add.rect.p1.x * self.canvas.width, cell_to_add.rect.p1.y*self.canvas.height, (cell_to_add.rect.p2.x-cell_to_add.rect.p1.x)*self.canvas.width, (cell_to_add.rect.p2.y-cell_to_add.rect.p1.y)*self.canvas.height);
			self.ctx.restore();	
		}
		
		if (old_onEndScene)	old_onEndScene();
	}
}


PhotoEditor.prototype.initAction_OpenBackground = function()
{
	var self = this;	
	 
	var DragAndDrop_mouseup = function(e)	 
	{
		if (e.DragAndDrop.type =="background")	
		{
			var p = e.SinglePos;
		
			if (p.x > 0 && p.x < 1 && p.y > 0 && p.y < 1)
			{
			
				self.setBackground(DragAndDrop.img);		
			    
			}
		}	
		
	}	
	
	this.addEventListener("DragAndDrop_mouseup", DragAndDrop_mouseup);
}


PhotoEditor.prototype.initAction_smileAdd = function()
{
	var self = this;	
	
	var DragAndDrop_mouseup = function(e)	 
	{
		if (e.DragAndDrop.type =="smile")	
		{
			var p = e.SinglePos;
		
			if (p.x > 0 && p.x < 1 && p.y > 0 && p.y < 1)
			{
				self.AddSmile(DragAndDrop.img,p.x,p.y);
			}
			
			
			
		}	
	
	}	
	
	
	this.addEventListener("DragAndDrop_mouseup", DragAndDrop_mouseup);
}

PhotoEditor.prototype.initAction_PhotoOperations = function()
{
	var self = this;
	
	function mousedown(e)
	{
		var p1 = e.SinglePos;
		var cell1 = self.getCellByPoint(p1);	
		if (cell1.resource)
		{
			var old_offset_x = cell1.resource.offset_x;
			var old_offset_y = cell1.resource.offset_y;
		}
	

		function mousemove(e)
		{
			self.setCursor("move");
			
			var p2 = e.SinglePos;
			var cell2 = self.getCellByPoint(p2);	
			
			if (self.isOutPhoto && cell1 !== cell2)
			{
				
				
				remove();
				new DragAndDrop("img",cell1.resource.img);	
				
				function DragAndDrop_mouseup1(e2)
				{
					var p3 = e2.SinglePos;
					var cell3 = self.getCellByPoint(p3);
					var old_res = cell3.resource;
					setTimeout(function(){
							cell1.resource = old_res;
							self.removeEventListener("DragAndDrop_mouseup", DragAndDrop_mouseup1);	
						},0);
			      
				}
				
				self.addEventListener("DragAndDrop_mouseup", DragAndDrop_mouseup1,true);	
				
			}else
			if (cell1.resource)
			{
				var rect = cell1.RectOut(); 
				
				var cell_single_w = (1 / self.canvas.width) * rect.w;
				var cell_single_h = (1 / self.canvas.height) * rect.h;
				
				var sina = Math.sin(cell1.resource.rotate), cosa = Math.cos(cell1.resource.rotate);
				
				var imgW = Math.abs(cell1.resource.img.original.width*cosa) + Math.abs(cell1.resource.img.original.height*sina);
				var imgH = Math.abs(cell1.resource.img.original.width*sina) + Math.abs(cell1.resource.img.original.height*cosa);
			    
			    
				var s = Math.max((rect.w / imgW),(rect.h / imgH)) * cell1.resource.zoom;
				
				
				cell1.resource.offset_x = old_offset_x + ((1 / cell_single_w)  *  (p1.x - p2.x));
				cell1.resource.offset_y = old_offset_y + ((1 / cell_single_h)  *  (p1.y - p2.y));
				
				cell1.resource.offset_x = Math.max(0,cell1.resource.offset_x);
				cell1.resource.offset_y = Math.max(0,cell1.resource.offset_y);
				
				cell1.resource.offset_x = Math.min(((imgW * s)/rect.w)-1,cell1.resource.offset_x); 
				cell1.resource.offset_y = Math.min(((imgH * s)/rect.h)-1,cell1.resource.offset_y); 
				
				
			}
			return true;
		}

		function remove()
		{
		
			if (cell1.resource)
			{
				self.DoChange("cell");
			}	
			self.removeEventListener("mouseup", remove,true);
			self.removeEventListener("mousemove", mousemove,true);	
		}

		if (cell1.resource)
		{
			self.addEventListener("mousemove", mousemove,true);
			self.addEventListener("mouseup", remove,true);		
		}
      	
	
	
	}	
	
	
	function wheel(e)
	{
		var p = e.SinglePos;
		var cell = self.getCellByPoint(p);	
		if (cell && cell.resource)
		{
			var delta = e.deltaY || e.detail || e.wheelDelta;	
			cell.photo_zoom(cell.resource.zoom * (delta > 0?0.8:1.2));
			self.DoChange("cell");
			return true;
		}	
	}
	
	
	
	this.addEventListener("wheel", wheel);
	

	this.addEventListener("mousedown", mousedown);	
}




PhotoEditor.prototype.initAction_selection = function()
{
	var self = this;
	function mousedown(e) 	
	{  
		var p = e.SinglePos;
		var element = self.GetLayerByPoint(p);
	   
		if (!element)element = self.getCellByPoint(p);
	
		if (element)
		{
			if (!self.selection || self.selection.element !== element)	
			{
				self.selection = new Selection(self,element);		
				self.DoChange("selection");
				
				if(self.selection && self.selection.element instanceof Layer)
				{
					self.layers.splice(self.layers.indexOf(self.selection.element), 1);		
					self.layers.push(self.selection.element);
					
				}	
			}
			
		}else 
		self.selection = undefined;	
		
		
		
	}
	function mouseup(e)
	{
		 
		
		if (self.selection)
		{
			var p = e.SinglePos;
			var element = self.GetLayerByPoint(p);
	    
			if (!element)element = self.getCellByPoint(p);
	        
	        
			if (element !== self.selection.element)
			{
				self.selection = undefined;	
				self.DoChange("selection");	
			}
		}	
		
	}	


	var old_onEndScene = this.onEndScene;
	
	this.onEndScene = function()
	{
		if (self.selection)
		{
			self.ctx.save();	
	
			self.ctx.lineWidth = 1;
			
			
			//self.ctx.setLineDash([1, 1]);
			
			
			self.ctx.strokeStyle = "#23cadc";
			
		
			var rect = self.selection.rect();
			self.ctx.strokeRect(rect.x * this.editor.canvas.width, rect.y * this.editor.canvas.height, rect.w * this.editor.canvas.width, rect.h * this.editor.canvas.height);
			
			self.ctx.restore();	
		}
		if (old_onEndScene)	old_onEndScene();
	}


	
	this.addEventListener("mousedown", mousedown);
	this.addEventListener("mouseup", mouseup);
}


PhotoEditor.prototype.dist = function(a,b)
{
	var x = a.x - b.x;
	var y = a.y - b.y;	
	return Math.sqrt((x*x)+(y*y));	
}


PhotoEditor.prototype.initAction_LayerOperations = function()
{
	var self = this;
	
	var img_move = new Image();
	img_move.src = "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gU3ZnIFZlY3RvciBJY29ucyA6IGh0dHA6Ly93d3cub25saW5ld2ViZm9udHMuY29tL2ljb24gLS0+DQo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgMTAwMCAxMDAwIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCAxMDAwIDEwMDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPG1ldGFkYXRhPiBTdmcgVmVjdG9yIEljb25zIDogaHR0cDovL3d3dy5vbmxpbmV3ZWJmb250cy5jb20vaWNvbiA8L21ldGFkYXRhPg0KPGc+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMC4wMDAwMDAsNTExLjAwMDAwMCkgc2NhbGUoMC4xMDAwMDAsLTAuMTAwMDAwKSI+PHBhdGggZD0iTTQwODMuMyw0MDk4LjdMMzE3Ny41LDMxOTNoNjI2LjRoNjI4LjZsNC40LTExMTcuNWw2LjUtMTExOS43bDU1Mi4yLTYuNWw1NTAtNC40djExMjQuMVYzMTkzSDYxODNoNjM5LjVsLTkwNS44LDkwNS44Yy00OTcuNiw0OTcuNi05MTAuMiw5MDUuOC05MTYuNyw5MDUuOFM0NTgwLjksNDU5Ni40LDQwODMuMyw0MDk4Ljd6Ii8+PHBhdGggZD0iTTEwMDUuOCwxMDIxLjJMMTAwLDExNS41bDkxMi4zLTkxMi4zbDkxMC4yLTkxMC4ydjYzOS41djYzNy4zaDExMTkuN2gxMTE3LjVsLTIuMiw0NDMuMWMtMi4yLDI0Mi4zLTQuNCw0ODYuNy02LjUsNTQ1LjdsLTIuMiwxMDIuNkgzMDM1LjZIMTkyMi41bC00LjQsNjMzbC02LjUsNjMzTDEwMDUuOCwxMDIxLjJ6Ii8+PHBhdGggZD0iTTgwNzMuMSwxMzA1bC02LjUtNjMzbC0xMTE3LjUtNi41bC0xMTE5LjctNC40VjExNS41di01NDUuN2wxMTE5LjctNC40bDExMTcuNS02LjVsMTAuOS02MjguNmwxMC45LTYyNi40bDkwNS44LDkwNS44TDk5MDAsMTE1LjVsLTkxMC4yLDkxMC4ybC05MTIuMyw5MTIuM0w4MDczLjEsMTMwNXoiLz48cGF0aCBkPSJNNDQzMi41LTE4NDguOXYtMTExMy4xSDM4MTdoLTYxNy43bDkxMi4zLTkxMC4ybDkxMC4yLTkxMi4zbDkxMC4yLDkxMi4zbDkxMi4zLDkxMC4yaC02NTAuNGgtNjQ4LjJ2MTExMy4xdjExMTMuMWgtNTU2LjZoLTU1Ni42Vi0xODQ4Ljl6Ii8+PC9nPjwvZz4NCjwvc3ZnPg==";
	
	var img_resize = new Image();
	img_resize.src = "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE4LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDE4Mi45MzEgMTgyLjkzMSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTgyLjkzMSAxODIuOTMxOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8cGF0aCBkPSJNMTczLjkzLDkyLjc5OGMtNC45NzEsMC05LDQuMDI5LTksOXY1MC40MDRMMzAuNzI4LDE4aDUwLjQwNGM0Ljk3MSwwLDktNC4wMjksOS05cy00LjAyOS05LTktOUg5QzQuMDMsMCwwLDQuMDI5LDAsOQ0KCXY3Mi4xMzJjMCw0Ljk3MSw0LjAyOSw5LDksOXM5LTQuMDI5LDktOVYzMC43MjlsMTM0LjIwMiwxMzQuMjAyaC01MC40MDRjLTQuOTcxLDAtOSw0LjAyOS05LDlzNC4wMjksOSw5LDloNzIuMTMyDQoJYzQuOTcxLDAsOS00LjAyOSw5LTl2LTcyLjEzMkMxODIuOTMsOTYuODI4LDE3OC45MDEsOTIuNzk4LDE3My45Myw5Mi43OTh6Ii8+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8L3N2Zz4NCg==";
	
	var img_edit = new Image();
	img_edit.src = "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNi4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMzJweCIgaGVpZ2h0PSIzMnB4IiB2aWV3Qm94PSIwIDAgMzIgMzIiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDMyIDMyIiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxwYXRoIGlkPSJwZW4iIGZpbGw9IiMzMzMzMzMiIGQ9Ik0yOS4zOTUsMi41OEMyNy43NSwwLjkzNywyNS41ODQsMCwyMy40NDksMGMtMS44MDEsMC0zLjQ1OSwwLjY2OC00LjY3LDEuODc3bC00Ljg2Nyw0LjkwNA0KCWMtMC4wMTUsMC4wMTQtMC4wMzIsMC4wMjMtMC4wNDcsMC4wMzhjLTAuMDA4LDAuMDA4LTAuMDEzLDAuMDE5LTAuMDIxLDAuMDI2bDAuMDAyLDAuMDAyTDMuNTE3LDE3LjI1Ng0KCWMtMC40NzYsMC40NzMtMC44MjEsMS4wNjItMS4wMTMsMS43MDVsLTIuMzQ5LDguNTA4QzAuMTUzLDI3LjQ5MiwwLDI4LjE2LDAsMjguNUMwLDMwLjQzMiwxLjU2OSwzMiwzLjUwNCwzMg0KCWMwLjM4NSwwLDEuMTMtMC4xODQsMS4xNTctMC4xODhsOC40NzgtMi4yMjljMC42NDQtMC4xOTEsMS4yMjktMC41MzksMS43MDUtMS4wMTZsMTUuMjYzLTE1LjM4Mw0KCUMzMi44ODMsMTAuNDA2LDMyLjU3LDUuNzUsMjkuMzk1LDIuNTh6IE0xNi4wMTQsMjMuNzk1Yy0wLjA4Mi0wLjkwMi0wLjMzNy0xLjc4Ny0wLjcxOS0yLjYyN2w5LjQ1NS05LjQ1NA0KCWMwLjU3OCwxLjgyNiwwLjI4MSwzLjczNi0wLjk4Niw1LjAwNGMtMC4wMDgsMC4wMDgtMC4wMTgsMC4wMTMtMC4wMjUsMC4wMjFsMC4wMTQsMC4wMTNsLTcuNzI4LDcuNzkNCglDMTYuMDI1LDI0LjI5MywxNi4wMzcsMjQuMDQ5LDE2LjAxNCwyMy43OTV6IE0xNC43OTMsMjAuMjU2Yy0wLjM3My0wLjYxMy0wLjc5Ny0xLjIwNS0xLjMyMi0xLjcyOQ0KCWMtMC42MTEtMC42MTEtMS4zMTItMS4wOS0yLjA0NC0xLjQ5Mmw5LjUzMi05LjUzMmMwLjc0OCwwLjMzMiwxLjQ2NSwwLjgwNSwyLjA5OCwxLjQzOGMwLjU0MSwwLjUzOSwwLjk1OSwxLjE0MywxLjI4MSwxLjc3MQ0KCUwxNC43OTMsMjAuMjU2eiBNMTAuNDg2LDE2LjU2MmMtMC45MjYtMC4zNzMtMS44OTYtMC41ODYtMi44NjgtMC41OTlsNy43MDMtNy43NjJjMS4xNzktMS4xNSwyLjg5Ni0xLjQ4MSw0LjU4Ny0xLjA2Mg0KCUwxMC40ODYsMTYuNTYyeiBNNC4xNjcsMjkuODczQzQuMDU4LDI5Ljg5OCwzLjcxOSwyOS45ODQsMy40ODksMzBDMi42NjcsMjkuOTksMiwyOS4zMjIsMiwyOC41DQoJYzAuMDEyLTAuMTY4LDAuMDc5LTAuNDU3LDAuMTAyLTAuNTYybDEuMDUzLTMuODE0YzEuMTQzLTAuMDMxLDIuMzczLDAuNDE0LDMuMzQsMS4zODNjMC45ODIsMC45OCwxLjQ0NCwyLjIzNCwxLjM5NCwzLjM5MQ0KCUw0LjE2NywyOS44NzN6IE04Ljg3NCwyOC42MzdjLTAuMDI0LTEuMzQyLTAuNTctMi43MzgtMS42NzItMy44MzhDNi4xNiwyMy43NTYsNC43OTYsMjMuMTU0LDMuNDM2LDIzLjFsMC45OTYtMy42MDcNCgljMC4wNzItMC4yNCwwLjIxNS0wLjQ3NywwLjM5MS0wLjY4NGMyLjAwNi0xLjQzNiw1LjA5MS0xLjAxMiw3LjIzNCwxLjEzM2MyLjI2NywyLjI2NiwyLjYxNyw1LjU4NiwwLjg3MSw3LjU2OA0KCWMtMC4xMTYsMC4wNjEtMC4yMzMsMC4xMTktMC4zNTksMC4xNTZMOC44NzQsMjguNjM3eiBNMjguNjkxLDExLjc3MmwtMS42ODQsMS42OTdjMC0wLjIyNiwwLjAyNy0wLjQ0MywwLjAwNi0wLjY3NA0KCWMtMC4xNzYtMS45MzUtMS4wNzgtMy44MDYtMi41NDMtNS4yNjljLTEuNjI5LTEuNjMtMy43ODktMi41NjUtNS45MjgtMi41NzFsMS42NTYtMS42N0MyMS4wMjcsMi40NTgsMjIuMTg0LDIsMjMuNDQ5LDINCgljMS42MDksMCwzLjI2MiwwLjcyOCw0LjUzMywxLjk5NWMxLjE5MywxLjE5MSwxLjkwNCwyLjY3MSwyLjAwNiw0LjE2OEMzMC4wODIsOS41NiwyOS42MjEsMTAuODQxLDI4LjY5MSwxMS43NzJ6Ii8+DQo8L3N2Zz4NCg==";
	
	var img_rot = new Image();
	img_rot.src = "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gU3ZnIFZlY3RvciBJY29ucyA6IGh0dHA6Ly93d3cub25saW5ld2ViZm9udHMuY29tL2ljb24gLS0+DQo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgMTAwMCAxMDAwIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCAxMDAwIDEwMDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPG1ldGFkYXRhPiBTdmcgVmVjdG9yIEljb25zIDogaHR0cDovL3d3dy5vbmxpbmV3ZWJmb250cy5jb20vaWNvbiA8L21ldGFkYXRhPg0KPGc+PHBhdGggZD0iTTY2OSw3NTMuOWMtNTMuMiwzOS45LTExNS41LDU5LjgtMTc5LjIsNTguOWMtOC40LTAuMS03OC41LTExLjgtODQuNS0xMy44Yy0yLjktMS01OC0yNS44LTU4LjMtMjZjLTI1LjYtMTUtNDktMzMuOC02OS42LTU1LjdjLTAuMy0wLjMtMjEuNy0yNS45LTIyLjktMjcuNmMtNDEuOS01NS45LTcyLjgtMTM3LjItNzIuOC0yMTQuM0gyNTVsLTEyMi41LTE5NkwxMCw0NzUuNWg3My41YzAsOTAuNSwzMy41LDE4NSw3OC40LDI1NC40YzAuNiwxLDQ2LjYsNTkuOSw0Ny4zLDYwLjdjMjYuOSwyOC44LDU3LjQsNTMsOTAuNSw3Mi41YzAuOSwwLjUsNTYuNiwyNy4zLDY1LjQsMzAuNWM0LjIsMS41LDI4LjEsOC45LDM2LDEwLjljNS4zLDEuMyw1MC4xLDkuNCw1Mi44LDkuN2MxMy41LDEuNCwyNi45LDIuNCw0MC4zLDIuNGM4MS45LDAsMTYxLjctMjYuOCwyMzAuMy03OGMyMS44LTE2LjQsMjcuMS00OC42LDExLjgtNzJDNzIwLjksNzQzLjIsNjkwLjgsNzM3LjUsNjY5LDc1My45IE0zMzEsMjQ2LjFjNTMuMi0zOS45LDExNS41LTU5LjgsMTc5LjItNTguOWM4LjQsMC4xLDc4LjUsMTEuOCw4NC41LDEzLjhjMi45LDEsNTgsMjUuOCw1OC40LDI2YzI1LjYsMTUsNDksMzMuOCw2OS42LDU1LjdjMC4zLDAuMywyMS43LDI1LjksMjIuOSwyNy42YzQxLjgsNTUuOSw3Mi44LDEzNy4yLDcyLjgsMjE0LjNINzQ1bDEyMi41LDE5NmwxMjIuNS0xOTZoLTczLjVjMC05MC41LTMzLjUtMTg1LTc4LjQtMjU0LjRjLTAuNS0xLTQ2LjUtNTkuOS00Ny4zLTYwLjdjLTI2LjktMjguOC01Ny40LTUzLTkwLjUtNzIuNWMtMC45LTAuNS01Ni42LTI3LjMtNjUuNC0zMC41Yy00LjItMS41LTI4LjEtOC45LTM2LTEwLjljLTUuMy0xLjMtNTAuMi05LjQtNTIuOC05LjdjLTEzLjUtMS40LTI2LjktMi40LTQwLjMtMi40Yy04MS45LDAtMTYxLjcsMjYuOC0yMzAuMyw3OGMtMjEuOCwxNi40LTI3LjEsNDguNi0xMS44LDcyQzI3OS4xLDI1Ni44LDMwOS4yLDI2Mi41LDMzMSwyNDYuMSIvPjwvZz4NCjwvc3ZnPg==";
	    
	var offset = 12;
	var radius = 10;
	
	
	function get_pos_move(rect)
	{
		return {x:rect.x+rect.w/2,y:rect.y-((1 / self.canvas.height) * offset)};	
	}
	
	function get_pos_resize(rect)
	{
		return {x:rect.x+rect.w+((1 / self.canvas.width) * offset),y:rect.y+rect.h+((1 / self.canvas.height) * offset)};	
	}
	
	function get_pos_edit(rect)
	{
		return {x:rect.x-((1 / self.canvas.width) * offset),y:rect.y+rect.h+((1 / self.canvas.height) * offset)};	
	}
	
	function get_pos_rot(rect)
	{
		return {x:rect.x+rect.w/2,y:rect.y+rect.h+((1 / self.canvas.height) * offset)};	
	}
	
	
	function mousedown(e) 	
	{  	
		var p1 = e.SinglePos;
		var layer;
		
		
		
		
		function translate()
		{
			function mousemove(e)
			{
				var p2 = e.SinglePos;	
				
				layer.x = old_offset_x - (p1.x - p2.x);
				layer.y = old_offset_y - (p1.y - p2.y);
	
				layer.x  = Math.max(layer.x,-layer.w/2);
	
				layer.y  = Math.max(layer.y,-layer.h/2);
	
				layer.x  = Math.min(layer.x,1-layer.w/2);
	
				layer.y  = Math.min(layer.y,1-layer.h/2);
				
				self.setCursor("move");
	
				return true;
			}	
		
	
	
			function remove()
			{
				
				self.DoChange('layer');
				self.removeEventListener("mouseup", remove,true);
				self.removeEventListener("mousemove", mousemove,true);	
			}
		
		
			var old_offset_x = layer.x;
			var old_offset_y = layer.y;
			
			self.addEventListener("mousemove", mousemove,true);
			self.addEventListener("mouseup", remove,true);
				
		
		}
		
		
		function rotate()
		{   let old_rotate = layer.rotate;
			let angle1 = Math.atan2(p1.x - layer.c_x(),p1.y - layer.c_y());
			
			function mousemove(e)
			{	
				var p2 = e.SinglePos;
				let angle2 = Math.atan2(p2.x - layer.c_x(),p2.y - layer.c_y());
				layer.rotate = (old_rotate + (angle1 - angle2)) % (Math.PI * 2);
			}	
			
			
			function remove()
			{
				
				self.DoChange('layer');
				self.removeEventListener("mouseup", remove,true);
				self.removeEventListener("mousemove", mousemove,true);	
			}
			
			
			self.addEventListener("mousemove", mousemove,true);
			self.addEventListener("mouseup", remove,true);
			
		}
	
	
	
		function resize()
		{
			
			function mousemove(e)
			{
				var minsize = 0.01; // минимальный размер
				
				var p2 = e.SinglePos;
				
				layer.w = Math.max(minsize, old_w - (p1.x - p2.x));
				
				layer.h = (old_h / old_w) *  layer.w;
				
				self.setCursor("nwse-resize");	
				return true;
			}
			
			
			
			function remove()
			{
				
				self.DoChange('layer');
				self.removeEventListener("mouseup", remove,true);
				self.removeEventListener("mousemove", mousemove,true);	
			}
			
			var old_w = layer.w;
			var old_h = layer.h;
			
			self.addEventListener("mousemove", mousemove,true);
			self.addEventListener("mouseup", remove,true);
			
			
		}
		
		
		if (self.selection && self.selection.element instanceof Layer)
		{
			layer = self.selection.element;	
			
		}else 
		{
		  layer = self.GetLayerByPoint(p1);	
		  self.selection = new Selection(self,layer);	
		}
		
		
		if (layer)
		{
			
			var r = layer.rect();
			
			var mn = (1 / Math.max(self.canvas.width,self.canvas.height)) * 10;
			
			if (self.dist(get_pos_resize(r),p1) < mn)
			{
				resize();
				return true;	
			}else
			if (self.dist(get_pos_rot(r),p1) < mn)
			{
				rotate();	
				return true;
			}else
			if (layer.type == "text" && self.dist(get_pos_edit(r),p1) < mn)
			{
				self.showTextarea(layer);
				return true;
				
			}if (self.dist(get_pos_move(r),p1) < mn)
			{
				translate();
				return true;
				
			}else if (r.x <=  p1.x && r.y <=  p1.y && r.x + r.w >= p1.x && r.y + r.h >= p1.y)
			{
				translate();
				return true;	
			}
			
			
		}
		
		
	}
	
	
	this.addEventListener("mousedown", mousedown);	
	
	
	function mousemove(e)
	{
		var p = e.SinglePos;
		
		
		
		if (self.selection && self.selection.element instanceof Layer)
		{
			var layer = self.selection.element;	
			var r = layer.rect();
			
			var mn = (1 / Math.max(self.canvas.width,self.canvas.height)) * 10;
	 	
			if (self.dist(get_pos_resize(r),p) < mn)
			{
				self.setCursor("nwse-resize");	
			}else
			if (layer.type == "text" && self.dist(get_pos_edit(r),p) < mn)
			{
				self.setCursor("text");	
			
			}else
			if (self.dist(get_pos_rot(r),p) < mn)
			{
				self.setCursor("pointer");	
			
			}
			else
			if (self.dist(get_pos_move(r),p) < mn)
			{
				self.setCursor("move");	
			}else
			if (r.x <=  p.x && r.y <=  p.y && r.x+r.w >= p.x && r.y+r.h >= p.y)
			{
				self.setCursor("move");
			}
			
			return true;
		}
	}
	
	
	this.addEventListener("mousemove",mousemove);	
	
	var	old_onEndScene = this.onEndScene;
	
	this.onEndScene = function()
	{
		if (self.selection && self.selection.element instanceof Layer)
		{
			var layer = self.selection.element;	
			var r = layer.rect();
			
			self.ctx.save();
			
			self.ctx.fillStyle = "#23cadc";	
			
			self.ctx.save();
			self.ctx.beginPath();
			var p = get_pos_resize(r);
			
			self.ctx.translate(p.x* self.canvas.width,p.y* self.canvas.height);
			self.ctx.drawImage(img_resize,-radius,-radius,radius*2,radius*2);
			self.ctx.restore();	
	        
	        
			self.ctx.save();
			self.ctx.beginPath();
			var p = get_pos_rot(r);
			self.ctx.translate(p.x* self.canvas.width,p.y* self.canvas.height);
            
			self.ctx.drawImage(img_rot,-radius,-radius,radius*2,radius*2);
			self.ctx.restore();	
	        
	       
			if (layer.type == "text")
			{
				self.ctx.save();
				self.ctx.beginPath();
				var p = get_pos_edit(r);
				self.ctx.translate(p.x* self.canvas.width,p.y* self.canvas.height);
				self.ctx.drawImage(img_edit,-radius,-radius,radius*2,radius*2);
				self.ctx.restore();	
			}
			
			self.ctx.save();
			self.ctx.beginPath();
			var p = get_pos_move(r);
			self.ctx.translate(p.x* self.canvas.width,p.y* self.canvas.height);
			self.ctx.drawImage(img_move,-radius,-radius,radius*2,radius*2);
			self.ctx.restore();
			
			
			
			self.ctx.restore();	
		}
		if (old_onEndScene)	old_onEndScene();
	}
	
	
		
}

PhotoEditor.prototype.drawBackground = function()
{
	this.ctx.save();
	
	var sx =  (1 / (1-(this.offset_cell_x *2)));
	var sy =  (1 / (1-(this.offset_cell_y *2)));
	
	this.ctx.translate(-(this.canvas.width * (sx - 1))/2,-(this.canvas.height * (sy - 1))/2);
	
	this.ctx.scale(sx,sy);
		
	this.ctx.fillStyle = this.Background_color;	
	this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
	
	
	
	if (this.Background)
	{
		var s = Math.max(this.canvas.width / this.Background.original.width,this.canvas.height / this.Background.original.height);
		this.ctx.scale(s,s);
		this.ctx.drawImage(this.Background.original, 0, 0);
	}
	

	this.ctx.restore();	
}





PhotoEditor.prototype.roundRect = function(x, y, width, height, radius)
{
	if (typeof radius === 'undefined') {
		radius = 0;
	}
	if (typeof radius === 'number') {
		radius = {tl: radius, tr: radius, br: radius, bl: radius};
	} else {
		var defaultRadius = {tl: 0, tr: 0, br: 0, bl: 0};
		for (var side in defaultRadius) {
			radius[side] = radius[side] || defaultRadius[side];
		}
	}
 
	this.ctx.moveTo(x + radius.tl, y);
	this.ctx.lineTo(x + width - radius.tr, y);
	this.ctx.quadraticCurveTo(x + width, y, x + width, y + radius.tr);
	this.ctx.lineTo(x + width, y + height - radius.br);
	this.ctx.quadraticCurveTo(x + width, y + height, x + width - radius.br, y + height);
	this.ctx.lineTo(x + radius.bl, y + height);
	this.ctx.quadraticCurveTo(x, y + height, x, y + height - radius.bl);
	this.ctx.lineTo(x, y + radius.tl);
	this.ctx.quadraticCurveTo(x, y, x + radius.tl, y);
}






PhotoEditor.prototype.drawGrid = function()
{
	for (var key in this.grid)	
	{
		var cell = this.grid[key];
		
		var anim = cell.anim();
		
		this.ctx.save();
		
		this.ctx.beginPath();
		
		var rect = cell.RectOut();
		
		
		this.ctx.translate(rect.x, rect.y);
		
		this.roundRect(0, 0, rect.w, rect.h,this.radius);
		
		this.ctx.clip();
		
		this.ctx.fillStyle = this.ColorCell;
		this.ctx.fillRect(0, 0, rect.w, rect.h);
		
		
		
		if (cell.resource)
		{
			var sina = Math.sin(cell.resource.rotate), cosa = Math.cos(cell.resource.rotate)
			var imgW = Math.abs(cell.resource.img.original.width*cosa) + Math.abs(cell.resource.img.original.height*sina);
			var imgH = Math.abs(cell.resource.img.original.width*sina) + Math.abs(cell.resource.img.original.height*cosa);
			
			
			
			
			var s = Math.max((rect.w / imgW),(rect.h / imgH)) * cell.resource.zoom;
			
			var offset_x = cell.resource.offset_x;
			var offset_y = cell.resource.offset_y;
			
			offset_x = Math.max(0,offset_x);
			offset_y = Math.max(0,offset_y);
				
			offset_x = Math.min(((imgW * s)/rect.w)-1,offset_x); 
			offset_y = Math.min(((imgH * s)/rect.h)-1,offset_y); 
			
			this.ctx.translate(-offset_x*rect.w, -offset_y*rect.h);
			
			this.ctx.scale(s,s);
			
			this.ctx.translate(imgW/2,imgH/2);//поворот
			this.ctx.rotate(cell.resource.rotate);
			this.ctx.translate(-cell.resource.img.original.width/2,-cell.resource.img.original.height/2);	
			
			
			if (cell.resource.invert_x)
			{
				this.ctx.translate(imgW,0);
				this.ctx.scale(-1,1);
			}
			if (cell.resource.invert_y)
			{
				this.ctx.translate(0,imgH);
				this.ctx.scale(1,-1);
			}
			
			this.ctx.drawImage(cell.resource.img.original, 0, 0);
		}
		
		
		
		
		this.ctx.restore();
	}	
}



PhotoEditor.prototype.drawLayers = function()
{
	for (var key in this.layers)	
	{
		var layer = this.layers[key];
		
		
		if (layer.show)
		{
			
			this.ctx.save();	

			this.ctx.translate(this.canvas.width * layer.x , this.canvas.height * layer.y);

			this.ctx.translate(this.canvas.width * (layer.w / 2) , this.canvas.height * (layer.h / 2));
			this.ctx.rotate(layer.rotate);
			this.ctx.translate(this.canvas.width * -(layer.w / 2) , this.canvas.height * -(layer.h / 2));
		    
		
			this.ctx.scale((layer.w * this.canvas.width)/layer.src_w,(layer.h * this.canvas.height) / layer.src_h);





			if (layer.type == "text")
			{
				this.ctx.fillStyle = layer.color;
				this.ctx.font = layer.size + "px " + layer.font;	
				this.ctx.textBaseline = "top";	
				this.ctx.textAlign = "center";
			
				var lines = layer.text.split("\n");
			
				for (var i = 0; i < lines.length; i++)
				{
					var line = lines[i];	
					this.ctx.fillText(line,layer.src_w/2,i * layer.size);
				}
				
			}else 
			if (layer.type == "smile")   
			{
				this.ctx.drawImage(layer.img.original, 0, 0);
			}
		
			this.ctx.restore();		
		
		}	}
}




PhotoEditor.prototype.render = function(isOffset)
{
	
	if (this.ctx !== undefined)	
	{  
		this.ctx.save();
		
		if (isOffset)
		{
			this.ctx.translate(this.canvas.width * this.offset_cell_x,this.canvas.height * this.offset_cell_y);
			this.ctx.scale(1-(this.offset_cell_x*2),1-(this.offset_cell_y*2));
		}
		this.drawBackground();
		
		if (this.onBeginScene)this.onBeginScene();
		
		this.drawGrid();
		
		this.drawLayers();
		
		
		
		
		if (this.onEndScene)this.onEndScene();
		
		this.ctx.restore();
	}
	
}



PhotoEditor.prototype.enable_drawing = function()
{
	if (!this.is_drawing)
	{
		var self = this;	
		this.is_drawing = true;	
		function UpDate()
		{
			if (self.is_drawing)
			{
				self.render();	
				window.requestAnimationFrame(UpDate);	
			}	
		}	
		
		UpDate();
	
	}	
}


PhotoEditor.prototype.disable_drawing = function()
{
	this.is_drawing = false;	
}


PhotoEditor.prototype.save_template = function()
{
	
	return btoa(JSON.stringify(this.grid.map(function(cell)
				{
					return {p1:cell.p1,p2:cell.p2};
				})));	
}

PhotoEditor.prototype.open_template = function(template)
{
	var self = this;	
	this.grid = [];	
	this.counter = 0;	
	template = atob(template);
	template = JSON.parse(template);		
	template.forEach(function(item)
		{
			var n = new Cell(self);
			n.id = self.counter++;
			n.p1 = item.p1;
			n.p2 = item.p2;
			self.grid.push(n);		
		});	
	
	this.selection = undefined;	
	this.DoChange("selection");	
	this.DoChange("cell");	
}



PhotoEditor.prototype.loadPhotos = function(list,ran,ratio,rotate)
{

	if (ran)list = list.sort(function(){return Math.random() - 0.5;});

	var cells = [];
	
	this.grid.forEach(function(cell){if (!cell.resource)cells.push(cell);});
		
		
	var cells = cells.sort(function(a,b){
			if (a.p1.y == b.p1.y)
			{
				return a.p1.x - b.p1.x;	
			}else
			{
				return a.p1.y - b.p1.y;		
			}
		});	
			
	for (var cell of cells) 
	{
		let img;	
			
		if (ratio)
		{
			img = list.find(function(img,i){
					if ((img.original.height >= img.original.width) == (cell.h()>=cell.w()))
					{
						list.splice(i, 1);
						return true; 	
					}	
				});
		}
            
            
            

		if (!img) img = list.shift();	
	
		if (img)
		{
			cell.setImg(img);
			if (rotate && ((img.original.height >= img.original.width) !== (cell.h()>=cell.w())))
			{
				cell.photo_rotate(Math.PI/2);	
			}
					
		}else
		break;
	}

}


PhotoEditor.prototype.getAllPhotos = function()
{
	
	var imgs = [];	

	var cells = this.grid.sort(function(a,b){
			if (a.p1.y == b.p1.y)
			{
				return a.p1.x - b.p1.x;	
			}else
			{
				return a.p1.y - b.p1.y;		
			}
		});
			
			
	
	cells.forEach(function(cell){if (cell.resource)imgs.push(cell.resource.img);});	

	return imgs;
	
}



PhotoEditor.prototype.out = function(width,height,isOffset = true)
{
	var old_canvas = this.canvas;
	var old_ctx = this.ctx;

	if (!width)width = old_canvas.width;
	if (!height)height = old_canvas.height;

	this.canvas = document.createElement('canvas');
	this.canvas.width = width;
	this.canvas.height = height;
	this.ctx = this.canvas.getContext('2d');
	
	this.ctx.save();
	if (isOffset)
	{
		this.ctx.translate(this.canvas.width * this.offset_cell_x,this.canvas.height * this.offset_cell_y);
		this.ctx.scale(1-(this.offset_cell_x*2),1-(this.offset_cell_y*2));
	}
	
	this.drawBackground();
	this.drawGrid();
	this.drawLayers();
	
	this.ctx.restore();
	
	
	var r = this.canvas.toDataURL('image/jpeg', 1.0);
	this.canvas = old_canvas;
	this.ctx = old_ctx;	
	return r;
}

PhotoEditor.prototype.GetLayerByPoint = function(point)
{

	
	return this.layers.find(function(layer){
			
			var r = layer.rect();
		
			if (r.x <= point.x && r.x + r.w >= point.x &&
				r.y <= point.y && r.y + r.h >= point.y)return true;});		
}


PhotoEditor.prototype.AddSmile = function(img,x,y)
{
	var self = this;	
	
		
	var smile  = new Layer(this);
	smile.x = x;
	smile.y = y;
	smile.type = "smile";
	smile.img = img;
	smile.show = true;
	smile.show = true;
	smile.rotate = 0;
		
		
	var mx = Math.max(img.original.width,img.original.height);
		
		
	smile.w = ((50 / mx)*img.original.width) / self.canvas.width;
	smile.h = ((50 / mx)*img.original.height) / self.canvas.height;
		
		
	smile.x -= smile.w / 2;
	smile.y -= smile.h / 2;
		
		
	
	smile.src_w = img.original.width;
	smile.src_h = img.original.height;
    
	self.layers.push(smile);
	
	this.selection = new Selection(this,smile);	
		
	self.DoChange('layer');
    
	
}


PhotoEditor.prototype.showTextarea = function(text)
{
	text.show = false;
	
	this.updateSizeText(text);	
	
	var container = document.createElement("div");	

	container.style.setProperty("position","fixed");
	container.style.setProperty("z-index","10000");

	

	
	var textarea = document.createElement("textarea");


	textarea.value = text.text;

	textarea.style.setProperty("position","absolute");
	
	var resize = function  ()
	{
	this.updateSizeText(text);	
	var rect = this.canvas.getBoundingClientRect();	
	container.style.setProperty("top",rect.top + "px");
	container.style.setProperty("left",rect.left + "px");
	container.style.setProperty("height",rect.height + "px");
	container.style.setProperty("width",rect.width + "px");	
	textarea.style.setProperty("font-size",text.size +"px");
    textarea.style.setProperty("line-height", text.size + "px");
	}.bind(this);
	
	window.addEventListener('resize', resize);
	
	resize();
	
	
    textarea.style.setProperty("top",(text.y * 100) + "%");
	textarea.style.setProperty("left",(text.x * 100) + "%");
	textarea.style.setProperty("width",(text.w * 100) + "%");
	textarea.style.setProperty("height",(text.h * 100) + "%");
	textarea.style.setProperty("background","none");
	textarea.style.setProperty("resize","none");

	textarea.style.setProperty("text-align","center");

	textarea.style.setProperty("overflow","hidden");


	textarea.style.setProperty("font-family",text.font);

	
	
	
	var deg = (180 / Math.PI) *  text.rotate; 
	
	textarea.style.setProperty("transform", "rotate("+deg+"deg)");

	textarea.style.setProperty("vertical-align", "top");
	textarea.style.setProperty("white-space", "nowrap");
	textarea.style.setProperty("color", text.color);
	textarea.style.setProperty("border","1px solid #8c535b");
	textarea.style.setProperty("min-width","50px");
	textarea.style.setProperty("box-sizing","content-box");
	textarea.style.setProperty("padding","5px");
	textarea.style.setProperty("margin-left","-5px");
	textarea.style.setProperty("margin-top","-5px");
	


	textarea.oninput = function()
	{
		text.text  = textarea.value;	
		this.updateSizeText(text);
		textarea.style.setProperty("width",(text.w * 100) + "%");
		textarea.style.setProperty("height",(text.h * 100) + "%");
		textarea.style.setProperty("font-size",text.size +"px");
		textarea.style.setProperty("line-height", text.size + "px");
	}.bind(this);



	


	container.appendChild(textarea);
	document.body.appendChild(container);
    
    
    
	setTimeout(function(){
			textarea.focus();	
		},0);
    
	textarea.onblur = function()
	{
		document.body.removeChild(container);
		text.show = true;
		if (text.text == ""){
			
			this.layers.splice(this.layers.indexOf(text), 1);	
			this.selection = undefined;	
		}else
		{
			this.selection = new Selection(self,text);		
		}
		
		
		this.DoChange('layer');
		this.DoChange("selection");	
			
		
	}.bind(this);
	


	this.selection = undefined;	
}	

PhotoEditor.prototype.updateSizeText = function(text)
{
	this.ctx.save();
	
	if (text.w && text.src_w)
	{
		text.size *= (text.w * this.canvas.width)/text.src_w;	
	}
	
	this.ctx.font = text.size + "px " + text.font;	

	var lines = text.text.split("\n");
	
	var mx = 0;
	
	for (var i = 0; i < lines.length; i++)
	mx = Math.max(this.ctx.measureText(lines[i]).width,mx);
	
	text.w = mx / this.canvas.width;
	text.h = (text.size * lines.length) / this.canvas.height;
	
	text.src_w = mx;
	text.src_h = (text.size * lines.length);
	
	this.ctx.restore();		
	
}



PhotoEditor.prototype.AddText = function(value,font,size,color)
{
	
	

	var text = new Layer(this);

	text.x = 0.5;
	text.y = 0.5;
	
	text.type = "text"; 
	text.text = value;
	text.font = font;
	text.color = color;
	text.size = size;
	text.show = true;
	text.rotate = 0;
	
	this.updateSizeText(text);
	
	text.x -= text.w / 2;
	text.y -= text.h / 2;
		
	this.layers.push(text);	
	
	
	this.showTextarea(text);
	
	this.DoChange('layer');
}

PhotoEditor.prototype.InitResource = function(obj,type){
	
	obj.element.addEventListener("click",function()
		{
		
		
			if (obj.isLoad)	
			{	
		
				switch(type) {
					case 'img':
					if (this.selection && this.selection.cell)
					this.selection.cell.setImg(obj.img);
					break;
		
					case 'background':
					this.setBackground(obj.img); 
					break;
	
					case 'smile':
					var rect_smile = obj.element.getBoundingClientRect();
					var center_smile = {
						x : rect_smile.left + rect_smile.width / 2, y : rect_smile.top + rect_smile.height / 2
					}
					
					var rect_canvas = this.canvas.getBoundingClientRect();
					
					var center_canvas = {
						x : rect_canvas.left + rect_canvas.width / 2, y : rect_canvas.top + rect_canvas.height / 2
					}
					
					
					Animation_smilemoved(obj.img.original.src,center_smile,center_canvas,500);
					setTimeout(function(){this.AddSmile(obj.img,0.5,0.5); }.bind(this),500);
					
					break;
				}	
			}
	
		
		}.bind(this));
	
	
	DragAndDrop_img(obj,type);
	
}

