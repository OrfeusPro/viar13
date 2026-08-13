(function(){
	
	$(".slider-tabs").on("click", "a", function() {
			var a = $(this).data("tabs");
			$(".slider-tabs a").removeClass("active"),
			$(this).addClass("active"),
			$(".gallery-photo .tabs-item").removeClass("active"),
			$("." + a).addClass("active").trigger('show'),
			//$(".interior-slider").get(0).slick.setPosition(),
			// $(".ramu-slider").get(0).slick.setPosition(),
			$.fn.matchHeight &&
				$(".ramu-item .img").matchHeight({
					byRow: !1
				})
		});	
		
	$.fn.setMinMax=function(min, max){
		if (min==='' || isNaN(min)) min=this.prop('min');
		if (max==='' || isNaN(max)) max=this.prop('max');
		$('~.changeSm span', this)
		.eq(0).html(min).end()
		.eq(1).html(max)
		return this.prop({min: min, max: max});
	}	
	
	//загружаем в кеш
	
	$('.ramu-item').each(function(){
			if (this.dataset.src){
				var img = new Image();
				img.src = this.dataset.src;
			}
		});
				
	$('.interior-item').each(function(){
			if (this.dataset.src){
				var img = new Image();
				img.src = this.dataset.src;
			}
		});		
				
				
				

	var canvas = document.getElementById("canvas_interior");
   
	var gen = new InteriorGenerator(canvas);
	
	
	
	function resize()
	{	
		gen.maximum(canvas.parentElement.clientWidth,Math.max(700,canvas.parentElement.clientHeight));//максимальна область 
	} 
		
		
		
	
	$( window ).resize(function() {
		
			resize();
		});
	
		
	$('.interior.tabs-item').on('show', function(){	
		
			
			resize();
			
			// обычные товары
			if (typeof is_image_canvas !== 'undefined') {
				if($('.tabs-item1 .slick-slide[data-slick-index="2"]').length){
					var cur_img = $('.tabs-item1 .slick-slide[data-slick-index="2"]').children('img');
					console.log('third img');
				}else{
					var cur_img = $('.tabs-item1 .slick-slide.slick-current.slick-active').children('img');
					console.log('cur img');
				}
				let src = cur_img.attr('src');

				if (typeof src == 'undefined') return;

				let cur_size = $('.js_size.kviz-radio_active').data('size');
				if(cur_size){
					var w = cur_size.split('x')[0];			
					var h = cur_size.split('x')[1];		
				}else{
					var w = 30;
					var h = 40;
				}
				
				gen.setPortrait(src,parseInt(w),parseInt(h));
			}

			// модульные картины
			else if (typeof is_image_modular !== 'undefined') {				
					console.log('is on modular');
					
					let data = onShowInterior(function(data){
						console.log(data);
						gen.setPortrait(data.src,data.size.w,data.size.h,data.clips);
					});

					if (data)gen.setPortrait(data.src,data.size.w,data.size.h,data.clips);

			}
			
			else if (window.onShowInterior)
			{
				let data = window.onShowInterior();
				gen.setPortrait(data.src,data.size.w,data.size.h);
			}

			$(".interior-slider").get(0).slick.setPosition();		
			
				
			
			$('.ramu-item.active').each(function(){
					if (this.dataset.src){
						gen.setRam(this.dataset.src,parseInt(this.dataset.width));	
					}else
					{
						gen.setRam();	
					}
				});	
		
			$('.interior-item.active').each(
				function(){
					var minMax=this.dataset.size.split(/[\,,\s]+/);
					gen.setInterior(this.dataset.interior);
					wallSizeInp.setMinMax(minMax[0], minMax[1]).trigger('input');
					
					
				}
			);
		});
		
		
		
		
	var wallSizeInp= $('.interior-sizes input').on('input', function(e){
			wallSizeInp.val(this.value);
			gen.setInteriorWidth(this.value);
		});
		
		
	$('.ramu-item').on("click",function()
		{
			if (this.dataset.src){
				gen.setRam(this.dataset.src,parseInt(this.dataset.width));	
			}else
			{
				gen.setRam();	
			}
		});
	
	
	$('.interior-item').on("click",
		function(e){
			var minMax=this.dataset.size.split(/[\,,\s]+/);
			wallSizeInp.setMinMax(minMax[0], minMax[1]).trigger('input');
			gen.setInterior(this.dataset.interior);
		}
	);
	
	
	
	
	$('.js_full').on("click", function(e){
		$('.js_top_fulll').trigger('click');
	});

	$('.js_min').on("click", function(e){
		$('.js_top_zoom_min').trigger('click');
	});

	$('.js_plus').on("click", function(e){
		$('.js_top_zoom_plus').trigger('click');
	});

	$('#interior_zoom_minus').on("click",
		function(e){
			gen.minus_Zoom();
		}
	);
	
	
	$('#interior_zoom_plus').on("click",
		function(e){
			gen.plus_Zoom();
		}
	);
	
	var tool_fullscreen = document.getElementById("interior_fullscreen");

		
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

