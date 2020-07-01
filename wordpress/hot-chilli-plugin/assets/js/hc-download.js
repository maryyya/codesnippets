$( document ).ready(function() {
    /**
     * If すべて選択 checkbox is checked
     */
    $('#all_select').on('click', all_header_click);

    /**
     * If すべて解除 checkbox is checked
     */
    $('#all_delete').on('click', all_delete_click);

    /**
     * If select own checkbox is checked
     */
    $('.u_mem_dl_area').on('click', 'input[name=multi_select]', select_download);

    /**
     * If x button is clicked on left list files
     */
    $('.c_side_selects').on('click', '.c_side_selects_list li .c_side_selects_delete', remove_file);

    /**
     * If download single button is clicked
     */
    $('.u_mem_dl_area').on('click', '.download_single_btn', download_single);

    /**
     * If download multpile button is clicked
     */
    $('.download_multiple_btn').on('click', download_multiple);

    /**
     * If search button is clicked
     */
    $('.download_search').on('click', search);

    /**
     * If pagination is clicked
     */
    $('.hc-pagination').on('click', 'a', search);

    /**
     * If each file is clicked
     */
    $('.u_mem_dl_area').on('click', '.show_file_detail', set_session_file_list);

    /**
     * If each file is clicked
     * from the sidebar
     */
    $('.c_side_selects_list').on('click', '.show_file_detail', set_session_file_list);

    // set the file list on the sidebar on download page
    set_download_file_list();

    /***************************************************************/
    /*                                                             */
    /*               Single page for Download                      */
    /*                                                             */
    /***************************************************************/

    // set the file list on the sidebar on single page
    set_single_file_list();

    /**
     * If download single button is clicked on single page
     */
    $('.download_single_page_btn').on('click', download_single);

    /**
     * If select own checkbox is checked on single page
     */
    $('.dl_single_page input[name=multi_select]').on('click', select_download);

    /**
     * If search button on single page is clicked
     */
    $('.single_search').on('click', single_search);
});

/**
 * If すべて選択 checkbox is checked
 */
function all_header_click() {
    $('.hc-download-error').css('display', 'none');
    $('#all_select').removeAttr('disabled');
    $('#all_delete').prop('checked', false);
    $('#all_delete').removeAttr('disabled');
    var site_url = $('input[name=site_url]').val();

    if ($('#all_select').is(':checked')) {
        $('#all_select').prop('checked', true);
        $('.u_mem_dl_select input[name=multi_select]').prop('checked', true);
        if ($('input[name=multi_select]').not(':checked').length < 1) {
            $('#all_select').attr('disabled', 'disabled');
        }
    } else {
        $('#all_select').prop('checked', true);
        if ($('input[name=multi_select]').not(':checked').length < 1) {
            $('#all_select').attr('disabled', 'disabled');
        }
        return;
    }

    var selectedlist = [];
    var newsessionlist = [];
    var sessionlist = JSON.parse(sessionStorage.getItem('download_list'));

    // for list
    $('.c_side_selects_list li').each(function() {
        selectedlist.push($(this).attr('class'));
    });

    // for adding into the list if it is checked
    if ($('input[name=multi_select]').not(':checked').length < 1) {
        var selected = [];

        $('.u_mem_dl_area_child_hc').find('.u_mem_dl_select input:checked').each(function() {
            selected.push($(this).attr('value'));
        });

        var content = '';

        for (var i=0; i<selected.length; i++) {
            var split = selected[i].split('|');
            var lidata = 'child'+split[0];
            newsessionlist.push(selected[i]);

            if ($.inArray(lidata, selectedlist) < 0) {
                var data = selected[i].split('|');
                content += '<li class="child'+data[0]+'" data="'+data[0]+'|'+data[1]+'|'+data[2]+'">';
                content += '<svg class="c_icon is_icon_file">';
                content += '<use xlink:href="#icon_file"></use>';
                content += '</svg>';
                content += '<a href="'+site_url+'/member/download/'+data[0]+'" data="more_select'+data[0]+'" class="show_file_detail">'+escapeHtml(data[2])+'</a>';
                content += '<span class="c_side_selects_delete">×</span>';
                content += '</li>';
            }
        }

        $('.c_side_selects_list').append(content);
    }

    // this is if page is reloaded
    if (sessionlist !== null) {
        if (selectedlist.length < 1) {
            for(var i=0; i<sessionlist.length; i++) {
                var split = sessionlist[i].split('|');
                var lidata = 'child'+split[0];
                if ($.inArray(lidata, selectedlist) < 0) {
                    newsessionlist.push(sessionlist[i]);
                }
            }
        } else {
            for(var i=0; i<sessionlist.length; i++) {
                var split = sessionlist[i].split('|');
                var lidata = 'child'+split[0];
                newsessionlist.push(sessionlist[i]);
            }
        }
    } else {
        // newsessionlist.push(selected[i]);
    }

    var unique = newsessionlist.filter( only_unique );

    sessionStorage.setItem('download_list', JSON.stringify(unique));
}

