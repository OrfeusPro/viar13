
{{-- <script src="//www.google.com/recaptcha/api.js" async defer></script> --}}
<script src="{{ ver_asset('js/custom.js') }}"></script>
<script defer src="{{ ver_asset(env('THEME').'js/cart.min.js') }}"></script> <!-- main menu 6 -->

<link type="font/woff" crossorigin rel="preload" href="{{ asset('fonts/fontello.woff') }}" as="font" 
onload="this.onload=null;this.rel='stylesheet'">
<link type="font/woff" crossorigin rel="preload" href="{{ asset('fonts/Conv_MrDafoe-Regular.woff') }}" as="font" 
onload="this.onload=null;this.rel='stylesheet'">


{{-- <script id="loadcss">
  loadCSS( "{{ asset('fonts/fontello.woff') }}", document.getElementById("loadcss") );
  loadCSS( "{{ asset('fonts/Conv_MrDafoe-Regular.woff') }}", document.getElementById("loadcss") );
</script> --}}

<script>
    function regFormSubmit(token) {
        $.ajax({
                method: 'POST',
                url: "{{ route('register') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    email: $('.popup-registration input[name=email]').val(),
                    password: $('.popup-registration input[name=password]').val(),
                    password_confirmation: $('.popup-registration input[name=password_confirmation]').val(),
                    invited: $('.popup-registration input[name=invited]').val()
                },
                dataType: 'json',
                success: function (msg) {
                    if (msg.status = 'ok') {
                        $('.popup-registration input[name=email]').val('')
                        $('.popup-registration input[name=password]').val('')
                        $('.popup-registration input[name=password_confirmation]').val('')
                        $('.popup-registration input[name=invited]').val('')
                        $('.popup-registration div.error').html('')

                        location.href = "{{ url(config('auth.auth_redirect_to')) }}"
                    }
                },
                error: function (jqXHR, exception) {
                    var msg = JSON.parse(jqXHR.responseText);

                    try {
                        var errs = msg.errors;
                    } catch (error) {
                        location.reload;
                    }

                    $('.popup-registration div.error').html('');

                    try {
                        for (key in msg.errors) {
                            for (key2 in msg.errors[key]) {
                                $('.popup-registration div.error[name=' + key + ']').append('<p style="color: red;">' + msg.errors[key][key2] + '</p>')
                            }
                        }                  
                    } catch (error) {
                        location.reload;
                    }
                }
            })
    }
  </script>

