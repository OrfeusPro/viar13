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
    
	function mouseup(e)
	{
		document.body.removeChild(n);
		document.removeEventListener("mousemove", mousemove,true);	
		document.removeEventListener("mouseup", mouseup,true);
		if (DragAndDrop.onmouseup)DragAndDrop.onmouseup(e);
		e.stopImmediatePropagation();
	}
 
 
	document.addEventListener("mousemove", mousemove,true);
	document.addEventListener("mouseup", mouseup,true);	
 
}


function DragAndDrop_img(obj,type)
{
	
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

			function remove()
			{
				document.removeEventListener("mousemove", mousemove);	
				document.removeEventListener("mouseup", remove);		
			}

		
			document.addEventListener("mousemove", mousemove);
			document.addEventListener("mouseup", remove);
				
		}
		
		return false;
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



var PhotoEditor = function(canvas)
{
	var self = this;	
	this.between = 0.01;
	this.radius = 5;
	this.counter = 0;
	this.grid = [];	
	this.is_drawing = false;
	this.canvas = canvas;
	this.ctx = canvas.getContext('2d');	
	this.history = [];
	this.history_pos = 0;
	this.layers = [];
	
	
	this.Background_offset_x = 0.0;
	this.Background_offset_y = 0.0;
	
	this.ColorCell = '#6a9586';
	
	this.canvas.addEventListener("mousemove", function(){self.setCursor("default");});


	this.initAction_selection();//инициализируем выделения
	this.initAction_LayerOperations();
	this.initAction_cellresize();//инициализируем растягивания
	this.initAction_cellAdd();//инициализируем добавления
	
	this.initAction_PhotoOperations();//инициализируем,операции с фото, вытащить фото с ячейки,двигать внутри
	this.initAction_OpenBackground();
	
	this.initAction_smileAdd();
	
	
	this.new();
	this.add_history();
	
	
}


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
		
		
			
		
		var p = self.ClientPosToSinglePos({x:e.clientX,y:e.clientY});
			
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
	   
	   
			e.stopImmediatePropagation();
			
		}
	
		
		
		
	}
	

	
	function mousemove(e)
	{
		
		
		var p = self.ClientPosToSinglePos({x:e.clientX,y:e.clientY});
		
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
	
	
	document.addEventListener("mousemove", mousemove);
	document.addEventListener("mouseup", mouseup);
	
	
	this.canvas.addEventListener("mousedown", mousedown);
}



PhotoEditor.prototype.getCellAddToPoint = function(p)
{
	var cell = this.getCellByPoint(p);

	var offset = 0.2;

	if (cell)
	{
		if (cell.p1.x < p.x && cell.p2.x > p.x &&
			cell.p1.y < p.y && cell.p1.y + cell.h() * offset  > p.y)
		{
			return {side:'top',cell:cell,rect:{p1:{x:cell.p1.x,y:cell.p1.y},p2:{x:cell.p2.x,y:cell.p1.y + cell.h() * offset}}};
		}else
		if (cell.p1.x < p.x && cell.p2.x > p.x &&
			cell.p2.y - cell.h() * offset  < p.y && cell.p2.y > p.y)
		{
			return {side:'bottom',cell:cell,rect:{p1:{x:cell.p1.x,y:cell.p2.y -cell.h() * offset},p2:{x:cell.p2.x,y:cell.p2.y}}};
		}else
		if (cell.p1.y < p.y && cell.p2.y > p.y &&
			cell.p1.x < p.x && cell.p1.x + cell.w() * offset  > p.x)
		{
			return {side:'left',cell:cell,rect:{p1:{y:cell.p1.y,x:cell.p1.x},p2:{y:cell.p2.y,x:cell.p1.x + cell.w() * offset}}};

		}else
		if (cell.p1.y < p.y && cell.p2.y > p.y &&
			cell.p2.x - cell.w() * offset  < p.x && cell.p2.x > p.x)
		{
			return {side:'right',cell:cell,rect:{p1:{y:cell.p1.y,x:cell.p2.x -cell.w() * offset},p2:{y:cell.p2.y,x:cell.p2.x}}};
		}else return {side:'center',cell:cell};	
	}	
}



