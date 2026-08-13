(function(){


	$.fn.setMinMax=function(min, max){
		if (min==='' || isNaN(min)) min=this.prop('min');
		if (max==='' || isNaN(max)) max=this.prop('max');
		$('~.changeSm span', this)
		.eq(0).html(min).end()
		.eq(1).html(max)
		return this.prop({min: min, max: max});
	}	
	
	var canvas = document.getElementById("canvas_interior");
   
	var gen = new InteriorGenerator(canvas);
	
	
	
	function resize()
	{	
		gen.maximum(canvas.parentElement.clientWidth,Math.max(700,canvas.parentElement.clientHeight));//максимальна область 
	} 
		
	resize()
		
	
	$( window ).resize(function() {
		
			resize();
		});

	$('.frame-item').each(function(){
		if (this.dataset.src){
			var img = new Image();
			img.src = this.dataset.src;
		}
	});

	$(document).on('click', '.rcard-tab', function() {
		$('.rcard-tab').removeClass('active');
		$(this).addClass('active');
		let blockDataId = $(this).data('id');
		$(`.rcm-block`).addClass('hidden-block');
		$(`.rcm-block[data-block="${blockDataId}"]`).removeClass('hidden-block');
		$(`.rcc-block`).addClass('hidden-block');
		$(`.rcc-block[data-block="${blockDataId}"]`).removeClass('hidden-block');
		if (blockDataId === 2) {
			resize()
			let portraitSrc;
			if (window.matchMedia('(max-width: 991px)').matches) {
				portraitSrc = $('.mc-mainSlide.swiper-slide-active').data('portrait');
				console.log(portraitSrc)
			} else {
				portraitSrc = $('.mc-navSLide.active, .mc-navSlide.swiper-slide-thumb-active').data('portrait');
			}
			let portraitSize = $('.mcard-size li.active input').data('size');
			let w = portraitSize.split('x')[0];
			let h = portraitSize.split('x')[1];
			

	
			gen.setPortrait(portraitSrc,parseInt(w),parseInt(h));
			// let interiorSource = $('.interiorSlide.active').data('interior');
			// gen.setInterior(interiorSource);



			$('.frame-item.active').each(function(){
				let fWidth = parseInt(this.dataset.width);
				if (this.dataset.src){
					console.log(typeof fWidth)
					gen.setRam(this.dataset.src, fWidth);	
				}else
				{
					gen.setRam();	
				}
			});	
			$('.interiorSlide.active').each(
				function(){
					var minMax=this.dataset.size.split(/[\,,\s]+/);
					gen.setInterior(this.dataset.interior);
					wallSizeInp.setMinMax(minMax[0], minMax[1]).trigger('input');
					
					
				}
			);


			// let ramSrc = $('.frame-item.active').data('frame');
			// let ramWidth = $('.frame-item.active').data('width');
			// gen.setRam(ramSrc,parseInt(ramWidth));	

		}
	})

    jcf.replaceAll()

	$(document).on('show', '.rcc-block', function() {
		resize();
		let interiorSource = $('.interiorSlide.active').data('interior');
		console.log($('.interiorSlide.active'))
		gen.setInterior(interiorSource);
	})


	var wallSizeInp= $('.rcc-size input').on('input', function(e){
		wallSizeInp.val(this.value);
		gen.setInteriorWidth(this.value);
	});

	$(document).on('click', '.interiorSlide', function(e) {
		// resize();
		$('.interiorSlide').removeClass('active');
		$(this).addClass('active');
		var index = $(this).data('slider');

		$('.range__box').hide(0);
		$('.range__box').eq(index).show(0);
		var minMax=this.dataset.size.split(/[\,,\s]+/);
		wallSizeInp.setMinMax(minMax[0], minMax[1]).trigger('input');
		gen.setInterior(this.dataset.interior);
	})


	$(document).on('click', '.mc-navSLide', function() {
		
		let index = $(this).data('slider');
		$('.mc-navSLide').removeClass('active')
		$(`.mc-navSLide[data-slider="${index}"]`).addClass('active');
		let src = $(this).data('portrait');
		let portraitSize = $('.mcard-size li.active input').data('size');
		let w = portraitSize.split('x')[0];
		let h = portraitSize.split('x')[1];
		gen.setPortrait(src, parseInt(w),parseInt(h));
	})

	$(document).on('click', '.frame-item', function() {
		$('.frame-item').removeClass('active');
		let fWidth = parseInt(this.dataset.width);
		$(this).addClass('active');
		if (this.dataset.src) {
			console.log(typeof fWidth)
			gen.setRam(this.dataset.src, fWidth);	
		}else
		{
			gen.setRam();	
		}
	  })

	  window.gen = gen;







	
	var tool_fullscreen = document.getElementById("rIntZoom");

		
	tool_fullscreen.onclick = function()
	{
		tool_fullscreen.classList.add("disable");	
		var body = document.getElementById("interior_container");
			
		var fullscreen = document.createElement("div");
		fullscreen.className = "interior_fullscreen";
				
		document.body.appendChild(fullscreen);				
		var old_parentElement = body.parentElement; 		
			
			
		function close()
		{			
			
			tool_fullscreen.classList.remove("disable");
			document.body.removeChild(fullscreen);
			old_parentElement.appendChild(body);
			$(window).trigger('resize');
			resize();
		}
				
				
				
		var wrap = document.createElement("div");
		wrap.className = "wrap";
		wrap.onclick = close;
				
		fullscreen.appendChild(wrap);
			
		fullscreen.appendChild(body);
			
		resize();
	}
})();