<script>
    if($('#real_file_input').length){
        setTimeout(() => {
            var txt = $('#real_file_input').data('desc');            
            $('.lbl_upl span.jcf-fake-input').text(txt);            
        }, 200);
    }

    !function(e){"use strict";var t={opts:{},elements:[],init:function(n){t.opts=e.extend({extratxt:"Подробнее: %link%",length:150,hide:!0,allowcopy:!0,first:!1,sourcetxt:"Источник",style:"",className:"copyright-span"},n||{}),this.each(function(){t.elements.push(this)});var o=t.isIE()?"body":document;return e(o).on("copy",function(n){if(n.target&&"TEXTAREA"!==n.target.tagName){var o=!1;if(t.isWinSelection()){var i=window.getSelection().getRangeAt(0);e(t.elements).each(function(){i.intersectsNode(this)&&(o=!0)})}else if(t.isDocSelection()){i=document.selection.createRange();e(t.elements).each(function(){var e=t.createRangeWithNode(this);document.createRange?-1===i.compareBoundaryPoints(Range.END_TO_START,e)&&1===i.compareBoundaryPoints(Range.START_TO_END,e)&&(o=!0):i.compareEndPoints("StartToEnd",e)<0&&i.compareEndPoints("EndToStart",e)>0&&(o=!0)})}else n.preventDefault();return o?t.beforeCopy(n):void 0}}),this},createRangeWithNode:function(e){var t;return window.getSelection&&document.createRange?(t=document.createRange()).selectNodeContents(e):document.body.createTextRange&&(t=document.body.createTextRange()).moveToElementText(e),t},isOpera:function(){return!!window.opera||navigator.userAgent.indexOf(" OPR/")>=0},isIE:function(){return!!document.documentMode},isWinSelection:function(){return window.getSelection},isDocSelection:function(){return document.selection&&"Control"!==document.selection.type},beforeCopy:function(e){var n=t.getSelection();if(!n||n.length<t.opts.length)return!0;n.length>=t.opts.length&&!t.opts.allowcopy?e.preventDefault():t.isWinSelection()&&!t.isOpera()?t.stdCopy(e):t.isWinSelection()&&t.isOpera()?t.oprCopy(e):t.isDocSelection()?t.docCopy(e):e.preventDefault()},stdCopy:function(n){if(t.opts.first)var o=t.getExtra()+"&nbsp;\n"+t.getSelection();else o=t.getSelection()+"&nbsp;\n"+t.getExtra();var i=window.getSelection(),c=i.getRangeAt(0).cloneRange(),r=e("<div>",{html:o,style:"position:absolute;left:-99999em;"});e("body").append(r),window.getSelection().selectAllChildren(r[0]),window.setTimeout(function(){r.remove(),(i=window.getSelection()).removeAllRanges(),i.addRange(c)},0)},docCopy:function(e){if(t.opts.first)t.getExtra(),t.getSelection();else t.getSelection(),t.getExtra();var n=document.selection.createRange(),o=n.duplicate(),i=document.createElement("DIV");i.innerHTML=t.getExtra(),i.setAttribute("style","position:absolute;left:-99999em;"),o.collapse(t.opts.first),o.pasteHTML(i.outerHTML),t.opts.first?e.preventDefault():(n.setEndPoint("EndToEnd",o),n.select())},oprCopy:function(n){var o=window.getSelection(),i=o.getRangeAt(0),c=i.cloneRange();c.collapse(t.opts.first);var r=document.createElement("DIV");r.innerHTML=t.getExtra(),r.setAttribute("style","position:absolute;left:-99999em;"),c.insertNode(r),t.opts.first||i.setEndAfter(r),window.setTimeout(function(){e(r).remove(),o.removeAllRanges(),o.addRange(i)},0)},getSelection:function(){var e="";return t.isWinSelection()?e=window.getSelection().toString():t.isDocSelection()&&(e=document.selection.createRange().text),e},getExtra:function(){var e="";t.opts.hide&&(e+="position:absolute;left:-9999em;top:0;"),e+=t.opts.style;return'<span class="'+t.opts.className+'"'+(e?' style="'+e+'"':"")+">"+t.opts.extratxt.replace("%link%",'<a href="'+document.location.href+'">'+document.location.href+"</a>").replace("%source%",'<a href="'+document.location.href+'">'+t.opts.sourcetxt+"</a>")+"</span>"},destroy:function(){}};e.fn.copyright=function(e){if("object"==typeof e||!e)return t.init.apply(this,arguments)}}(jQuery);
    // copyright
    var baseurl = window.location.origin+window.location.pathname;
    
    $('.blog-article, .what-item').copyright({
        extratxt: '&copy; %source%',
        sourcetxt: baseurl,
        hide: false
    });

    // share
    
        
    $(function () {
        // login
        $(document).on('submit', '.js_login_form', function (e) {
            e.stopImmediatePropagation();
            e.preventDefault();    
            
            $('.js_spinner').jmspinner('large');
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                dataType: 'JSON',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    email: $('.popup-login input[name=email]').val(),
                    password: $('.popup-login input[name=password]').val()
                },
                success: function (msg) {
                    if (msg.status == true) {
                        $('.popup-login input[name=email]').val('')
                        $('.popup-login input[name=password]').val('')
                        $('.popup-login div.error').html('')
                        location.href = "{{ url(config('auth.auth_redirect_to')) }}"
                    }else{
                        $('.js_login_form [data-name="email"]').text(msg.errors);
                    }
                    $('.js_spinner').jmspinner(false);
                },
                error: function (jqXHR, exception) {
                    $('.js_spinner').jmspinner(false);
                }
            })
        })

        // register
        $(document).on('submit', '#reg_form', function (e) {
            e.stopImmediatePropagation();
            e.preventDefault();    
            $('.js_spinner').jmspinner('large');
            $.ajax({
                method: 'POST',
                url: "{{ route('register') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    email: $('.popup-registration input[name=email]').val(),
                    password: $('.popup-registration input[name=password]').val(),
                    password_confirmation: $('.popup-registration input[name=password_confirmation]').val(),
                    invited: $('.popup-registration input[name=invited]').val()
                },
                dataType: 'json',
                success: function (msg) {
                    if (msg.status = 'ok') {
                        $('.popup-registration input[name=email]').val('')
                        $('.popup-registration input[name=password]').val('')
                        $('.popup-registration input[name=password_confirmation]').val('')
                        $('.popup-registration input[name=invited]').val('')
                        $('.popup-registration div.error').html('')

                        location.href = "{{ url(config('auth.auth_redirect_to')) }}"
                    }
                    $('.js_spinner').jmspinner(false);
                },
                error: function (jqXHR, exception) {
                    $('.js_spinner').jmspinner(false);
                    if(exception == 'parseerror'){
                        location.reload;
                    }
                    
                    try {
                        var msg = JSON.parse(jqXHR.responseText);
                    } catch (e) {
                        location.reload;
                    }

                    $('.popup-registration div.error').html('')

                    try {
                        for (key in msg.errors) {
                            for (key2 in msg.errors[key]) {
                                $('.popup-registration div.error[data-name=' + key + ']').append('<p style="color: red;">' + msg.errors[key][key2] + '</p>')
                            }
                        }
                        
                    } catch (error) {
                        location.reload;
                    }

                }
            })

            return false
        })
        // cabinet chat
        $(document).on('submit', '.js_painter_admin_chat', function (e) {
            e.preventDefault();    
            e.stopImmediatePropagation();
            $('.js_spinner').jmspinner('large');

            var th = $(this);

            var order_id = $(this).data('id');
            var painter_msg = $(this).find('.painter_msg').val();

            $.ajax({
                method: 'POST',
                url: "{{ route('update_order_chat') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    order_id: order_id,
                    painter_msg: painter_msg,
                },
                dataType: 'json',
                success: function (msg) {
                    if (msg.status = 1) {
                        var you_text = 'Вы';
                        var d = new Date();
                        var curr_date = d.getDate();
                        var curr_month = d.getMonth() + 1;
                        var curr_year = d.getFullYear();

                        curr_month = curr_month+='';
                        if(curr_month.length == 1){
                            curr_month = '0'+curr_month;
                        }

                        var date = curr_year+'/'+curr_month+'/'+curr_date;

                        var text = painter_msg;
                        $('.comments__main__list').append(`
                        <li>
                            <div class="comment__user">${you_text}</div>
                            <div class="comment__date">${date}</div>
                            <div class="comment">${painter_msg}</div>
                        </li>
                        `);

                        th.find('.painter_msg').val("");

                    }
                    
                    $('.js_spinner').jmspinner(false);
                },
                error: function (jqXHR, exception) {
                    $('.js_spinner').jmspinner(false);
                    
                }
            })

            return false
        })
    })
