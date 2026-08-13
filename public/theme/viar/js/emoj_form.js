$(document).on('submit', '#q__form', function (e) {
    e.preventDefault();

    const th = $(this);

    let step0 = th.find('input[name=step0]:checked').val() ?? null;
    let dest = th.find('input[name=dest]:checked').val() ?? null;

    let formData = new FormData(this);
    formData.append('email', th.find('input[name=email]').val());
    formData.append('phone', th.find('input[name=phone]').val());
    formData.append('step0', step0);
    formData.append('step1', th.find('input[name=step1][checked]').val());
    formData.append('step2', th.find('input[name=step2][checked]').val());
    formData.append('step3', th.find('input[name=step3][checked]').val());
    formData.append('dest', dest);
    console.log(
        th,
        th.find('input[name=step1]:checked'),
        th.find('input[name=step2]:checked'),
        th.find('input[name=step3]:checked')
    )

    formData.append('images', $('#file_images_upl')[0].files[0]);

    th.find('.loading-bar').fadeIn();
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        contentType: false,
        processData: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        success: function (msg) {
            if (msg.message === 'success') {
                $('.kviz-thanks__parent').show().addClass('quiz-thanks__show');
                ht.find('.loading-bar').fadeOut();
            }
        },
        error: function (response) {
        }
    })


})
