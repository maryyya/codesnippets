jQuery(function($){
    /*
     * Select/Upload image(s) event
     */
    $('body').on('click', '.hc_upload_file_button', function(e){
        e.preventDefault();

            var button = $(this),
                custom_uploader = wp.media({
            title: 'ファイルを挿入する',
            library : {
                // uncomment the next line if you want to attach image to the current post
                // uploadedTo : wp.media.view.settings.post.id,
                type : [
                    'application/pdf'
                    , 'application/zip'
                    , 'application/x-zip'
                    , 'application/x-tar'
                    , 'application/rar'
                    , 'application/x-7z-compressed'
                    , 'application/msword'
                    , 'application/vnd.ms-powerpoint'
                    , 'application/vnd.ms-excel'
                    , 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    , 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    , 'application/vnd.ms-excel.sheet.macroEnabled.12'
                    , 'application/vnd.ms-excel.sheet.binary.macroEnabled.12'
                    , 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
                    , 'text/plain'
                    , 'text/csv'
                    , 'text/tab-separated-values'
                    , 'text/html'
                    , 'image'
                ]
            },
            button: {
                text: 'このファイルを使用する' // button label text
            },
            multiple: false // for multiple image selection set to true
        }).on('select', function() { // it also has "open" and "close" events
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            var content = '';
            content += '<div class="hc-thumbnail">';
            content += '<div class="hc-centered">';
            content += '<img src="'+attachment.icon+'">';
            content += '</div>';
            content += '<div class="hc-filename">';
            content += '<div>'+attachment.filename+'</div>';
            content += '</div>';
            content += '</div>';
            $(button).removeClass('button').html(content).next().val(attachment.id).next().show();
            /* if you sen multiple to true, here is some code for getting the image IDs
            var attachments = frame.state().get('selection'),
                attachment_ids = new Array(),
                i = 0;
            attachments.each(function(attachment) {
                attachment_ids[i] = attachment['id'];
                console.log( attachment );
                i++;
            });
            */
        })
        .open();
    });

    /*
     * Remove image event
     */
    $('body').on('click', '.hc_remove_image_button', function(){
        $(this).hide().prev().val('').prev().addClass('button').html('ファイルを選択');
        return false;
    });

});