/**
 * If すべて解除 checkbox is checked
 */
function all_delete_click() {
    $('.hc-download-error').css('display', 'none');
    $('#all_select').removeAttr('disabled');
    $('#all_select').prop('checked', false);
    $('#all_delete').removeAttr('disabled');

    if($('#all_delete').is(':checked')) {
        $('.u_mem_dl_select input[name=multi_select]').prop('checked', false);
        if ($('input[name=multi_select]').not(':checked').length > 0) {
            $('#all_delete').attr('disabled', 'disabled');
        }
    }

    // if no checked checkbox
    if ( typeof val === 'undefined' ) {
        var selected = [];
        var notchecked = [];
        var newsessionlist = [];

        // for list
        $('.c_side_selects_list li').each(function() {
            var value = $(this).attr('data');
            var split = value.split('|');
            selected.push(split[0]+'|'+split[1]);
        });

        if ($('input[name=multi_select]').not(':checked').length > 0) {
            $('.u_mem_dl_area_child_hc').find('.u_mem_dl_select input').not(':checked').each(function() {
                var classunchecked = $(this).attr('value');
                var split = classunchecked.split('|');
                notchecked.push(classunchecked);
                $('.c_side_selects_list .child'+split[0]).remove();
            });
        }

        var sessionlist = JSON.parse(sessionStorage.getItem('download_list'));
        if (sessionlist !== null) {
            for(var i=0; i<sessionlist.length; i++) {
                if ($.inArray(sessionlist[i], notchecked) < 0) {
                    newsessionlist.push(sessionlist[i]);
                }
            }
        }

        sessionStorage.setItem('download_list', JSON.stringify(newsessionlist));

        // Clear session storage
        // if (typeof(Storage) !== "undefined") {
        //     if (typeof(sessionStorage.getItem('download_list')) !== "undefined") {
        //         sessionStorage.clear();
        //         sessionStorage.removeItem('download_list');
        //     }
        // }
        return;
    }
}

/**
 * If select own checkbox is checked
 * This will put the list on the left.
 */
function select_download() {
    $('#all_select').removeAttr('disabled');
    $('#all_delete').removeAttr('disabled');

    $('#all_select').prop('checked', false);
    $('#all_delete').prop('checked', false);
    $('.hc-download-error').css('display', 'none');
    var val = $(this).attr('value');
    var value = val.split('|');
    var site_url = $('input[name=site_url]').val();
    var newsessionlist = [];

    if ($(this).is(':checked')) {
        var con = '';
        con += '<li class="child'+value[0]+'" data="'+value[0]+'|'+value[1]+'|'+value[2]+'">';
        con += '<svg class="c_icon is_icon_file">';
        con += '<use xlink:href="#icon_file"></use>';
        con += '</svg>';
        con += '<a href="'+site_url+'/member/download/'+value[0]+'" data="more_select'+value[0]+'" class="show_file_detail">'+escapeHtml(value[2])+'</a>';
        con += '<span class="c_side_selects_delete">×</span>';
        con += '</li>';
        $('.c_side_selects_list').append(con);

        var identity = $('input[name=page_identity]').val();
        // check if on single page for download
        if ((typeof identity !== 'undefined' && identity === 'single')
            || (typeof identity !== 'undefined' && identity === 'download')) {
            var newsessionlist = JSON.parse(sessionStorage.getItem('download_list'));
            if (newsessionlist !== null) {
                newsessionlist.push(val);
            } else {
                var newsessionlist = [];
                newsessionlist.push(val);
            }
        }

        var checkedMultiselect = $('input[name=multi_select]:checked').length;
        var totalMultiselect = $('input[name=multi_select]').length;
        if ( checkedMultiselect === totalMultiselect ) {
            $('#all_select').prop('checked', true);
            $('#all_select').attr('disabled', 'disabled');
        } else {
            $('#all_select').prop('checked', false);
        }
    } else {
        $('.c_side_selects_list').find('li.child'+value[0]).remove();
        var newsessionlist = [];
        var sessionlist = JSON.parse(sessionStorage.getItem('download_list'));
        if (sessionlist !== null) {
            for(var i=0; i<sessionlist.length; i++) {
                if (sessionlist[i] !== val) {
                    newsessionlist.push(sessionlist[i]);
                }
            }
        }
    }

    sessionStorage.setItem('download_list', JSON.stringify(newsessionlist));
}