</script>



<script>
    /*!
    * The Final Countdown for jQuery v2.2.0 (http://hilios.github.io/jQuery.countdown/)
    * Copyright (c) 2016 Edson Hilios
    */
    !function(a){"use strict";"function"==typeof define&&define.amd?define(["jquery"],a):a(jQuery)}(function(a){"use strict";function b(a){if(a instanceof Date)return a;if(String(a).match(g))return String(a).match(/^[0-9]*$/)&&(a=Number(a)),String(a).match(/\-/)&&(a=String(a).replace(/\-/g,"/")),new Date(a);throw new Error("Couldn't cast `"+a+"` to a date object.")}function c(a){var b=a.toString().replace(/([.?*+^$[\]\\(){}|-])/g,"\\$1");return new RegExp(b)}function d(a){return function(b){var d=b.match(/%(-|!)?[A-Z]{1}(:[^;]+;)?/gi);if(d)for(var f=0,g=d.length;f<g;++f){var h=d[f].match(/%(-|!)?([a-zA-Z]{1})(:[^;]+;)?/),j=c(h[0]),k=h[1]||"",l=h[3]||"",m=null;h=h[2],i.hasOwnProperty(h)&&(m=i[h],m=Number(a[m])),null!==m&&("!"===k&&(m=e(l,m)),""===k&&m<10&&(m="0"+m.toString()),b=b.replace(j,m.toString()))}return b=b.replace(/%%/,"%")}}function e(a,b){var c="s",d="";return a&&(a=a.replace(/(:|;|\s)/gi,"").split(/\,/),1===a.length?c=a[0]:(d=a[0],c=a[1])),Math.abs(b)>1?c:d}var f=[],g=[],h={precision:100,elapse:!1,defer:!1};g.push(/^[0-9]*$/.source),g.push(/([0-9]{1,2}\/){2}[0-9]{4}( [0-9]{1,2}(:[0-9]{2}){2})?/.source),g.push(/[0-9]{4}([\/\-][0-9]{1,2}){2}( [0-9]{1,2}(:[0-9]{2}){2})?/.source),g=new RegExp(g.join("|"));var i={Y:"years",m:"months",n:"daysToMonth",d:"daysToWeek",w:"weeks",W:"weeksToMonth",H:"hours",M:"minutes",S:"seconds",D:"totalDays",I:"totalHours",N:"totalMinutes",T:"totalSeconds"},j=function(b,c,d){this.el=b,this.$el=a(b),this.interval=null,this.offset={},this.options=a.extend({},h),this.instanceNumber=f.length,f.push(this),this.$el.data("countdown-instance",this.instanceNumber),d&&("function"==typeof d?(this.$el.on("update.countdown",d),this.$el.on("stoped.countdown",d),this.$el.on("finish.countdown",d)):this.options=a.extend({},h,d)),this.setFinalDate(c),this.options.defer===!1&&this.start()};a.extend(j.prototype,{start:function(){null!==this.interval&&clearInterval(this.interval);var a=this;this.update(),this.interval=setInterval(function(){a.update.call(a)},this.options.precision)},stop:function(){clearInterval(this.interval),this.interval=null,this.dispatchEvent("stoped")},toggle:function(){this.interval?this.stop():this.start()},pause:function(){this.stop()},resume:function(){this.start()},remove:function(){this.stop.call(this),f[this.instanceNumber]=null,delete this.$el.data().countdownInstance},setFinalDate:function(a){this.finalDate=b(a)},update:function(){if(0===this.$el.closest("html").length)return void this.remove();var b,c=void 0!==a._data(this.el,"events"),d=new Date;b=this.finalDate.getTime()-d.getTime(),b=Math.ceil(b/1e3),b=!this.options.elapse&&b<0?0:Math.abs(b),this.totalSecsLeft!==b&&c&&(this.totalSecsLeft=b,this.elapsed=d>=this.finalDate,this.offset={seconds:this.totalSecsLeft%60,minutes:Math.floor(this.totalSecsLeft/60)%60,hours:Math.floor(this.totalSecsLeft/60/60)%24,days:Math.floor(this.totalSecsLeft/60/60/24)%7,daysToWeek:Math.floor(this.totalSecsLeft/60/60/24)%7,daysToMonth:Math.floor(this.totalSecsLeft/60/60/24%30.4368),weeks:Math.floor(this.totalSecsLeft/60/60/24/7),weeksToMonth:Math.floor(this.totalSecsLeft/60/60/24/7)%4,months:Math.floor(this.totalSecsLeft/60/60/24/30.4368),years:Math.abs(this.finalDate.getFullYear()-d.getFullYear()),totalDays:Math.floor(this.totalSecsLeft/60/60/24),totalHours:Math.floor(this.totalSecsLeft/60/60),totalMinutes:Math.floor(this.totalSecsLeft/60),totalSeconds:this.totalSecsLeft},this.options.elapse||0!==this.totalSecsLeft?this.dispatchEvent("update"):(this.stop(),this.dispatchEvent("finish")))},dispatchEvent:function(b){var c=a.Event(b+".countdown");c.finalDate=this.finalDate,c.elapsed=this.elapsed,c.offset=a.extend({},this.offset),c.strftime=d(this.offset),this.$el.trigger(c)}}),a.fn.countdown=function(){var b=Array.prototype.slice.call(arguments,0);return this.each(function(){var c=a(this).data("countdown-instance");if(void 0!==c){var d=f[c],e=b[0];j.prototype.hasOwnProperty(e)?d[e].apply(d,b.slice(1)):null===String(e).match(/^[$A-Z_][0-9A-Z_$]*$/i)?(d.setFinalDate.call(d,e),d.start()):a.error("Method %s does not exist on jQuery.countdown".replace(/\%s/gi,e))}else new j(this,b[0],b[1])})}});
