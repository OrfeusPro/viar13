<div class="popup-discount">
  <div class="popup-discount__wrapper">
	 <a href="" class="popup-discount__exit js-popup-discount__btn">
		<svg>
		   <use xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#close"></use>
		</svg>
	 </a>
	 <div class="popup-discount__title">
		Выгодное предложенния <b>для ВАС!</b>
	 </div>
	 <div class="popup-discount__text">
		Замените Ваш заказ на картину <b>размером 55x70</b> (больше Вашей картины на 1размер)
		<b class="block"> с выгодной скидкой!</b>
	 </div>
	 <div class="popup-discount__arrow-art">
		<img src="{{ asset(env('THEME') . 'img/cart/arrow-img.svg') }}" alt="">
	 </div>
	 <div class="popup-discount__row">
		<div class="popup-discount__col small">
		   <span>
				Ваш заказ
			</span>
		   <div class="popup-discount__img">
			  <img src="{{ asset(env('THEME') . 'img/cart/painting.svg') }}" alt="">
		   </div>
		</div>
		<div class="popup-discount__col big">
		   <span>
				Вместо
			</span>
		   <b> 35€</b>
		   <div class="popup-discount__img">
			  <svg>
				 <use xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#discount"></use>
			  </svg>
			  <img src="{{ asset(env('THEME') . 'img/cart/painting.svg') }}" alt="">
		   </div>
		   <strong> 32€</strong>
		</div>
	 </div>
	 <div class="popup-discount__button">
		<a href="" class="btn--orange">
		   <span>Воспользоватся скидкой</span>
		   <svg>
			  <use xlink:href="{{ asset(env('THEME') . 'img/cart/sprite.svg') }}#percent"></use>
		   </svg>
		</a>
	 </div>
  </div>
</div>
<div class="bg-overley "></div>