/**
 * Remove the file from the list
 * on the left side if it is
 * checked.
 */
function remove_file() {
    $('.hc-download-error').css('display', 'none');

    var id = $(this).closest('li').find('a').attr('data');
    $('.u_mem_dl_select input[id='+id+']').prop('checked', false);
    $(this).closest('li').remove();

    var newsessionlist = [];
    var sessionlist = JSON.parse(sessionStorage.getItem('download_list'));
    if (sessionlist !== null) {
        for(var i=0; i<sessionlist.length; i++) {
            var item = sessionlist[i].split('|');
            if ('more_select'+item[0] !== id) {
                newsessionlist.push(sessionlist[i]);
            }
        }
    }

    sessionStorage.setItem('download_list', JSON.stringify(newsessionlist));
}

/**
 * Download single file
 */
function download_single() {
    var param = {
        action   : hc_download_ajax.hc_ajax_action,
        loginid  : $('input[name=loginid]').val(),
        file_data: $(this).attr('data'),
        type     : 'download-single'
    };

    $('.hc-download-error').css('display', 'none');

    // check the loginid
    if (param.loginid.length < 1 || typeof param.loginid === 'undefined') {
        $('.hc-download-error').css('display', 'block');
        $('.hc-download-error p').html('ログインIDが定義されていません。');
        return false;
    }

    // check value of the file
    if (param.file_data.length < 1 || typeof param.file_data === 'undefined') {
        $('.hc-download-error').css('display', 'block');
        $('.hc-download-error p').html('ファイルデータがありません。');
        return false;
    }

    // check the file data has two data
    var data_split = param.file_data.split('|');
    if (data_split.length !== 2) {
        $('.hc-download-error').css('display', 'block');
        $('.hc-download-error p').html('ファイルデータが完成していません。');
    }

    // check if file data is not empty
    if (data_split[0].length < 1 || data_split[1] < 1) {
        $('.hc-download-error').css('display', 'block');
        $('.hc-download-error p').html('ファイルデータがありません。');
    }

    $.ajax({
        url     : hc_download_ajax.ajax_url,
        type    : 'POST',
        dataType: 'json',
        data    : param,
        beforeSend: function() {
            $('#loaderMemberModal').css('display', 'block');
        },
        success : function(response) {
            if (response.success !== true) {
                $('#loaderMemberModal').css('display', 'none');
                $('.hc-download-error').css('display', 'block');
                $('.hc-download-error p').html('サーバーエラー');
                return false;
            }

            var res = JSON.parse(response.data);
            if (res.status === 'ng') {
                $('#loaderMemberModal').css('display', 'none');
                $('.hc-download-error').css('display', 'block');
                $('.hc-download-error p').html(res.msg.replace(/\n/g,"<br />"));
            } else {
                $('.hc-download-error').css('display', 'none');
                $('.hc-download-error p').html('');

                var content = '<input type="hidden" name="file_id" value="'+res.data.id+'">';
                content += '<input type="hidden" name="type" value="file-single">';
                $('#download-form').html(content);
                var form = document.getElementById('download-form');

                form.action = res.data.home_url+'/filedownload';
                $('#download-form').submit();
                $('#loaderMemberModal').css('display', 'none');
            }
        },
        error   : function() {
            $('#loaderMemberModal').css('display', 'none');
        }
    });
}

/**
 * Download multiple files
 */
