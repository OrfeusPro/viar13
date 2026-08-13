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
	this.renderer.setClearColor( 0xFFF2EE, 1);
	this.renderer.setPixelRatio(window.devicePixelRatio);
	//this.renderer.setPixelRatio(2.5);
	this.light = new THREE.DirectionalLight( 0xFFF2EE, 1.8 );
	this.light.position.set( -1, 6, 5 ); 			
	//this.light.castShadow = true;            
	this.scene.add(this.light);
	
			
	this.light.shadow.camera.left = -40;
	this.light.shadow.camera.bottom = -40;
	this.light.shadow.camera.right = 20;
	this.light.shadow.camera.top = 20;
	
	
	this.raycaster = new THREE.Raycaster();
				
	this.scene.background = 0xFFF2EE;
	
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