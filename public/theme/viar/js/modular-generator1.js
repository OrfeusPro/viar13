// settings
const modular={
	Panorama: .4,
	Landscape: .75,
	Square: 1,
	Portrait: 1.5,
	minLandscape: .5,
	minSquare: .8,
	minPortrait: 1.1,
	maxW: 3000,

	min: 40,
	max: 360,
	minSize: 20,
	minSpace:.2,
	maxSpace:30,
	space:2,
	dpi: 30, //100,
	deltaH: 40, // (viewport height) - (max canvas height)

};

function mediaChecker(max_min, resolution, width = "width") {
    return window.matchMedia(`(${max_min}-${width}: ${resolution}px)`).matches;
}

function moveControls() {
	if (mediaChecker("max", 1080)) {
		$(".tabs-content").before($(".cs-ds-controls"));
	} else {
		$(".cs-ds-controls").appendTo('.mg-flex')
	}
}
//moveControls();
$(window).on('resize', moveControls);

var lang={
	tooSmall: 'Выбирите картинку не меньше $*$!',
	notSupport: 'Не поддерживаемый формат изображения!',
	notSelected: 'Выберите блок для редактирования!',
	tooSmallH: 'Недостаточная высота блока для разделения',
	tooSmallW: 'Недостаточная ширина блока для разделения'
};