function download_multiple() {
    $('.hc-download-error').css('display', 'none');

    var selected = [];

    // get the checked members
    $('.c_side_selects_list li').each(function() {
        selected.push($(this).attr('data'));
    });

    var param = {
        action   : hc_download_ajax.hc_ajax_action,
        loginid  : $('input[name=loginid]').val(),
        file_data: selected,
        type     : 'download-multiple'
    };

    // if list is empty
    if (param.file_data.length < 1) {
        $('.hc-download-error').css('display', 'block');
        $('.hc-download-error p').html('ファイルを選択してください。');
        return false;
    }

    $.ajax({
        url     : hc_download_ajax.ajax_url,
        type    : 'POST',
        dataType: 'json',
        data    : param,
        beforeSend: function() {
            $('#loaderMemberModal').css('display', 'block');
        },
        success : function(response) {
            if (response.success !== true) {
                $('#loaderMemberModal').css('display', 'none');
                $('.hc-download-error').css('display', 'block');
                $('.hc-download-error p').html('サーバーエラー');
                return false;
            }

            var res = JSON.parse(response.data);
            if (res.status === 'ng') {
                $('#loaderMemberModal').css('display', 'none');
                $('.hc-download-error').css('display', 'block');
                $('.hc-download-error p').html(res.msg.replace(/\n/g,"<br />"));
            } else {
                $('.hc-download-error').css('display', 'none');
                $('.hc-download-error p').html('');
                $('#loaderMemberModal').css('display', 'none');

                var content = '<input type="hidden" name="file_id" value="'+res.data.id+'">';
                content += '<input type="hidden" name="type" value="file-multiple">';
                $('#download-form').html(content);
                var form = document.getElementById('download-form');

                form.action = res.data.home_url+'/filedownload';
                $('#download-form').submit();
                $('#loaderMemberModal').css('display', 'none');
            }
        },
        error   : function() {
            $('#loaderMemberModal').css('display', 'none');
        }
    });
}

/**
 * Search the keyword and tag
 * This is also used for pagination
 */
