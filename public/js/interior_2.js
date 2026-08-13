(function(){

		function toDataUrl(url, callback) {
			var xhr = new XMLHttpRequest();
			xhr.onload = function() {
				var reader = new FileReader();
				reader.onloadend = function() {
					callback(reader.result);
				}
				reader.readAsDataURL(xhr.response);
			};
			xhr.open('GET', url);
			xhr.responseType = 'blob';
			xhr.send();
		}
	
	
	
		var out_img = new Image();
	  
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



		var size;
    
		var frames=$('.frames');

		var wallTab = $('.interior.tabs-item');

		var cw_w,cw_h;
		setTimeout(() => {
			cw_w = $('.tabs-item1 .slick-current').children('img').width();
			cw_h = $('.tabs-item1 .slick-current').children('img').height();
		}, 200);

		$('.tabs-item1').on('afterChange', function(event, slick, currentSlide, nextSlide){ 
			cw_w = $('.tabs-item1 .slick-current').find('img').width();
			cw_h = $('.tabs-item1 .slick-current').find('img').height();
		});
		
		$(document).on('click', '.slider-tabs a', function(e){
			
			var imagepath = $('.tabs-item1 .slick-current').find('img').attr('src');

			toDataUrl(imagepath, function(bs64) {
				out_img.src = bs64;
			});
			
			$('.interior-item.active').click();
			frames.show();
			wallTab.addClass('frame');
			wallSizeInp.trigger('input');

		});
				

		wallTab.on('mousedown touchstart', function(e){
				var touch = e.type=='touchstart' && e.originalEvent.changedTouches[0];
				// if (touch && !$(this).is(':hover')) return;
				e.preventDefault();
				wallTab.addClass('grabbing');
				var x0=(touch||e).pageX;
				var y0=(touch||e).pageY;
				var id=touch && touch.identifier;
		
				var pos0=$('.active .interior-wrapper', wallTab)[0].getBoundingClientRect();
				var preview=out_img.closest('div');
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

					dx=Math.max(dx, bBox.left-pos0.left);
					dy=Math.max(dy, bBox.top-pos0.top);

					dx=Math.min(dx, bBox.right-pos0.right);
					dy=Math.min(dy, bBox.bottom-pos0.bottom);

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

				if($('.js_size.active').length){
					cw_w =  $('.js_size.active').data('size').split('x')[0]*8;
					cw_h =  $('.js_size.active').data('size').split('x')[1]*8;
				}
				
				var div=$('.interior .active'),
				
				box = {
					x:0,y:0,
					width:cw_w/5,
					height:cw_h/5
				};
				
				w=div.width(),
				scale=w/this.value;
				$('.interior-wrapper', div).css({
						width: box.width*scale+'px',
						height: box.height*scale+'px',
						'--size': box.width*scale+'px',
						'--dx': '0px',
						'--dy': '0px',
						'font-size': scale
					});
	
		});

		$('.interior-item').each(function(){
				var place=$('<div><div class="interior-wrapper"/></div>').appendTo(wallTab);
				$(this).click(function(){

						$(this.parentNode).trigger('focus');
						$(".interior-slider")[0].slick.setPosition();
						$('img', wallTab).eq(0).prop('src', this.dataset.interior);
						$('.active', wallTab).removeClass('active');
						place.addClass('active');
						$('div', place).append(out_img);
						var minMax=this.dataset.size.split(/[\,,\s]+/);
						wallSizeInp.setMinMax(minMax[0], minMax[1]).trigger('input');

						$('.interior-item').removeClass('active');
						$(this).addClass('active');
					})
			})//.filter('.active').click();

		$('.ramu-item').click(function(){
			var css=(this.dataset.frame || 'none').replace(/\(([^"].+)\)/, '("$1")');
			
			wallTab.css({
					'--frame': css,
					'--fw': (css.match(/\d*\.?\d+em/)||0)[0]
				})

			$('.ramu-item').removeClass('active');
			$(this).addClass('active');
			

			if($('.ramm__pr').length && rp == 0){
				$('.ramm__pr').remove();
			}
			
			if(rp != 0 && rp != undefined && rp != null){
				$('.ramm__pr').remove();
				var rm_txt = $('body').data('for_ram');
				$('.total-price #totalSum').append("<span class='ramm__pr'>"+parseInt(rp)+"€ "+rm_txt+"</span>");
			}

			
		})
	

	
	})();

