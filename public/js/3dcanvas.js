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


Cell.prototype.h = function() //Ð²Ñ‹ÑÐ¾Ñ‚Ð°
{
	return this.p2.y - this.p1.y;	
}

Cell.prototype.w = function()//ÑˆÐ¸Ñ€Ð¸Ð½Ð°
{
	return this.p2.x - this.p1.x;		
}

Cell.prototype.anim = function()//ÐºÐ¾Ð¾Ñ€Ð´Ð¸Ð½Ð°Ñ‚Ñ‹ Ð¿Ñ€Ð¸ Ð¸ÑÐ¿Ð¾Ð»ÑŒÐ·Ð¾Ð²Ð°Ð½Ð¸ Ð°Ð½Ð¸Ð¼Ð°Ñ†Ð¸Ð¸
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



Cell.prototype.BeginAnimation = function()//Ð¿ÐµÑ€ÐµÐ¼ÐµÑÑ‚Ð¸Ñ‚ÑŒ Ð°Ð½Ð¸Ð¼Ð°Ñ†Ð¸ÑŽ Ð² Ð½Ð°Ñ‡Ð°Ð»Ð¾
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
		n.p1.x = this.p1.x;//Ð½Ð°Ñ‡Ð°Ð»Ð¾ Ð´Ð»Ñ Ð°Ð½Ð¸Ð¼Ð°Ñ†Ð¸Ð¸
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
		n.p1.y = this.p1.y;//Ð½Ð°Ñ‡Ð°Ð»Ð¾ Ð´Ð»Ñ Ð°Ð½Ð¸Ð¼Ð°Ñ†Ð¸Ð¸ 
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

Cell.prototype.RectOut = function()//Ð¾Ð±Ð»Ð°ÑÑ‚ÑŒ Ð²Ñ‹Ð²Ð¾Ð´Ð°
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






var Layer = function(){}
	


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
		return {x:this.element.x,y:this.element.y,w:this.element.w,h:this.element.h};	
	}	
}