function search() {
    $('.hc-search-error').removeClass('hc-search-show');
    $('.hc-search-error').css('display', 'none');
    $('.hc-pagination').css('display', 'none');
    $('.hc-download-error').css('display', 'none');
    $('.hc-download-error p').html('');

    var param = {
        action : hc_search_ajax.hc_ajax_action,
        search : $('input[name=search]').val(),
        tag    : $('#tag').val(),
        loginid: $('input[name=loginid]').val(),
        type   : 'search',
        limit  : $('input[name=limit]').val()
    };

    var numpg = $(this).attr('data');
    if (typeof numpg !== 'undefined') {
        param.numpg = numpg;
        param.curpg = $('.hc-current').text();
    }

    $.ajax({
        url     : hc_search_ajax.ajax_url,
        type    : 'POST',
        dataType: 'json',
        data    : param,
        beforeSend: function() {
            $('#loaderSearchModal').css('display', 'table');
            $('.u_mem_dl_area').css('display', 'none');
        },
        success : function(response) {
            if (response.success !== true) {
                $('#loaderSearchModal').css('display', 'none');
                $('.u_mem_dl_area').css('display', 'block');
                $('.hc-search-error').css('display', 'block');
                $('.hc-search-error p').html('サーバーエラー');
                $('.hc-search-error').addClass('hc-search-show');

                return false;
            }

            var res = JSON.parse(response.data);
            if (res.status === 'ng') {
                $('#loaderSearchModal').css('display', 'none');
                $('.hc-search-error').css('display', 'block');
                $('.hc-search-error p').html(res.msg.replace(/\n/g,"<br />"));
                $('.hc-search-error').addClass('hc-search-show');
                $('.u_mem_all_num').html('');
                $('#all_select').attr('disabled', 'disabled');
                $('#all_delete').attr('disabled', 'disabled');
            } else {
                $('#all_delete').prop('checked', false);
                $('#all_select').prop('checked', false);
                $('.hc-search-error').css('display', 'none');
                $('.hc-search-error p').html('');
                $('#loaderSearchModal').css('display', 'none');
                $('.hc-search-error').removeClass('hc-search-show');
                $('#all_select').removeAttr('disabled');
                $('#all_delete').removeAttr('disabled');

                $('.u_mem_all_num').html('全'+res.data.total+'件中'+res.data.searchdata.length+'件');
                var data    = res.data.searchdata;
                var total   = res.data.total;
                var list    = res.data.list;
                var content = '';
                content += '<input type="hidden" name="loginid" value="'+escapeHtml(res.data.loginid)+'">';
                content += '<input type="hidden" name="limit" value="'+escapeHtml(res.data.limit)+'">';
                content += '<div class="l_colmn02 l_column_wrap has_column_wrap_fill40 u_mab30 u_mem_dl_area_child_hc">';

                for (var i=0; i < data.length; i++ ) {
                    var mat40 = i>2?'u_mat40':'';

                    // give the content
                    content += '<div class="l_column has_column_pc4unit has_column_sp12unit has_column_padding40 '+mat40+'">';
                    content += '<div class="u_mem_dl_box">';
                    content += '<a href="javascript:void(0);">';
                    content += '<div class="l_column_wrap has_column_wrap_fill20">';
                    content += '<div class="u_left_box l_column has_column_pc4unit has_column_sp12unit has_column_padding20">';
                    content += '<p><img src="'+data[i].img_url+'" alt="pdf"></p>';
                    content += '</div>';
                    content += '<div class="u_right_box l_column has_column_pc8unit has_column_sp12unit has_column_padding20">';
                    content += '<p class="u_mem_dl_ttl">'+escapeHtml(data[i].title)+'</p>';
                    content += '<p class="u_mem_dl_tag">'+escapeHtml(data[i].tag)+'</p>';
                    content += '<p class="u_mem_dl_txt">'+escapeHtml(data[i].content)+'</p>';
                    content += '</div>';
                    content += '</div>';
                    content += '<p class="u_mem_dl_detail_link"></p></a>';
                    content += '</div>';
                    content += '<div class="u_mem_dl_btn">';
                    content += '<ul>';
                    content += '<li class="u_mem_dl_select">';
                    content += '<p class="c_checkbox_2">';
                    content += '<input id="more_select'+data[i].ID+'" class="selected" type="checkbox" name="multi_select" value="'+data[i].ID+'|'+data[i].file_mem_id+'|'+escapeHtml(data[i].title)+'">';
                    content += '<label for="more_select'+data[i].ID+'"></label>';
                    content += '</p>';
                    content += '</li>';
                    content += '<li>';
                    content += '<p class="u_mem_dl">';
                    content += '<a href="javascript:void(0);" class="c_btn c_btn_type05 download_btn download_single_btn" data="'+data[i].ID+'|'+data[i].file_mem_id+'">個別ダウンロード<span class="c_icon_wrap_txt is_icon_wrap_type01">';
                    content += '<svg class="c_icon is_icon_dl"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon_download"></use></svg></span>';
                    content += '</p>';
                    content += '</li>';
                    content += '</ul>';
                    content += '</div>';
                    content += '</div>';
                }
                content += '</div>';

                // add the content
                $('.u_mem_dl_area').html(content);
                $('.u_mem_dl_area').css('display', 'block');
                $('.u_mem_dl_area .u_mem_dl_box .u_right_box').tile(3);

                // sum of the array checking of the list
                var sumArr = [];

                // total of the data
                var totalArr = [];

                // the left list
                var selected = [];

                // for list
                $('.c_side_selects_list li').each(function() {
                    var value = $(this).attr('data');
                    var split = value.split('|');
                    selected.push(split[0]+'|'+split[1]);
                });

                // check the checkbox if it is on the list
                for (var i=0; i < data.length; i++ ) {
                    // total array
                    totalArr.push(i);

                    // get the value of the input
                    var lidata = data[i].ID+'|'+data[i].file_mem_id;

                    // get if value is in the list
                    sumArr.push($.inArray(lidata, selected));

                    // check the checkbox if it is in the list
                    if ($.inArray(lidata, selected) > -1) {
                        $('.u_mem_dl_area .l_colmn02').find('.l_column .u_mem_dl_box .u_mem_dl_select #more_select'+data[i].ID).prop('checked', true);
                    }
                }

                var checkedMultiselect = $('input[name=multi_select]:checked').length;
                var totalMultiselect = $('input[name=multi_select]').length;

                // sum of the checked data
                var sumRes = sumArr.reduce(getSum, 0);

                // sum of all total data
                var totalRes = totalArr.reduce(getSum, 0);

                // to check if all checkboxes are checked
                if ((sumRes === totalRes) || (checkedMultiselect === totalMultiselect)) {
                    $('#all_select').prop('checked', true);
                }

                // for pagination
                if ( total > 0 ) {
                    var total_page = Math.ceil( total / res.data.limit );

                    // no pagination needed
                    if (total_page === 1) {
                        return;
                    }

                    // if no pagination
                    if (res.data.numpg.length < 1) {
                        $('.hc-pagination').css('display', 'block');
                        var pg = '';
                        var page = total_page > 5 ? 5 : total_page;
                        pg += '<span class="page-numbers current">1</span>';

                        for(var i=1;i<page;i++) {
                            pg += '<a class="hc-pgnum" href="javascript:void(0);" data="'+(i+1)+'">'+(i+1)+'</a>';
                        }

                        if (total_page > 5) {
                            pg += '<span class="extend hc-pgnum hc-dots">…</span>';
                            pg += '<a class="page-numbers" href="javascript:void(0);" data="'+total_page+'">'+total_page+'</a>';
                        }

                        pg += '<a class="next page-numbers" href="javascript:void(0);" data="2">次へ &gt;</a>';

                        $('.hc-pagination').html(pg);
                    } else {
                        $('.hc-pagination').css('display', 'block');
                        var pg = '';
                        var numpg = parseInt(res.data.numpg);
                        var total_page = Math.ceil( total / res.data.limit );
                        var last_page = '';

                        // previous button
                        if ( numpg > 1 ) {
                            pg += '<a class="prev page-numbers" href="javascript:void(0);" data="'+(numpg-1)+'">&lt; 前へ</a>';
                        }

                        // previous pages
                        var page = total_page > 5 ? 5 : total_page;

                        // last three pages
                        var last_3 = total_page - 3;

                        if (numpg < 4 || total_page === 5) {
                            for (var i=1; i<=page; i++) {
                                if (numpg !== i) {
                                    pg += '<a class="page-numbers" href="javascript:void(0);" data="'+i+'">'+i+'</a>';
                                    continue;
                                }

                                pg += '<span aria-current="page" class="page-numbers current">'+i+'</span>';
                            }

                            if ( total_page > 5 ) {
                                pg += '<span class="extend hc-pgnum hc-dots">…</span>';
                                pg += '<a class="page-numbers" href="javascript:void(0);" data="'+total_page+'">'+total_page+'</a>';
                            }
                        } else if ( numpg > last_3 ) {
                            pg += '<a class="page-numbers" href="javascript:void(0);" data="1">1</a>';
                            pg += '<span class="extend hc-pgnum hc-dots">…</span>';

                            if (numpg !== total_page) {
                                for (var i=(numpg-1); i<=total_page; i++) {
                                    if (numpg !== i) {
                                        pg += '<a class="page-numbers" href="javascript:void(0);" data="'+i+'">'+i+'</a>';
                                        continue;
                                    }

                                    pg += '<span aria-current="page" class="page-numbers current">'+i+'</span>';
                                }
                            } else {
                                pg += '<a class="page-numbers" href="javascript:void(0);" data="'+(numpg-2)+'">'+(numpg-2)+'</a>';
                                pg += '<a class="page-numbers" href="javascript:void(0);" data="'+(numpg-1)+'">'+(numpg-1)+'</a>';
                                pg += '<span aria-current="page" class="page-numbers current">'+numpg+'</span>';
                            }
                        } else {
                            pg += '<a class="page-numbers" href="javascript:void(0);" data="1">1</a>';
                            pg += '<span class="extend hc-pgnum hc-dots">…</span>';
                            pg += '<a class="page-numbers" href="javascript:void(0);" data="'+(numpg-2)+'">'+(numpg-2)+'</a>';
                            pg += '<a class="page-numbers" href="javascript:void(0);" data="'+(numpg-1)+'">'+(numpg-1)+'</a>';
                            pg += '<span aria-current="page" class="page-numbers current">'+numpg+'</span>';
                            pg += '<a class="page-numbers" href="javascript:void(0);" data="'+(numpg+1)+'">'+(numpg+1)+'</a>';
                            pg += '<a class="page-numbers" href="javascript:void(0);" data="'+(numpg+2)+'">'+(numpg+2)+'</a>';
                            pg += '<span class="extend hc-pgnum hc-dots">…</span>';
                            pg += '<a class="page-numbers" href="javascript:void(0);" data="'+total_page+'">'+total_page+'</a>';
                        }


                        // next page
                        if (total_page > numpg) {
                            pg += '<a class="hc-next hc-pgnum" href="javascript:void(0);" data="'+(numpg+1)+'">次へ &gt;</a>';
                        }

                        $('.hc-pagination').html(pg);
                    }
                }
            }
        },
        error   : function() {
            $('#loaderMemberModal').css('display', 'none');
        }
    });
}