$(function(){

	const $win=$(window);
	const dpcm = modular.dpcm || modular.dpi/2.54;
	const minImgSize = Math.round(modular.min*dpcm);
	const tooSmall = lang.tooSmall.replace(/\$/g, minImgSize);

	const cHistory = new function(){
		var snapshots = [],
			state = {},
			hObj, id,

			clear = (html='')=>html.replace(/\s?(selected|hover)\s?/g, ''),
			resized = ()=>'w,h,s,maxW,src'.split(',').some(key=>options[key]!=state.options?.[key]);

		return hObj={
			step: -1,
			snapshots,
			save() {

				var newState = {},
					html=svgG[0].innerHTML.replace(/rect class[^>]*/g, 'rect');

				if (clear(html)!=clear(state.html)) newState = {html};
				else if (!resized()) return;

				state = newState;
				
				state.options = Object.assign({}, options);
				//console.log(clear(html), clear(last.html))
				snapshots.length = state.step = ++hObj.step;
				snapshots.push(state);
				console.log(snapshots.length, hObj.step, state.options.src);
				calcTotalPrice();
			},
			restore() {
				console.log(snapshots.length, hObj.step);
					state = snapshots[hObj.step],
					{format}=options;
				if (state.html) svgG.html(state.html.replace('hover', ''));
				if (resized()) {
					options=Object.assign({}, state.options);
					pattern.attr('href', options.src);
					wInp.val(options.w);
					setMinMax();
					resizeCanvas();
					if (format!=options.format) setTemplates(true);
				}
				calcTotalPrice();
			},
			undo() {
				if (hObj.step<1) return;
				hObj.step--;
				hObj.restore();
			},
			redo() {
				if (hObj.step>snapshots.length-2) return;
				hObj.step++;
				hObj.restore();
			},
			// get current() {return current},
			// set current(i) {state = snapshots[current=i] || {}},
			get state() {return state},
		}
	} 

	function SVG(tag){
		return document.createElementNS('http://www.w3.org/2000/svg', tag)
	}

	// plugins

	$.fn.setModulesHtml=function(){
		return this.setRectId().each(function(){
			if (this.querySelector('g')) return;
			this.innerHTML+='<g><rect/><rect/><rect/><rect/><rect/><rect/><rect/><rect/></g>'
		})
	}
	$.fn.setMinMax=function(min, max){
		if (min==='' || isNaN(min)) min=this.prop('min');
		if (max==='' || isNaN(max)) max=this.prop('max');
		$('~.changeSm span', this)
		 .eq(0).html(min).end()
		 .eq(1).html(max)
		return this.prop({min: min, max: max});
	}
	$.fn.setBg = function(){
		return this.each(function(){
			if (this.type!='range') return;
			var range=this.max-this.min;
			this.style['background-size']=(this.value-this.min)/range*100+'%'
		})
	}

	$.fn.setRectId=function(){
		return this.each((i, el)=>{
			el.id = 'rect' + idCounter++
		})
	}
	let idCounter = 0;

	const templatesContainer = $('.modular-shapes');
	const mainContainer = $('.modular');

	window.svg0 = $('.canv_cel svg'); console.log(1)

	const pattern = $('image', svg0),
		svgG = $('>g', svg0)
		.on('mouseleave', e=>{$('.selected', svgG).appendTo(svgG)});

	var options={
		actual: 1
	};

	function redraw() {svgG[0].innerHTML+=''}

	function resizeCanvas(save){
		//if (!$('g', svg0).html()) return;
		const {w, h} = options;
		svg0[0].setAttribute('viewBox', '0,0,'+w+','+h);
		$('pattern, image', svg0).attr({width: w, height: h});
		redraw(); // huck for svg redraw
		var rw=$('>rect', svg0)[0].getBoundingClientRect().width;
		hRuler.width(rw);
		options.dpcm=rw/w;
		svg0.css({fontSize: 1/options.dpcm});
		resized=false;
		if (save) cHistory.save(1)
	}

	function setTemplates(restore){
		// templatesContainer.not('[aria-hidden]').hide();
		const format = options.format;

		templatesContainer.html('');
		var w0, h0;
		templates.forEach(function(tpl,i){
			if (tpl.format!=format) return;

			var svg=$('<svg viewBox="0, 0, 100, '+modular[format]*100+'"/>');
			var blocks=tpl.bl[0]?tpl.bl:[tpl.bl];
			var maxW=0, maxH=0;

			if (blocks.find(function(block){
				maxW = Math.max(maxW, +block.x + +block.w);
				maxH = Math.max(maxH, +block.y + +block.h);

				return !('s' in block) || +block.r

			})) return;

			blocks.forEach(block=>{

				var x=+(block.x*100/maxW).toFixed(1)+'%',
					y=+(block.y*100/maxH).toFixed(1)+'%',
					w=+(block.w*100/maxW).toFixed(1)+'%',
					h=+(block.h*100/maxH).toFixed(1)+'%';
				$('<g class="module" style="--x: '+x+'; --y: '+y+'; --w: '+w+'; --h: '+h+'"><rect /></g>')
				.appendTo(svg);
			})

			svg[0].innerHTML+='';

			$('<button />').append(svg)//.prop({title: JSON.stringify(tpl)})
			.click(function(e){
				e.preventDefault();
				svgG[0].innerHTML=svg[0].innerHTML;
				$('g.module', svg0).setModulesHtml();
				cHistory.save();
			}).appendTo(templatesContainer);
		})

		if (restore) return;

		$('button', templatesContainer).eq(0).focus().click();
		$('a',templatesContainer.prev()).trigger('open');
	}

	// load images

	$('input.imageFile').on('change', function(){
		var inp=this;
		var file=this.files[0];

		if (!file)  return;
		if (!file.type.match(/image.*/))  {
			alert(lang.notSupport);
			return;
		}

		var img=new Image();
		img.onload=function(){
			if (!img.width) {
				alert(lang.notSupport);
				return;
			}
			var w=img.width,
				h=img.height,
				maxW=modular.maxW;

			if (w<minImgSize || h<minImgSize) {
				alert(tooSmall);
				return;
			}
			// $(inp).addClass('selected').hide()

			var hw=options.actual=img.height/img.width;
			
			if (w>maxW || h>maxW) {
				URL.revokeObjectURL(img.src);
				var w0=Math.min(maxW, maxW/hw),
					h0=Math.min(maxW, maxW*hw),
					canvas = $('<canvas>').prop({width: w0, height: h0})[0],
					ctx=canvas.getContext('2d');
				ctx.drawImage(img, 0, 0, w0, h0);
				canvas.toBlob(function(blob){
					img.src=URL.createObjectURL(blob)
				})
				return;
			}
			$(inp).parents('.download').children('.img')
			 .css('background-image', 'url("'+img.src+'")')

			pattern.attr('href', img.src);
			options.src=img.src;

			options.maxW=w/dpcm;
			options.maxH=h/dpcm;
			setMinMax();

			options.format = hw>modular.minPortrait ? 'Portrait' :
			hw>modular.minSquare ? 'Square' :
			hw>modular.minLandscape ? 'Landscape' : 'Panorama';

			resizeCanvas();
			setTemplates();
		}
		img.onerror=function(){alert(lang.notSupport)};
		img.src=URL.createObjectURL(file);
		$(this).data('img', img)

	}).on('click', function(e){
		$('input.imageFile').not(this).show();
		if ($(this).hasClass('selected')) {
			e.preventDefault();
			$(this).data('img').onload();
			return false;
		}
	})

	function setMinMax(){
		var hw=options.actual,
			minW=hw<1?Math.floor(modular.min/hw):modular.min,
			minH=hw>1?Math.floor(modular.min*hw):modular.min,
			maxW=Math.min(modular.max, options.maxW),
			maxH=Math.min(modular.max, options.maxH);
		maxW=Math.round(Math.min(maxW, modular.max/hw));
		maxH=Math.round(Math.min(maxH, modular.max*hw));
		wInp.setMinMax(minW, maxW);
		hInp.setMinMax(minH, maxH);
		sizeInp.trigger('input');
	}

	function getIntersections(el0, box0, move) {

		el0 = el0 || svgG[0].querySelector('.selected');
		const bBox = el0.firstChild.getBoundingClientRect();
		box0 = box0 || bBox;

		const intersections=[];
		const space= 5; //options.space * options.dpcm;

		svgG[0].childNodes.forEach(el=>{
			if (el==el0) return;

			const box=el.firstChild.getBoundingClientRect();

			const top = box.bottom + space - box0.top;
			const bottom = box0.bottom + space - box.top;
			const left = box.right + space - box0.left;
			const right = box0.right + space - box.left;

			const intersects = (top>0 && bottom>0 && left>0 && right>0);

			intersections.push({left, top, right, bottom, el, intersects});
		})
		return intersections;
	}

	function markIntersections(el0, intersections=[]) {

		svgG[0].childNodes.forEach(el=>{
			if (intersections.some(item => item.intersects && item.el==el)) {
				el.classList.add('intersected', el0.id);
				el0.classList.add('intersected', el.id);
			} else if(el.classList.contains('intersected')) {
				el.classList.remove(el0.id);
				el0.classList.remove(el.id);
				if (!/rect/.test(el.classList.value)) el.classList.remove('intersected')
			}
		})
	}

	// move and resize

	mainContainer.on('mouseleave', 'svg:not(.blocked) .hover', function(e){

		$('rect.hover', svg0).add(this).removeClass('hover');

	}).on('mouseover touchstart mousedown', 'svg:not(.blocked) g.module', function(e){
		//console.log(e.type, svg0.hasClass('blocked'))

		const el=this, $el=$(this).mouseleave(), targ=e.target;
		const box0=el.querySelector('rect').getBoundingClientRect();

		if (this.nextSibling) svgG.append(this);
		$el.addClass('hover');//.one('mouseleave', e=>$el.removeClass('hover'));
		/*

		[5]---1---[6]
		 |         |
		 4    0    2 
		 |         |
		[8]---3---[7]

		*/
		var rects=$('rect', el),
			i=rects.index(targ)
			move={
				l: /0|8|4|5/.test(i),
				t: /0|5|1|6/.test(i),
				r: /0|6|2|7/.test(i),
				b: /0|8|3|7/.test(i),
			};

		move.w=i && (-move.l || +move.r);
		move.h=i && (-move.t || +move.b);

		if (i) rects[i%5 || 4].classList.add('hover');
		if (i>4) rects[i-4].classList.add('hover');
		//console.log(move);

		if (e.type=='mouseover') return;

		$('.selected', svg0).removeClass('selected');
		$(this).addClass('selected');
		//console.log (e)
		var touch = (e.type=='touchstart' && e.originalEvent.changedTouches[0]);

		//if (touch && !$el.is(':hover')) return;
		e.preventDefault();
	    svg0.addClass('blocked');
		var x0=(touch||e).pageX;
		var y0=(touch||e).pageY;
		var id=touch && touch.identifier;
		var dx, dy, shiftX=0, shiftY=0;

		var bBox=$('>rect', svg0)[0].getBoundingClientRect(),
			minSize=modular.minSize*options.dpcm;
		function change(e){
			//
			var touch=e;
			if (e.type=='touchmove') {
				let touches=e.originalEvent.changedTouches;
				for (var i = 0; i < touches.length; i++) {
					if (touches[i].identifier===id) touch=touches[i]
				}
				if (touch==e) return;
			} else {
				e.preventDefault();
			}
			dx=(touch.pageX-x0);
			dy=(touch.pageY-y0);
			var x=box0.x - bBox.x,
				y=box0.y - bBox.y,
				w=box0.width,
				h=box0.height;

			var {left, top, right, bottom} = box0;
			var box = el.querySelector('rect').getBoundingClientRect();

			var css={}, attr={}, sticking={};

			dx=Math.max(dx, move.l?-x:-w+minSize);
			dy=Math.max(dy, move.t?-y:-h+minSize);

			dx=Math.min(dx, move.w<0?w-minSize:bBox.width-x-w);
			dy=Math.min(dy, move.h<0?h-minSize:bBox.height-y-h);

			left += dx*move.l;
			right += dx*move.r;
			top += dy*move.t;
			bottom += dy*move.b;

			var box1 = {left, top, right, bottom};

			const intersections = getIntersections(el, box1);

			for (let key in box1) {
				let move = box1[key] - box[key];
				if (!move) continue;
				intersections.forEach(intersection=>{
					
				})
			}

			markIntersections(el, intersections);

			if (move.l) css['--x']=attr['x']=+((x+dx)/bBox.width*100).toFixed(2)+'%';
			if (move.t) css['--y']=attr['y']=+((y+dy)/bBox.height*100).toFixed(2)+'%';
			css['--w']=attr['width']=+((w+dx*move.w)/bBox.width*100).toFixed(2)+'%';
			css['--h']=attr['height']=+((h+dy*move.h)/bBox.height*100).toFixed(2)+'%';

			$(el).css(css);
			//$('>rect', el).attr(attr);
			//return false
		}

		$win.on(touch?'touchmove':'mousemove', change)

		 .one('mouseup touchcancel touchend blur', function end(e){

			$(window).off('mousemove touchmove', change)
			 .off('mouseup touchcancel touchend blur', end);

			if (touch) rects.removeAttr('class');

			svg0.removeClass('blocked');
			if (e.type!='mouseup' || e.target!=targ) {
				$el.mouseleave();
				if (!touch) $(e.target).mouseover();
			}
			if (dx || dy) 
				cHistory.save()
			else if (/mouseup|touchend/.test(e.type)) {
				$('#'+Array.from(el.classList).join(', #'), svgG).first().before(el);
			}

		})
	})

	// Rulers

	const hRuler=$('.h-ruler');
	const wRuler=$('.v-ruler');
	for (var i = 0, j, html; i*20 < modular.max; i++) {
		j=i*20;
		html='<div><div><div>'+j+'</div><div>'+(j+5)+'</div></div><div><div>'+(j+10)+'</div><div>'+(j+15)+'</div></div></div>'
		hRuler.append(html);
		wRuler.append(html);
	}

	// Whole resize

	var resized;

	const wInp=$('.modular-size .width input').on('input', function(e){
		options.h=(this.value*options.actual).toFixed(1)*1;
		mainContainer.css({'--h': options.h, '--w': this.value});
		wInp.val(this.value).setBg();
		hInp.not(':focus').val(options.h);
		if (e.originalEvent) hInp.triggerHandler('input');

		resized=true;
	});
	const hInp=$('.modular-size .height input').on('input', function(e){
		options.w=(this.value/options.actual).toFixed(1)*1;
		mainContainer.css({'--h': this.value, '--w': options.w});
		hInp.val(this.value).setBg();
		wInp.not(':focus').val(options.w);
		if (e.originalEvent) wInp.triggerHandler('input');
	});
	const sizeInp=wInp.add(hInp).trigger('input')
	 .on('change', ()=>{if (resized) resizeCanvas(1)});
	resized=false;

	const sInp=$('.modular-size .space input').on('input change', function(e){
		sInp.val(options.space=this.value).setBg();
	}).val(options.space=modular.space).setBg()
	.setMinMax(modular.minSpace, modular.maxSpace);

	$.fn.hideScroll=function(){ 
		var el=this[0];
		if (!el) return;
		el.style.marginRight='';
		el.style.marginRight=el.clientWidth-el.offsetWidth-.5+'px';	
	}

	$('.filter-picture').on('opened', function(e, cObj){
		cObj.$details.filter('.modular-shapes').hideScroll()
	});

	// show preview

	var frames=$('.frames');
	var wallTab=$('.interior.tabs-item').on('show', function(){
		$('.interior-item.active').click();
		var single=!$('.module', svg0)[1];
		frames[single?'show':'hide']();
		wallTab[single?'addClass':'removeClass']('frame');
		wallSizeInp.trigger('input');
	});

	$('.modular-wrap').on('show', function(){
		$('.canv_cel').append(svg0);
		resizeCanvas();
	});

	wallTab.on('mousedown touchstart', function(e){
		var touch = e.type=='touchstart' && e.originalEvent.changedTouches[0];
		// if (touch && !$(this).is(':hover')) return;
		e.preventDefault();
		wallTab.addClass('grabbing');
		var x0=(touch||e).pageX;
		var y0=(touch||e).pageY;
		var id=touch && touch.identifier;

		var box0=svgG[0].getBoundingClientRect();
		var preview=svg0.closest('div')[0];
		var bBox=preview.parentNode.getBoundingClientRect();
		var left=parseInt(preview.style.left || 50);
		var top=parseInt(preview.style.top || 50);
		function change(e){
			//
			var touch=e;
			if (e.type=='touchmove') {
				let touches=e.originalEvent.changedTouches;
				for (var i = 0; i < touches.length; i++) {
					if (touches[i].identifier===id) touch=touches[i]
				}
				if (touch==e) return;
			} else {
				e.preventDefault();
			}
			var dx=touch.pageX-x0;
			var dy=touch.pageY-y0;

			dx=Math.max(dx, bBox.left-box0.left);
			dy=Math.max(dy, bBox.top-box0.top);

			dx=Math.min(dx, bBox.right-box0.right);
			dy=Math.min(dy, bBox.bottom-box0.bottom);

			preview.style.left= left+dx/bBox.width*100+'%';
			preview.style.top = top+dy/bBox.height*100+'%';
		}

		$(window).on(touch?'touchmove':'mousemove', change)
		 .on('mouseup touchcancel touchend blur', function(){
		 	$(window).off('mousemove touchmove', change);
			wallTab.removeClass('grabbing');
		})
	})

	var wallSizeInp=$('.interior-sizes input').on('input', function(e){
		wallSizeInp.val(this.value).setBg();
		var div=$('.interior .active'),
			box=svgG[0].getBBox(),
			box0=svg0[0].getBBox(),
			w=div.width(),
			scale=w/this.value;
		$('.interior-wrapper', div).css({
			width: box.width*scale+'px',
			height: box.height*scale+'px',
			'--size': box0.width*scale+'px',
			'--dx': (box0.x-box.x)*scale+'px',
			'--dy': (box0.y-box.y)*scale+'px',
			'font-size': scale
		});
		redraw();
	});

	$('.interior-item').each(function(){
		var place=$('<div><div class="interior-wrapper"/></div>').appendTo(wallTab);
		$(this).click(function(){
			$(this.parentNode).trigger('focus');
			$(".interior-slider")[0].slick.setPosition();
			$('img', wallTab).prop('src', this.dataset.interior);
			$('.active', wallTab).removeClass('active');
			place.addClass('active');
			$('div', place).append(svg0);
			var minMax=this.dataset.size.split(/[\,,\s]+/);
			wallSizeInp.setMinMax(minMax[0], minMax[1]).trigger('input');
		})
	})//.filter('.active').click();

	$('.ramu-item').click(function(){
		var css=(this.dataset.frame || 'none').replace(/\(([^"].+)\)/, '("$1")');
		debugger
		wallTab.css({
			'--frame': css,
			'--fw': (css.match(/\d*\.?\d+em/)||0)[0]
		})
	})
	$('.modular .cs-controls .csc-item').addClass('disabled');

	$(window).on('resize', e=>resizeCanvas())

	function checkActive() {
		var active=$('.selected, .module:only-child', svg0)
		if (!active[0]) alert(lang.notSelected)
		else return active
	}

	$('.split_v').click(function(){
		var selected=checkActive();
		if (!selected) return false;
		var bBox=$('>rect', selected)[0].getBBox(),
			h=(bBox.height - options.space)/2,
			y=bBox.y;
		if (h<modular.minSize) return alert(lang.tooSmallH);
		selected.css('--h', (h*100/options.h).toFixed(2)+'%')
		.clone().css('--y', ((y+h+options.space)*100/options.h).toFixed(2)+'%')
		.setRectId().removeClass('selected').appendTo(svgG);
	})
	$('.split_h').click(function(){
		var selected=checkActive();
		if (!selected) return false;
		var bBox=$('>rect', selected)[0].getBBox(),
			w=(bBox.width - options.space)/2,
			x=bBox.x;
		if (w<modular.minSize) return alert(lang.tooSmallW);
		selected.css('--w', (w*100/options.w).toFixed(2)+'%')
		.clone().css('--x', ((x+w+options.space)*100/options.w).toFixed(2)+'%')
		.setRectId().removeClass('selected').appendTo(svgG);
	})
	$('.cs-controls .delete').click(function(){
		var selected=checkActive();
		if (!selected) return false;
		if (selected.is(':only-child')) return;
		selected.remove();
		markIntersections(selected[0]);
		delete cHistory.movedEl;
	})
	$('.cs-controls .add_block').click(e=>{
		const block=SVG('g');

		$(block).css({'--w': modular.minSize+'px', '--h': modular.minSize+'px'})
		.addClass('module selected').html('<rect>').setModulesHtml().appendTo(svgG);

		markIntersections(block, getIntersections(block));
	})
	$('.cs-controls #clear').click(e=>{svgG.html('')});

	$('.cs-controls').click('.csc-item', e=>{(cHistory[e.target.id]||cHistory.save)()});

	window.saveModular=function(){
		var inp=$('input.imageFile:hidden');
		var svg=svg0.clone().attr({
			width: options.w+'cm', height: options.h+'cm',
			xmlns: 'http://www.w3.org/2000/svg',
			'xmlns:xlink': 'http://www.w3.org/1999/xlink'
		});

		$('image', svg).attr({
			href: inp[0].files[0].name,
			'xlink:href': inp[0].files[0].name
		});

		$('.module>rect', svg).each(function(i){
			var $el=$('.module>rect', svg0).eq(i);
			$(this).attr({
				fill: 'url(#image)', stroke: 'none',
			    width: $el.css('--w'),
			    height: $el.css('--h'),
			    x: $el.css('--x'),
			    y: $el.css('--y')
			})
			console.log($el.closest('g')[0].style)//.css('--w'))
		}).appendTo(svg);
		$('g, rect:first-child', svg).remove();

		return {svg: svg[0].outerHTML, input: inp}
	}
})