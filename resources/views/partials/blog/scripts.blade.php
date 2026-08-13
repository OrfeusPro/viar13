<script>
    var truncateString = function (str, num) {
        if (str.length <= num) {
            return str
        }
        return str.slice(0, num) + '...';
    }

    var replaceAll = function(str, find, replace) {
        return str.replace(new RegExp(find, 'g'), replace);
    }

    var page = 0;
    $('.js_load_more').click(function(e){
        e.preventDefault();
        page++;

        var more = $('.js_blog_arts').data('more');
        var cur_url = $('.js_blog_arts').data('url');

        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url:'{{ route('load_ajax_posts') }}',
            data: { page: page },
            success: function (response) {
                if (response) {
                        response.forEach(el => {
                            let short_text = truncateString(el.text, 150);
                            let img_url = el.image.replace(/\\/g, "/");

                            let short_text_clear = short_text.replace(/(<([^>]+)>)/gi, "");
                            
          
                            $('.js_blog_arts').append(`
                            <div class="blog-item">
                                <div class="img">
                                    <img src="/storage/${img_url}">
                                    </div>
                                <h3 class="b_post__title">${el.title}</h3>
                                <p>${short_text_clear}</p>
                                <a href="${cur_url}/${el.slug}"><span>${more}</span></a>
                                </p>
                            </div>
                            `);
                        });
                    
                }       
            },

        });
    });

</script>

<script src="{{ asset('js/jquery.mCustomScrollbar.min.js') }}"></script>
<script src="{{ asset('js/slick.min.js') }}"></script>
<script src="{{ asset('js/blog.min.js') }}?v=36"></script>