/**
 * Set session for the list on the side
 */
function set_session_file_list() {
    var selectedlist = [];
    // for list
    $('.c_side_selects_list li').each(function() {
        selectedlist.push($(this).attr('data'));
    });

    sessionStorage.setItem('download_list', JSON.stringify(selectedlist));
}

/**
 * Set the file list on the
 * sidebar on single page
 */
function set_single_file_list() {
    var identity = $('input[name=page_identity]').val();
    var site_url = $('input[name=site_url]').val();

    // check if on single page for download
    if (typeof identity === 'undefined' || identity !== 'single') {
        return;
    }

    // get the list
    var list = JSON.parse(sessionStorage.getItem('download_list'));

    // check if list is not set
    if (list === null) {
        return;
    }

    // check if list is empty
    if (list.length < 1) {
        return;
    }

    var content = '';
    for (var i=0; i<list.length; i++) {
        var split = list[i].split('|');
        var lidata = 'child'+split[0];
        var data = list[i].split('|');

        // check the checkbox if added on the list
        $('#more_select'+data[0]).prop('checked', true);

        content += '<li class="child'+data[0]+'" data="'+data[0]+'|'+data[1]+'|'+data[2]+'">';
        content += '<svg class="c_icon is_icon_file">';
        content += '<use xlink:href="#icon_file"></use>';
        content += '</svg>';
        content += '<a href="'+site_url+'/member/download/'+data[0]+'" data="more_select'+data[0]+'" class="show_file_detail">'+escapeHtml(data[2])+'</a>';
        content += '<span class="c_side_selects_delete">×</span>';
        content += '</li>';
    }

    $('.dl_single_body .c_side_selects_list').append(content);

    // set the list if when the user will go back to download page
    set_session_file_list();
}

