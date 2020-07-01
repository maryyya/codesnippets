jQuery(function($){
    /*
     * Select/Upload image(s) event
     */
    $('body').on('click', '.sie_upload_file_button', function(e){
        e.preventDefault();

            var button = $(this),
            custom_uploader = wp.media({
            title: 'ファイルを挿入する',
            library : {
                // uncomment the next line if you want to attach image to the current post
                // uploadedTo : wp.media.view.settings.post.id,
                type : [
                    // 'application/vnd.ms-excel',
                    // 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    // 'application/vnd.ms-excel.sheet.macroEnabled.12',
                    // 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
                    // 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
                    // 'application/vnd.ms-excel.template.macroEnabled.12',
                    // 'application/vnd.ms-excel.addin.macroEnabled.12',
                    'text/csv',
                ]
            },
            button: {
                text: 'このファイルを使用する' // button label text
            },
            multiple: false // for multiple image selection set to true
        }).on('select', function() { // it also has "open" and "close" events
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            var site_url = $('input[name=site_url]').val();
            var wp_uploads_filepath = $('input[name=wp_uploads_filepath]').val();
            var csv_filepath = attachment.url.replace(site_url+'/wp-content/uploads', wp_uploads_filepath);
            var content = '';
            content += '<div class="sie-thumbnail">';
            content += '<div class="sie-centered">';
            content += '<input type="hidden" name="shop_import_csv_file" value="'+csv_filepath+'">';
            content += '<img src="'+attachment.icon+'">';
            content += '</div>';
            content += '<div class="sie-filename">';
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
    $('body').on('click', '.sie_remove_image_button', function(){
        $(this).hide().prev().val('').prev().addClass('button').html('ファイルを選択');
        return false;
    });

});