var PhotoEditor = function(options)
{
	
	this.optionsDefault = {
		isMouseEvents:true,
		DragAndDropEvents:true,
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
	
	this.addEventListener("mousemove", function(){self.setCursor("default");});


	if (this.options.isAct_selection) this.initAction_selection();//Ð¸Ð½Ð¸Ñ†Ð¸Ð°Ð»Ð¸Ð·Ð¸Ñ€ÑƒÐµÐ¼ Ð²Ñ‹Ð´ÐµÐ»ÐµÐ½Ð¸Ñ
	if (this.options.isAct_LayerOperations) this.initAction_LayerOperations();
	if (this.options.isAct_cellresize) this.initAction_cellresize();//Ð¸Ð½Ð¸Ñ†Ð¸Ð°Ð»Ð¸Ð·Ð¸Ñ€ÑƒÐµÐ¼ Ñ€Ð°ÑÑ‚ÑÐ³Ð¸Ð²Ð°Ð½Ð¸Ñ
	if (this.options.isAct_cellAdd) this.initAction_cellAdd();//Ð¸Ð½Ð¸Ñ†Ð¸Ð°Ð»Ð¸Ð·Ð¸Ñ€ÑƒÐµÐ¼ Ð´Ð¾Ð±Ð°Ð²Ð»ÐµÐ½Ð¸Ñ
	if (this.options.isAct_PhotoOperations) this.initAction_PhotoOperations();//Ð¸Ð½Ð¸Ñ†Ð¸Ð°Ð»Ð¸Ð·Ð¸Ñ€ÑƒÐµÐ¼,Ð¾Ð¿ÐµÑ€Ð°Ñ†Ð¸Ð¸ Ñ Ñ„Ð¾Ñ‚Ð¾, Ð²Ñ‹Ñ‚Ð°Ñ‰Ð¸Ñ‚ÑŒ Ñ„Ð¾Ñ‚Ð¾ Ñ ÑÑ‡ÐµÐ¹ÐºÐ¸,Ð´Ð²Ð¸Ð³Ð°Ñ‚ÑŒ Ð²Ð½ÑƒÑ‚Ñ€Ð¸
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
	 	
		this.layers.push(Object.assign(new Layer(),layer));
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
	
	
	
	if (point.x > mn && point.y > mn && point.x < 1-mn && point.y < 1-mn)//Ð½Ðµ Ñ€Ð°Ð·Ñ€ÐµÑˆÐ°ÐµÑ‚ÑÑ Ð±Ñ€Ð°Ñ‚ÑŒ Ñ€Ð¾Ð´Ð¸Ñ‚ÐµÐ»Ñ Ñ ÐºÑ€Ð°ÑŽ 
	{
		
		var line,cell;
		
		this.grid.forEach(function(cell2){ //Ð¸Ñ‰Ð¸Ð¼ Ð»Ð¸Ð½Ð¸ÑŽ Ñƒ ÑÐ°Ð¼Ð¾Ð¹ Ð´Ð»Ð¸Ð½Ð¾Ð¹ ÑÑ‡ÐµÐ¹ÐºÐ¸ 
			
				if (cell2.p1.x <= point.x && cell2.p2.x >= point.x)//Ð½Ð°Ñ…Ð¾Ð´Ð¸Ñ‚ÑÑ Ð»Ð¸ Ñ‚Ð¾Ñ‡ÐºÐ° Ð²Ð½ÑƒÑ‚Ñ€Ð¸ Ð¿Ð¾ x
				{
				
				
					if (!line || ((cell2.p2.x - cell2.p1.x) > (line.p2.x - line.p1.x)))//ÐµÑÐ»Ð¸ Ð»Ð¸Ð½Ð¸Ð¸ Ð½Ðµ ÑÑƒÑ‰ÐµÑÑ‚Ð²ÑƒÐµÑ‚ Ð¸Ð»Ð¸ Ð½Ð¾Ð²Ð°Ñ Ð´Ð»Ð¸Ð½Ð½ÐµÐµ ÑÑ‚Ð°Ñ€Ð¾Ð¹
					{
						if (Math.abs(cell2.p1.y - point.y) < mn)//Ð¿Ñ€Ð¾Ð²ÐµÑ€Ð¸Ñ‚ÑŒ Ð´Ð¾ÑÑ‚Ð°Ñ‚Ð¾Ñ‡Ð½Ð¾ Ð»Ð¸ Ð±Ð»Ð¸Ð·ÐºÐ¾ Ñ‚Ð¾Ñ‡ÐºÐ° Ðº Ð»Ð¸Ð½Ð¸Ð¸ 
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
					var cell2 = this.grid[key];	//Ð¿Ð¾Ð»ÑƒÑ‡Ð°ÐµÐ¼ Ð²ÑÑŽ Ð´Ð»Ð¸Ð½Ñƒ Ð»Ð¸Ð½Ð¸Ð¸
					
					
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
			self.ctx.strokeRect(rect.x * self.canvas.width, rect.y*self.canvas.height, rect.w*self.canvas.width, rect.h*self.canvas.height);
			
	
			self.ctx.restore();	
		}
		if (old_onEndScene)	old_onEndScene();
	}


	
	this.addEventListener("mousedown", mousedown);
	this.addEventListener("mouseup", mouseup);
}



PhotoEditor.prototype.initAction_LayerOperations = function()
{
	var self = this;
	
	function mousedown(e) 	
	{  	
		var p1 = e.SinglePos;
		var text = self.GetLayerByPoint(p1);
		
		function translate()
		{
			function mousemove(e)
			{
				var p2 = e.SinglePos;	
				
				
				
				text.x = old_offset_x - (p1.x - p2.x);
				text.y = old_offset_y - (p1.y - p2.y);
	
				text.x  = Math.max(text.x,-text.w/2);
	
				text.y  = Math.max(text.y,-text.h/2);
	
				text.x  = Math.min(text.x,1-text.w/2);
	
				text.y  = Math.min(text.y,1-text.h/2);
				
				self.setCursor("move");
	
				return true;
			}	
		
	
	
			function remove()
			{
				
				self.DoChange('layer');
				self.removeEventListener("mouseup", remove,true);
				self.removeEventListener("mousemove", mousemove,true);	
			}
		
		
			var old_offset_x = text.x;
			var old_offset_y = text.y;
			
			self.addEventListener("mousemove", mousemove,true);
			self.addEventListener("mouseup", remove,true);
				
		
		}
	
	
	
		function resize()
		{
			
			function mousemove(e)
			{
				var minsize = 0.01; // Ð¼Ð¸Ð½Ð¸Ð¼Ð°Ð»ÑŒÐ½Ñ‹Ð¹ Ñ€Ð°Ð·Ð¼ÐµÑ€
				
				var p2 = e.SinglePos;
				
				text.w = Math.max(minsize, old_w - (p1.x - p2.x));
				
				text.h = (old_h / old_w) *  text.w;
				
				self.setCursor("nwse-resize");	
				return true;
			}
			
			
			
			function remove()
			{
				
				self.DoChange('layer');
				self.removeEventListener("mouseup", remove,true);
				self.removeEventListener("mousemove", mousemove,true);	
			}
			
			var old_w = text.w;
			var old_h = text.h;
			self.addEventListener("mousemove", mousemove,true);
			self.addEventListener("mouseup", remove,true);
			
			
		}
		
		if (text)
		{
			var mn = (1 / Math.max(self.canvas.width,self.canvas.height)) * 10;
			
			if (text.x+text.w - mn < p1.x && text.y+text.h - mn < p1.y)
			{
				resize();	
			}else
			{
				translate();	
			}
			
			
			
			
			return true;
		}
	}
	
	
	this.addEventListener("mousedown", mousedown);	
	
	
	function mousemove(e)
	{
		var p = e.SinglePos;
		var text = self.GetLayerByPoint(p);	
		if (self.selection && self.selection.element == text)
		{
	 	
			var mn = (1 / Math.max(self.canvas.width,self.canvas.height)) * 10;
			if (text.x+text.w - mn < p.x && text.y+text.h - mn < p.y)
			{
			
				self.setCursor("nwse-resize");	
			}else
			{
			
				self.setCursor("move");
			}
		}
	}
	
	
	this.addEventListener("mousemove",mousemove);		
		
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
			
			this.ctx.translate(imgW/2,imgH/2);//Ð¿Ð¾Ð²Ð¾Ñ€Ð¾Ñ‚
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


		
		this.ctx.save();	

		this.ctx.translate(this.canvas.width * layer.x , this.canvas.height * layer.y);

		
		
		this.ctx.scale((layer.w * this.canvas.width)/layer.src_w,(layer.h * this.canvas.height) / layer.src_h);

		if (layer.type == "text")
		{
			this.ctx.fillStyle = layer.color;
			this.ctx.font = layer.size + "px " + layer.font;	
			this.ctx.textBaseline = "top";	
			this.ctx.fillText(layer.text,0,0);	
		}else 
		if (layer.type == "smile")   
		{
			this.ctx.drawImage(layer.img.original, 0, 0);
		}
		
		this.ctx.restore();		
		
	}	
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
				try {
				self.render();	
				} catch(error) {}
				
				
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
			var mn_x = Math.min(layer.x+layer.w,layer.x);
			var mx_x = Math.max(layer.x+layer.w,layer.x);
			var mn_y = Math.min(layer.y+layer.h,layer.y);
			var mx_y = Math.max(layer.y+layer.h,layer.y);	
		
			if (mn_x <= point.x && mx_x >= point.x &&
				mn_y <= point.y && mx_y >= point.y)return true;});		
	
	
}


PhotoEditor.prototype.AddSmile = function(img,x,y)
{
	var self = this;	
	
		
	var smile  = new Layer();
	smile.x = x;
	smile.y = y;
	smile.type = "smile";
	smile.img = img;
		
		
	var mx = Math.max(img.original.width,img.original.height);
		
		
	smile.w = ((50 / mx)*img.original.width) / self.canvas.width;
	smile.h = ((50 / mx)*img.original.height) / self.canvas.height;
		
		
	smile.x -= smile.w / 2;
	smile.y -= smile.h / 2;
		
		
	
	smile.src_w = img.original.width;
	smile.src_h = img.original.height;
    
	self.layers.push(smile);
		
	self.DoChange('layer');
    
	
}

PhotoEditor.prototype.AddText = function(value,font,size,color)
{

	var text = new Layer();

	text.x = 0.5;
	text.y = 0.5;
	
	text.type = "text"; 
	text.text = value;
	text.font = font;
	text.color = color;
	text.size = size;
	
	
	

	this.ctx.save();

	this.ctx.fillStyle = text.color;

	this.ctx.font = text.size + "px " + text.font;	

	var m = this.ctx.measureText(text.text);

	text.w = m.width / this.canvas.width;
	text.h = size / this.canvas.height;
	
	text.src_w = m.width;
	text.src_h = size;
	

	text.x -= text.w / 2;
	text.y -= text.h / 2;

	this.ctx.restore();		
	this.layers.push(text);	
	
	this.DoChange('layer');
}






function Canvas3D(canvas_ViewPort)
{
	PhotoEditor.call(this,{canvas:document.createElement('canvas'),DragAndDropEvents:false,isCellDiv:false,isAct_cellresize:false,isOutPhoto:false});
	
	this.canvas_ViewPort = canvas_ViewPort; 
	this.scene = new THREE.Scene();
	this.camera = new THREE.PerspectiveCamera( 35, this.canvas_ViewPort.width / this.canvas_ViewPort.height, 0.1, 1000 );
	this.camera.position.z = 2.0//2;
	this.camera.position.y = 0.15//0.133;
	
	this.camera.lookAt(new THREE.Vector3(0, 0, 0));	
	
		
	this.renderer = new THREE.WebGLRenderer({ antialias: true,canvas:canvas_ViewPort,preserveDrawingBuffer: true });
	this.renderer.setSize(this.canvas_ViewPort.width,this.canvas_ViewPort.height);
				
	//this.renderer.shadowMap.enabled = true;
	//this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
	this.renderer.setClearColor( 0xffffff, 1);
	this.renderer.setPixelRatio(window.devicePixelRatio);
	//this.renderer.setPixelRatio(2.5);
	this.light = new THREE.DirectionalLight( 0xffffff, 1.8 );
	this.light.position.set( -1, 6, 5 ); 			
	//this.light.castShadow = true;            
	this.scene.add(this.light);
	
			
	this.light.shadow.camera.left = -40;
	this.light.shadow.camera.bottom = -40;
	this.light.shadow.camera.right = 20;
	this.light.shadow.camera.top = 20;
	
	
	this.raycaster = new THREE.Raycaster();
				
	this.scene.background = 0xffffff;
	
	this.canvas.width = 500;
	this.canvas.height = 500;
	
	this.texture = new THREE.CanvasTexture(this.canvas);
	this.texture.anisotropy = this.renderer.getMaxAnisotropy();
	
	
	
	this.InitEvents();		
	
	//this.InitRotation();	
				
	var WindowResize = function()
	{
		
      this.setSize(this.canvas_ViewPort.height,this.canvas_ViewPort.width);  

	}.bind(this);

	
	window.addEventListener( 'resize', WindowResize, false);
	
	WindowResize();
}


Canvas3D.prototype = Object.assign({},PhotoEditor.prototype); 

Canvas3D.prototype.setCursor = function(cursor)
{
	this.canvas_ViewPort.style.cursor = cursor;	
}

Canvas3D.prototype.BottomPanel = function(callback)
{
	
	
	var src = 'data:image/png;base64,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'
	
	
	const loader = new THREE.TextureLoader();
  loader.load(src, function(texture){
 const geometry = new THREE.PlaneGeometry(1, 3);
	const material = new THREE.MeshBasicMaterial({map: texture});
	this.ShadowPlane = new THREE.Mesh(geometry,material);
	
	var prot = new THREE.Euler(Deg2Rad(-100), Deg2Rad(35), 0, 'YXZ');
	var ppos = new THREE.Vector3(0, 1.35, -0.5);
	ppos.applyEuler(prot);
	
	this.ShadowPlane.position.copy(ppos);
	this.ShadowPlane.rotation.copy(prot);
	this.scene.add(this.ShadowPlane);
	callback();
}.bind(this));
	
	
	
	
	
}

Canvas3D.prototype.UpdateBottomPanel = function(size_width)
{
	this.ShadowPlane.scale.x = size_width + size_width*0.15;
	
}
Canvas3D.prototype.updata2DSize = function()
{
	//var ratio =  Math.min(this.canvas_ViewPort.height / this.size_height, this.canvas_ViewPort.width  /  this.size_width);  
	//this.canvas.height = this.size_height * (ratio * 0.8);
	//this.canvas.width = this.size_width * (ratio * 0.8);
	var mx = 1200;
	var ratio = Math.min(mx/this.size_height,mx/this.size_width);
	this.canvas.height = this.size_height * ratio;
	this.canvas.width = this.size_width * ratio;
}

Canvas3D.prototype.setSize = function(h,w)
{
	this.renderer.setSize(w,h);
	this.camera.aspect = w / h;
	this.camera.updateProjectionMatrix();	
}

Canvas3D.prototype.setSizeBox = function(size_height,size_width,size_depth)
{	
	this.size_width = size_width;
	this.size_height = size_height;	
	this.size_depth = size_depth;	
	this.offset_cell_y = (1 / this.size_height) * this.size_depth;
	this.offset_cell_x = (1 / this.size_width) * this.size_depth;
	this.Box(this.size_width,this.size_height,this.size_depth);	
	if (!this.ShadowPlane){this.BottomPanel(function(){this.UpdateBottomPanel(size_width);}.bind(this))}else{
	this.UpdateBottomPanel(size_width);	
	}
	
	this.updata2DSize();
}			


function Deg2Rad(in_deg){
	return in_deg * (Math.PI/180);
}


Canvas3D.prototype.Box = function(size_width,size_height,size_depth)
{
	if (this.box){
		this.scene.remove(this.box);
		this.scene.remove(this.mousebox);	
		
	}
	
	if (size_height < size_width)
	{
		var h = size_height / size_width;
		var w = 1;	
	}else
	{
		h = 1;
		w = size_width / size_height;		
	}
				
	const z = (h / size_height) *  size_depth;
				
				
	
	
				
	var offset_x = (1 / (w+z)) * z;
	var offset_y = (1 / (h+z)) * z;
	
	var offset_1_x = this.is_box_offset_1_x ? 0 : offset_x;
	var offset_1_y = this.is_box_offset_1_y ? 0 : offset_y;
	
	var offset_2_x = this.is_box_offset_2_x ? 0 : offset_x;
	var offset_2_y = this.is_box_offset_2_y ? 0 : offset_y;
	
			
	const geometry = new THREE.Geometry();
	geometry.vertices.push(
		new THREE.Vector3(-w/2, -h/2,  z/2),  // 0
		new THREE.Vector3( w/2, -h/2,  z/2),  // 1
		new THREE.Vector3(-w/2,  h/2,  z/2),  // 2
		new THREE.Vector3( w/2,  h/2,  z/2),  // 3
		new THREE.Vector3(-w/2, -h/2, -z/2),  // 4
		new THREE.Vector3( w/2, -h/2, -z/2),  // 5
		new THREE.Vector3(-w/2,  h/2, -z/2),  // 6
		new THREE.Vector3( w/2,  h/2, -z/2),  // 7
	);

	geometry.faces.push(
		new THREE.Face3(0, 3, 2),
		new THREE.Face3(0, 1, 3),
		// right
		new THREE.Face3(1, 7, 3),
		new THREE.Face3(1, 5, 7),
		// left
		new THREE.Face3(4, 2, 6),
		new THREE.Face3(4, 0, 2),
		// top
		new THREE.Face3(2, 7, 6),
		new THREE.Face3(2, 3, 7),
		// bottom
		new THREE.Face3(4, 1, 0),
		new THREE.Face3(4, 5, 1),
	);
				
	geometry.faceVertexUvs[0] = [];
			
	geometry.faceVertexUvs[0].push([new THREE.Vector2(offset_1_x,1 - offset_2_y),new THREE.Vector2(1 - offset_2_x,offset_1_y),new THREE.Vector2(offset_1_x ,offset_1_y)]); 	
			
	geometry.faceVertexUvs[0].push([new THREE.Vector2(offset_1_x,1 - offset_2_y),new THREE.Vector2(1 - offset_2_x,1 - offset_2_y),new THREE.Vector2(1 - offset_2_x ,offset_1_y)]);  
     
	geometry.faceVertexUvs[0].push([new THREE.Vector2(1 - offset_2_x,1 - offset_2_y),new THREE.Vector2(1,offset_1_y),new THREE.Vector2(1 - offset_2_x ,offset_1_y)]);  	
     
	geometry.faceVertexUvs[0].push([new THREE.Vector2(1 - offset_2_x,1 - offset_2_y),new THREE.Vector2(1,1 - offset_2_y),new THREE.Vector2(1,offset_1_y)]);  
     
	geometry.faceVertexUvs[0].push([new THREE.Vector2(0,1 - offset_2_y),new THREE.Vector2(offset_1_x ,offset_1_y),new THREE.Vector2(0 ,offset_1_y)]);  
     
	geometry.faceVertexUvs[0].push([new THREE.Vector2(0,1 - offset_2_y),new THREE.Vector2(offset_1_x,1 - offset_2_y),new THREE.Vector2(offset_1_x ,offset_1_y)]);  
     
	geometry.faceVertexUvs[0].push([new THREE.Vector2(offset_1_x ,offset_1_y),new THREE.Vector2(1 - offset_2_x,0),new THREE.Vector2(offset_1_x ,0)]); 
     
	geometry.faceVertexUvs[0].push([new THREE.Vector2(offset_1_x ,offset_1_y),new THREE.Vector2(1 - offset_2_x ,offset_1_y),new THREE.Vector2(1 - offset_2_x,0)]); 
	  
	geometry.faceVertexUvs[0].push([new THREE.Vector2(offset_1_x ,1),new THREE.Vector2(1 - offset_2_x,1 - offset_2_y),new THREE.Vector2(offset_1_x,1 - offset_2_y)]); 
     
	geometry.faceVertexUvs[0].push([new THREE.Vector2(offset_1_x ,1),new THREE.Vector2(1 - offset_2_x ,1),new THREE.Vector2(1 - offset_2_x,1 - offset_2_y)]); 
	  
	geometry.uvsNeedUpdate = true;
	            
	            
	geometry.computeFaceNormals();
			
	const material = new THREE.MeshLambertMaterial( {map: this.texture, side: THREE.DoubleSide} );
				
	this.box = new THREE.Mesh(geometry, material);
				
	//this.box.castShadow = true;
	this.box.receiveShadow = false;
				
	this.box.scale.set( 1, -1, 1 )			
	this.box.position.y = (h/2) - 0.5;
	
	this.box.rotation.x = Deg2Rad(-10);
	this.box.rotation.y = Deg2Rad(35);
	this.box.rotation.order = 'YXZ'
	this.scene.add(this.box);
	
}

Canvas3D.prototype.render2D = function()
{

	PhotoEditor.prototype.render.call(this,this.isOffset);
    
	this.texture.needsUpdate = true;	
	
}


Canvas3D.prototype.render3D = function()
{
	this.renderer.render(this.scene, this.camera);	
}
	
			
			
Canvas3D.prototype.render = function()
{
	this.render2D();
	this.render3D();
}

Canvas3D.prototype.PosTo3dBoxPos = function(SinglePos)
{
	var raycaster = new THREE.Raycaster();	
	var point = new THREE.Vector2();
	point.y = -(SinglePos.y * 2 - 1);
	point.x = SinglePos.x * 2 - 1;
    
	raycaster.setFromCamera(point,this.camera);
    
	var intersect = raycaster.intersectObject(this.box);
    
	if (intersect.length > 0)
	{
		var sx =  (1 / (1-(this.offset_cell_x *2)));
	    var sy =  (1 / (1-(this.offset_cell_y *2)));
	    var uv_x = intersect[0].uv.x;
		var uv_y = 1-intersect[0].uv.y;	
		
		var x = (uv_x * sx) - (this.offset_cell_x * sx);
		var y = (uv_y * sy) - (this.offset_cell_y * sx);
		
		return {x:x,y:y};
	}	
}


Canvas3D.prototype.InitRotation = function()
{
var trigger = new Events();	
new MouseEvents(this.canvas_ViewPort,trigger).on();
trigger.addEventListener("mousedown", function(e){
	
	var old_r_y = this.box.rotation.y;
	var old_r_x = this.box.rotation.x;
	var start = e.SinglePos;
	  
    var raycaster = new THREE.Raycaster();
	    raycaster.setFromCamera(new THREE.Vector2(e.SinglePos.x * 2 - 1,-(e.SinglePos.y * 2 - 1)),this.camera);
    
	if (raycaster.intersectObject(this.box).length == 0)
	{
		
		
		let mousemove = function(e){
		
	var move = e.SinglePos;
	
		this.box.rotation.x  = old_r_x - ((start.y - move.y) * 3);
		this.box.rotation.y  = old_r_y - ((start.x - move.x) * 3);
		
		if (this.box.rotation.y < -1.0)this.box.rotation.y = -1.0;
	    if (this.box.rotation.y > 1.0)this.box.rotation.y = 1.0;
	    
	    if (this.box.rotation.x < -0.8)this.box.rotation.x = -0.8;
	    if (this.box.rotation.x > 0.5)this.box.rotation.x = 0.5;
	    
	    
	    
	    
	    //console.log(this.box.rotation.y,this.box.rotation.x);
	
		}.bind(this);
		
		
	var mouseup = function()
	{
	trigger.removeEventListener("mousemove", mousemove);		
	trigger.removeEventListener("mouseup", mouseup);	
	}	
		
	trigger.addEventListener("mousemove", mousemove);	
	
	trigger.addEventListener("mouseup", mouseup);
	
	}
	
	
	
}.bind(this));
	
	
}



Canvas3D.prototype.InitEvents = function()
{
	var trigger = new Events();

	var old_SinglePos;

	new MouseEvents(this.canvas_ViewPort,trigger).on();
	
	DragAndDropEvents(this.canvas_ViewPort,trigger);
	

	trigger.addEventListener("mousedown", function(e){
			var SinglePos = this.PosTo3dBoxPos(e.SinglePos);
			if (SinglePos)
			{
				var data = Object.assign(e,{SinglePos:SinglePos});
	
				old_SinglePos = SinglePos;
				this.dispatch("mousedown",data);
			}
    
		}.bind(this));


	trigger.addEventListener("mousemove", function(e){
			var SinglePos = this.PosTo3dBoxPos(e.SinglePos);
			if (SinglePos)
			{
				var data = Object.assign(e,{SinglePos:SinglePos});;
	
				old_SinglePos = SinglePos;
				
				this.dispatch("mousemove",data);
   	
			}	
	
	
		}.bind(this));



	trigger.addEventListener("mouseup", function(e)
		{
			var data = Object.assign(e,{SinglePos:old_SinglePos});
			this.dispatch("mouseup",data);	
		}.bind(this));
		
		
	trigger.addEventListener("wheel", function(e){
		var SinglePos = this.PosTo3dBoxPos(e.SinglePos);
		if (SinglePos)
		{
			var data = Object.assign(e,{SinglePos:SinglePos});
			return this.dispatch("wheel",data);
   	
		}	
	}.bind(this));	
		


	trigger.addEventListener("DragAndDrop_mousemove", function(e){
			var SinglePos = this.PosTo3dBoxPos(e.SinglePos);
			if (SinglePos)
			{
				var data = Object.assign(e,{SinglePos:SinglePos});
				
					this.dispatch("DragAndDrop_mousemove",data);
   	
			}	
		}.bind(this));

trigger.addEventListener("DragAndDrop_mouseup", function(e){
		var SinglePos = this.PosTo3dBoxPos(e.SinglePos);
		if (SinglePos)
		{
			var data = Object.assign(e,{SinglePos:SinglePos});
			this.dispatch("DragAndDrop_mouseup",data);
   	
		}	
	}.bind(this));
	
}