/**
 * Set the file list on the sidebar
 * on the download page.
 */
function set_download_file_list() {
    var identity = $('input[name=page_identity]').val();
    var site_url = $('input[name=site_url]').val();

    // check if on download page
    if (typeof identity === 'undefined' || identity !== 'download') {
        return;
    }

    // get the list
    var list = JSON.parse(sessionStorage.getItem('download_list'));

    // check if list is not set
    if (list === null) {
        return;
    }

    // check if list is empty
    if (list.length < 1) {
        return;
    }

    var content = '';
    for (var i=0; i<list.length; i++) {
        var split = list[i].split('|');
        var lidata = 'child'+split[0];
        var data = list[i].split('|');

        // check the checkbox if added on the list
        $('#more_select'+data[0]).prop('checked', true);

        content += '<li class="child'+data[0]+'" data="'+data[0]+'|'+data[1]+'|'+data[2]+'">';
        content += '<svg class="c_icon is_icon_file">';
        content += '<use xlink:href="#icon_file"></use>';
        content += '</svg>';
        content += '<a href="'+site_url+'/member/download/'+data[0]+'" data="more_select'+data[0]+'" class="show_file_detail">'+escapeHtml(data[2])+'</a>';
        content += '<span class="c_side_selects_delete">×</span>';
        content += '</li>';
    }

    $('.c_side_selects_list').append(content);

    var checkedMultiselect = $('input[name=multi_select]:checked').length;
    var totalMultiselect = $('input[name=multi_select]').length;

    // check all select checkbox if everything is checked
    if (checkedMultiselect === totalMultiselect) {
        $('#all_select').prop('checked', true);
    }
}

/**
 * Search for single page
 */
function single_search() {
    var site_url = $('input[name=site_url]').val();
    var param = {
        search : $('input[name=search]').val(),
        tag    : $('#tag').val(),
        from   : 'single'
    };

    var content = '';
    content += '<input type="hidden" name="single_search" value="'+param.search+'">';
    content += '<input type="hidden" name="single_tag" value="'+param.tag+'">';
    content += '<input type="hidden" name="single_from" value="'+param.from+'">';

    $('#single-form').html(content);

    var form = document.getElementById('single-form');
    form.action = site_url+'/member/download/';
    $('#single-form').submit();
}

/**
 * Escape html
 *
 * @param  string text unescaped text
 * @return string      escaped text
 */
function escapeHtml(text) {
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };

  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

/**
 * Get the sum of array
 *
 * @param  int a value from the array
 * @param  int b value from the array
 * @return int   sum of the values from array.
 */
function getSum(a, b) {
    return a + b;
}

/**
 * Remove duplicate value inside array
 *
 * @param  string value value to be compared
 * @param  string index
 * @param  string self
 * @return string
 */
function only_unique(value, index, self) {
    return self.indexOf(value) === index;
}