PhotoEditor.prototype.initAction_cellAdd = function()
{
	var self = this;	
	
	var cell_to_add;	
	
	
	var old_onmousemove = DragAndDrop.onmousemove;
	DragAndDrop.onmousemove = function(e)
	{
		if(DragAndDrop.type =="img")
		{
			var p = self.ClientPosToSinglePos({x:e.clientX,y:e.clientY});
			cell_to_add = self.getCellAddToPoint(p);
		}
		if (old_onmousemove)old_onmousemove(e);	
	}	
	
	var old_onmouseup = DragAndDrop.onmouseup; 
	DragAndDrop.onmouseup = function(e)	 
	{
		if (DragAndDrop.type =="img" && cell_to_add)
		{
			var target = cell_to_add.cell;
			
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
				
			target.setImg(DragAndDrop.img);	
			
			self.selection = new Selection(self,target);
			self.DoChange("selection");	
		
			cell_to_add = undefined;
			
			
		}
			
		if (old_onmouseup)old_onmouseup(e);
	}
	
	
	
	var old_onEndScene = this.onEndScene;
	
	this.onEndScene = function()
	{
		if (cell_to_add && cell_to_add.side !== "center")
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
	var old_onmouseup = DragAndDrop.onmouseup; 
	DragAndDrop.onmouseup = function(e)	 
	{
		if (DragAndDrop.type =="background")	
		{
			var p = self.ClientPosToSinglePos({x:e.clientX,y:e.clientY});
		
			if (p.x > 0 && p.x < 1 && p.y > 0 && p.y < 1)
			{
			
				self.setBackground(DragAndDrop.img);		
			    
			}
		}	
		if (old_onmouseup)old_onmouseup(e);	
	}	
}


PhotoEditor.prototype.initAction_smileAdd = function()
{
	var self = this;	
	var old_onmouseup = DragAndDrop.onmouseup; 
	DragAndDrop.onmouseup = function(e)	 
	{
		if (DragAndDrop.type =="smile")	
		{
			var p = self.ClientPosToSinglePos({x:e.clientX,y:e.clientY});
		
			if (p.x > 0 && p.x < 1 && p.y > 0 && p.y < 1)
			{
				self.AddSmile(DragAndDrop.img,p.x,p.y);
			}
			
			
			
		}	
		if (old_onmouseup)old_onmouseup(e);	
	}	
}

PhotoEditor.prototype.initAction_PhotoOperations = function()
{
	var self = this;
	
		
	
	function mousedown(e)
	{
		var p1 = self.ClientPosToSinglePos({x:e.clientX,y:e.clientY});
		var cell1 = self.getCellByPoint(p1);	
		if (cell1.resource)
		{
			var old_offset_x = cell1.resource.offset_x;
			var old_offset_y = cell1.resource.offset_y;
		}
	

		function mousemove(e)
		{
			self.setCursor("move");
			
			var p2 = self.ClientPosToSinglePos({x:e.clientX,y:e.clientY});
			var cell2 = self.getCellByPoint(p2);	
			
			if (cell1 !== cell2)
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
			e.stopImmediatePropagation();
		}

		function remove()
		{
		
			if (cell1.resource)
			{
				self.DoChange("cell");
			}	
			document.removeEventListener("mouseup", remove,true);
			document.removeEventListener("mousemove", mousemove,true);	
		}

		if (cell1.resource)
		{
			document.addEventListener("mousemove", mousemove,true);
			document.addEventListener("mouseup", remove,true);		
		}
      	
	
	
	}	
	
	
	function wheel(e)
	{
		var p = self.ClientPosToSinglePos({x:e.clientX,y:e.clientY});
		var cell = self.getCellByPoint(p);	
		if (cell && cell.resource)
		{
			var delta = e.deltaY || e.detail || e.wheelDelta;	
			cell.photo_zoom(cell.resource.zoom * (delta > 0?0.8:1.2));
			e.preventDefault();
			self.DoChange("cell");
		}	
	}
	
	
	
	this.canvas.addEventListener("wheel", wheel);
	

	this.canvas.addEventListener("mousedown", mousedown);	
}




PhotoEditor.prototype.initAction_selection = function()
{
	var self = this;
	function mousedown(e) 	
	{  
		var p = self.ClientPosToSinglePos({x:e.clientX,y:e.clientY});
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
			var p = self.ClientPosToSinglePos({x:e.clientX,y:e.clientY});
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


	
	self.canvas.addEventListener("mousedown", mousedown);
	self.canvas.addEventListener("mouseup", mouseup);
}



PhotoEditor.prototype.initAction_LayerOperations = function()
{
	var self = this;
	
	function mousedown(e) 	
	{  	
		var p1 = self.ClientPosToSinglePos({x:e.clientX,y:e.clientY});
		var text = self.GetLayerByPoint(p1);
		
		function translate()
		{
			function mousemove(e)
			{
				var p2 = self.ClientPosToSinglePos({x:e.clientX,y:e.clientY});	
				
				
				
				text.x = old_offset_x - (p1.x - p2.x);
				text.y = old_offset_y - (p1.y - p2.y);
	
				text.x  = Math.max(text.x,-text.w/2);
	
				text.y  = Math.max(text.y,-text.h/2);
	
				text.x  = Math.min(text.x,1-text.w/2);
	
				text.y  = Math.min(text.y,1-text.h/2);
				
				self.setCursor("move");
	
				e.stopImmediatePropagation();	
			}	
		
	
	
			function remove()
			{
				
				self.DoChange('layer');
				document.removeEventListener("mouseup", remove,true);
				document.removeEventListener("mousemove", mousemove,true);	
			}
		
		
			var old_offset_x = text.x;
			var old_offset_y = text.y;
			
			document.addEventListener("mousemove", mousemove,true);
			document.addEventListener("mouseup", remove,true);
				
		
		}
	
	
	
		function resize()
		{
			
			function mousemove(e)
			{
				var minsize = 0.01; // минимальный размер
				
				var p2 = self.ClientPosToSinglePos({x:e.clientX,y:e.clientY});
				
				text.w = Math.max(minsize, old_w - (p1.x - p2.x));
				
				text.h = (old_h / old_w) *  text.w;
				
				self.setCursor("nwse-resize");	
				e.stopImmediatePropagation();
			}
			
			
			
			function remove()
			{
				
				self.DoChange('layer');
				document.removeEventListener("mouseup", remove,true);
				document.removeEventListener("mousemove", mousemove,true);	
			}
			
			var old_w = text.w;
			var old_h = text.h;
			document.addEventListener("mousemove", mousemove,true);
			document.addEventListener("mouseup", remove,true);
			
			
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
			
			
			
			
			e.stopImmediatePropagation();
		}
	}
	
	
	this.canvas.addEventListener("mousedown", mousedown);	
	
	
	function mousemove(e)
	{
		var p = self.ClientPosToSinglePos({x:e.clientX,y:e.clientY});
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
	
	
	this.canvas.addEventListener("mousemove",mousemove);		
		
}

PhotoEditor.prototype.drawBackground = function()
{
	this.ctx.save();
	
	var sx =  (1 / (1-(this.Background_offset_x *2)));
	var sy =  (1 / (1-(this.Background_offset_y *2)));
	
	this.ctx.translate(-(this.canvas.width * (sx - 1))/2,-(this.canvas.height * (sy - 1))/2);
	
	this.ctx.scale(sx,sy);
		
	this.ctx.fillStyle = "#ffffff";	
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




PhotoEditor.prototype.render = function()
{
	
	if (this.ctx !== undefined)	
	{
		
		
		this.drawBackground();
		
		if (this.onBeginScene)this.onBeginScene();
		
		this.drawGrid();
		
		this.drawLayers();
		
		
		
		
		if (this.onEndScene)this.onEndScene();
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



PhotoEditor.prototype.out = function(width,height)
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
	
	this.ctx.translate(this.canvas.width * this.Background_offset_x,this.canvas.height * this.Background_offset_y);
	this.ctx.scale(1-(this.Background_offset_x*2),1-(this.Background_offset_y*2));
	
	
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



