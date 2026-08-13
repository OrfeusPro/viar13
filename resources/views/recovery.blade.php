<div class="portraits portrait__screen new-screen size-screen no-bg container">
    <div class="portraits-wrap" style="text-align: center">
        <div class="portraits-slider slick-initialized slick-slider slick-dotted">
            <div class="slick-list draggable">
                <div class="slick-track" style="opacity: 1; width: 1914px;">
                    <div class="portraits-slide sl-slider pp-screen slick-slide slick-current slick-active"
                         data-slick-index="0" aria-hidden="false" tabindex="0" role="tabpanel"
                         id="slick-slide00" aria-describedby="slick-slide-control00">
                        <div class="m-screen-slide">
                            <div class="pmain__screen">


                                <div class="mp__inner">
                                    <div class="section-frame">
                                        <h2 style="font-size: 30px">
                                            @lang('cart_new.restoring_cart')
                                        </h2>
                                        <p>
                                            @lang('cart_new.your_email_to_restore_cart')
                                        </p>

                                        @if ($errors->any())
                                            <div class="alert alert-danger">
                                                <ul>
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <form action="{{ route('cart.recover.process') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="token" value="{{ $token }}">
                                            <input type="hidden" name="redirect" value="{{ $redirect }}">

                                            <div class="form-group">
                                                <label for="email">Email:</label>
                                                <input type="email" id="email" name="email" class="form-control"
                                                       required
                                                       style="border: 1px dotted gray;border-radius: 7px;padding: 2px 10px;"
                                                >
                                            </div>

                                            <button type="submit" class="btn btn-desk">
                                                @lang('cart_new.button_restore_cart')
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn {
        min-width: 120px;
        max-width: 100%;
        height: 35px;
        background: linear-gradient(90deg, #FC8C5F 0%, #FA7846 100%);
        box-shadow: 0 20px 50px -5px rgba(250, 120, 70, 0.25), 0 3px 0 #E87145;
        border-radius: 8px;
        font-weight: 700;
        font-size: 16px;
        line-height: 19px;
        align-items: center;
        text-align: center;
        color: #FFFFFF;
        justify-content: center;
        padding: 10px 15px;
        transition: 0.3s;
        margin-top: 10px;
    }

</style>