</script>

<script>
    $(document).on('click', '.js_fb', function(e){
        e.preventDefault();
        $('[data-count="fb"]').trigger('click');
    });

    $(document).on('click', '.js_od', function(e){
        e.preventDefault();
        $('[data-count="odkl"]').trigger('click');
    });

    $(document).on('click', '.js_ig', function(e){
        e.preventDefault();
    });

    $(document).ready(function () {
            
            if($('.js__count').length){
                $('.js__count').each(function(el){
                    var $this = $(this)
                    var end = $this.data('end').split(' ')[0];                   
                    $this.countdown(end, function(event) {              
                        $this.find('.js_dd span').text(event.strftime('%D'));
                        $this.find('.js_hh span').text(event.strftime('%H'));
                        $this.find('.js_mm span').text(event.strftime('%M'));
                        $this.find('.js_ss span').text(event.strftime('%S'));
                    });
                });
            }


            $(document).on('submit', '.js_send_future_art', function (e) {
                e.preventDefault();

                let form_data = new FormData();
                form_data.append('name', $(this).find('.js_name').val() );
                form_data.append('tel',  $(this).find('.js_phone').val());
                form_data.append('email', $(this).find('.js_email').val());
                form_data.append('is_canvas_collage', 1);
                
                var is_return = 0;
                let TotalImages = $('#js_file_images')[0].files.length; 
                let images = $('#js_file_images')[0]; 
                for (let i = 0; i < TotalImages; i++) {                   
                    form_data.append('images[]', images.files[i]);
                }
                
                form_data.append('TotalImages', TotalImages);
                $('.js_spinner').jmspinner('large');

                $.ajax({
                    type: 'post',
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url:'{{ route('add_future_art') }}',
                    data: form_data,
                    success: function (response) {
                    $('.js_spinner').jmspinner(false);
                    if (response) {
                        if(response.message == 'Invalid filesize or extension.'){
                            $('.popup-inv-size').addClass('active');
                        }

                        if(response.message == 'Media missing.'){
                            $('.popup-inv-foto').addClass('active');
                        }

                        if(response.message == 'Success'){
                            var msg = $('body').data('suc_send');
                            $('.popup-act-act').find('.p__mod__title').children('span').eq(0).text(msg);
                            $('.popup-act-act').addClass('active');
                        }
                    }       
                },
                error: function (error) {
                    $('.js_spinner').jmspinner(false);
                    console.log(error);
                }
                });
                
            });

        });

</script>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5PBM79X"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NQZGV37"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
