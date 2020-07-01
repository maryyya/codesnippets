jQuery(function($) {
    $('.edit').on('click', search);
    $('.calendar-display').on('click', '.before-edit a', editBefore);
    $('.calendar-display').on('click', '.next-edit a', editNext);
    $('.calendar-display').on('click', '.tsuika', submitMenu);
});

/**
 * This will submit the calendar input
 */
function submitMenu() {
    var info  = [];
    var mon   = [];
    var data  = [];
    var param = {
        'term_id': jQuery('input[name=term_id]').val(),
        'site_url': jQuery('input[name=site_url]').val(),
        'dir'     : jQuery('input[name=plugin_dir]').val(),
        'type'    : 'restaurant_add'
    };

    var weekday = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    var weekdays = {
        'Mon': [],
        'Tue':[],
        'Wed':[],
        'Thu':[],
        'Fri':[],
        'Sat':[],
        'Sun':[],
    };

    for (var j=0; j<weekday.length; j++) {
        info[weekday[j]] = escapeHtml(jQuery('#info_'+j).val());
    }
    param.info = jQuery.extend({}, info);

    param.weekdays = {
        'Mon': jQuery('input[name=Mon]').val(),
        'Tue': jQuery('input[name=Tue]').val(),
        'Wed': jQuery('input[name=Wed]').val(),
        'Thu': jQuery('input[name=Thu]').val(),
        'Fri': jQuery('input[name=Fri]').val(),
        'Sat': jQuery('input[name=Sat]').val(),
        'Sun': jQuery('input[name=Sun]').val(),
    };

    for(var z in weekdays) {
        for(var x=1; x<13;x++) {
            weekdays[z][x] = {
                'order': x,
                'menu' : escapeHtml(jQuery('.'+z+'').find('input[name=menu_'+x+']').val()),
                'label': escapeHtml(jQuery('.'+z+'').find('input[name=label_'+x+']').val()),
                'price': escapeHtml(jQuery('.'+z+'').find('input[name=price_'+x+']').val()),
            };
        }
    }


    param.data = weekdays;
    if (validate(param) === false) {
        alert('エラーがあります。 チェックしてください。');
        return;
    }

    jQuery.ajax({
        type: 'POST',
        url: param.site_url + '/ajax.php',
        data: param,
        success: function(res) {
            jQuery('#successModal').modal();
        },
        error: function(res) {
            errorConsole('Registration');
        }
    });
}

/**
 * Search and Display into the calendar
 */
function search() {
    var termid = jQuery('#term-search').val();
    var term = jQuery('#term-search :selected').text();
    var param = {
        'term_id'     : typeof termid === 'undefined' ? jQuery('#term_id').val():termid,
        'term'        : term.length > 0 ? term:jQuery('#term').val(),
        'site_url'    : jQuery('input[name=site_url]').val(),
        'dir'         : jQuery('input[name=plugin_dir]').val(),
        'display_type': 'search',
        'type': 'restaurant_search'
    };
    if (validate(param) === false) {
        return;
    }

    jQuery.ajax({
        type: 'POST',
        url: param.site_url + '/ajax.php',
        data: param,
        beforeSend: function() {
            jQuery('.overlay').html('<div class="loader"><img src="'+param.dir+'assets/img/loader.gif" alt="loader"></div>');
            jQuery('.overlay').removeClass('display-none');
            jQuery('.calendar-display').addClass('display-none');
        },
        success: function(res) {
            jQuery('.overlay').addClass('display-none');
            editcalendarCb(res);
        },
        error: function(res) {
            errorConsole('Searching the calendar');
        }
    });
}

/**
 * Search and Display into the calendar
 * by pressing the next button
 */
function editNext() {
    var term_search = jQuery('#term-search').val();
    var term_search_sel = jQuery('#term-search :selected').val();
    var term = typeof term_search === 'undefined' ? jQuery('input[name=term_id]').val() : jQuery('#term-search').val();
    var term_name = typeof term_search_sel === 'undefined' ? jQuery('input[name=term_name]').val() : jQuery('#term-search :selected').text();
    var param = {
        'term_id'     : term,
        'term'        : term_name,
        'site_url'    : jQuery('input[name=site_url]').val(),
        'dir'         : jQuery('input[name=plugin_dir]').val(),
        'display_type': 'search',
        'nav_type'    : 'next',
        'monday'      : jQuery('input[name=Mon]').val(),
        'sunday'      : jQuery('input[name=Sun]').val(),
        'type'        : 'restaurant_search'
    };

    if (validate(param) === false) {
        return;
    }

    jQuery.ajax({
        type: 'POST',
        url: param.site_url + '/ajax.php',
        data: param,
        success: function(res) {
            editcalendarCb(res);
        },
        error: function(res) {
            errorConsole('Next');
        }
    });
}

/**
 * Search and Display into the calendar
 */
function editBefore() {
    var term_search = jQuery('#term-search').val();
    var term_search_sel = jQuery('#term-search :selected').val();
    var term = typeof term_search === 'undefined' ? jQuery('input[name=term_id]').val() : jQuery('#term-search').val();
    var term_name = typeof term_search_sel === 'undefined' ? jQuery('input[name=term_name]').val() : jQuery('#term-search :selected').text();
    var param = {
        'term_id'     : term,
        'term'        : term_name,
        'site_url'    : jQuery('input[name=site_url]').val(),
        'dir'         : jQuery('input[name=plugin_dir]').val(),
        'display_type': 'search',
        'nav_type'    : 'before',
        'monday'      : jQuery('input[name=Mon]').val(),
        'sunday'      : jQuery('input[name=Sun]').val(),
        'type'        : 'restaurant_search'
    };

    if (validate(param) === false) {
        return;
    }

    jQuery.ajax({
        type: 'POST',
        url: param.site_url + '/ajax.php',
        data: param,
        success: function(res) {
            editcalendarCb(res);
        },
        error: function(res) {
            errorConsole('Before');
        }
    });
}

/**
 * Callback function from search
 * then displaying the data
 *
 * @param json res data from database
 */
function editcalendarCb(res) {
    var data    = '',
        dataend = '',
        html    = '',
        info    = '',
        weekly  = '',
        menu    = '';
    data   += '<div class="blockpad">';
    data   += '<div class="calendarnav">';
    data   += '<p>＜今週の'+res.data.term+'メニュー表＞</p>';
    weekly += '<input type="hidden" name="term_id" value="'+res.data.term_id+'">';
    weekly += '<input type="hidden" name="term_name" value="'+res.data.term_name_admin+'">';
    data   += '<ul>';
    data   += '<li class="before-edit"><a href="javascript:void(0);">前の週へ</a></li>';
    data   += '<li class="before-edit"><a href="javascript:void(0);"><img src="'+res.data.home_url+'/common/images/icons/icon_arrow_week_prev.png" alt="prev"></a></li>';
    data   += '<li class="next-edit"><a href="javascript:void(0);"><img src="'+res.data.home_url+'/common/images/icons/icon_arrow_week_next.png" alt="next"></a></li>';
    data   += '<li class="next-edit"><a href="javascript:void(0);">次の週へ</a></li>';
    data   += '</ul>';
    data   += '</div>';
    data   += '<table class="table_week mab10">';

    // by 7
    // date weekly ex. 11月27日(月)
    weekly += '<tr>';
    menu   += '<tr class="menu">';
    info   += '<tr class="info">';

    var extra    = res.data.res.title;
    if (extra.length <= 0) {
        html += data + weekly + info + menu;
        jQuery('.calendar-display').html(html);
        return;
    }

    var extraCnt = res.data.res.titleCount;
    if (extraCnt.length <= 0) {
        html += data + weekly + info + menu;
        jQuery('.calendar-display').html(html);
        return;
    }

    var resData  = res.data.res.menu;
    if (resData.length <= 0) {
        html += data + weekly + info + menu;
        jQuery('.calendar-display').html(html);
        return;
    }

    for (var i in extra) {
        if (jQuery.isEmptyObject(extra[i]) === false) {
            info += '<td>';
            info += '<textarea class="form-control" style="resize:none;width: 100%" id="info_'+i+'" rows=5 cols=15>'+extra[i][0]+'</textarea>';
            info += '</td>';
        } else {
            info += '<td>';
            info += '<textarea class="form-control" style="resize:none;width: 100%" id="info_'+i+'" rows=5 cols=15></textarea>';
            info += '</td>';
        }
    }

    jQuery.each(resData, function(index, value) {
        weekday = index.split('|');
        weekly += '<th>';
        weekly += weekday[0];
        weekly += '<input type="hidden" name="'+weekday[2]+'" value="'+weekday[1]+'">';
        weekly += '</th>';

        if (Object.keys(value).length > 0) {
            menu +=  '<td class="'+weekday[2]+'"><input type="hidden" name="'+weekday[2]+'" value="'+weekday[1]+'"><ul class="meal">';

            for (var j in value) {
                menu +=  ' <li><span class="bold"><input style="display:inline-block" class="form-control col-xs-4" type="text" name="label_'+j+'" value="'+value[j].data.label+'"></span><span class="error-label-'+j+' errors"></span><br>';
                menu +=  '<input style="display:inline-block" class="form-control col-xs-4" type="text" name="menu_'+j+'" value="'+value[j].data.menu+'"><span class="error-menu-'+j+' errors"></span><br>';
                menu +=  '<input style="width:65px; display:inline-block" class="form-control col-xs-4" type="text" name="price_'+j+'" value="'+value[j].data.price+'">&nbsp;円<span class="error-price-'+j+' errors"></span></li>';
            }

            menu += '</ul></td>';
        }


    });

    weekly += '</tr>';
    menu   += '</tr>';
    info   += '</tr>';

    dataend += '</table>';
    dataend += '<p style="text-align:left; margin-top:30px"><input type="button" class="btn btn-info tsuika" name="submit" value="登録する"><br>';
    dataend += '<p>※メニューは仕入れ等の関係から変更する場合があります。<br>';
    dataend += '※品切れの際はご容赦ください。</p>';

    html += data + weekly + info + menu + dataend;
    jQuery('.calendar-display').removeClass('display-none');
    jQuery('.calendar-display').html(html);
}


/**
 * Validate Param
 *
 * @param  array param input value
 * @return boolean error if there's something wrong with the input else false.
 */
function validate(param) {
    var res   = true;
    var avail = jQuery('#menutoadd').val();
    var cnt   = 0;
    if (typeof param.data !== 'undefined') {
        var weekdays = param.data;
        for(var z in weekdays) {
            for(var x=1; x<weekdays[z].length;x++) {
                if (weekdays[z][x].menu.length <= 0 && weekdays[z][x].label.length > 0 && weekdays[z][x].price.length > 0) {
                    jQuery('.'+z+' .error-menu-'+x).html('必須入力です。');
                    jQuery('.'+z+' .error-label-'+x).html('');
                    if (!isNum(weekdays[z][x].price)) {
                        jQuery('.'+z+' .error-price-'+x).html('価格は半角数字で入力してください。');
                        res = false;
                    } else {
                        jQuery('.'+z+' .error-price-'+x).html('');
                    }
                    res = false;

                } else if (weekdays[z][x].menu.length <= 0 && weekdays[z][x].label.length <= 0 && weekdays[z][x].price.length > 0) {
                    jQuery('.'+z+' .error-menu-'+x).html('必須入力です。');
                    jQuery('.'+z+' .error-label-'+x).html('必須入力です。');

                    if (!isNum(weekdays[z][x].price)) {
                        jQuery('.'+z+' .error-price-'+x).html('価格は半角数字で入力してください。');
                        res = false;
                    } else {
                        jQuery('.'+z+' .error-price-'+x).html('');
                    }

                    res = false;

                } else if (weekdays[z][x].menu.length > 0 && weekdays[z][x].label.length <= 0 && weekdays[z][x].price.length <= 0) {
                    jQuery('.'+z+' .error-label-'+x).html('必須入力です。');
                    jQuery('.'+z+' .error-price-'+x).html('必須入力です。');
                    jQuery('.'+z+' .error-menu-'+x).html('');
                    res = false;

                } else if (weekdays[z][x].menu.length > 0 && weekdays[z][x].label.length > 0 && weekdays[z][x].price.length <= 0) {
                    jQuery('.'+z+' .error-price-'+x).html('必須入力です。');
                    jQuery('.'+z+' .error-menu-'+x).html('');
                    jQuery('.'+z+' .error-label-'+x).html('');
                    res = false;

                } else if (weekdays[z][x].menu.length > 0 && weekdays[z][x].label.length <= 0 && weekdays[z][x].price.length > 0) {
                    jQuery('.'+z+' .error-label-'+x).html('必須入力です。');
                    jQuery('.'+z+' .error-menu-'+x).html('');

                    if (!isNum(weekdays[z][x].price)) {
                        jQuery('.'+z+' .error-price-'+x).html('価格は半角数字で入力してください。');
                        res = false;
                    } else {
                        jQuery('.'+z+' .error-price-'+x).html('');
                    }

                    res = false;

                } else if (weekdays[z][x].menu.length <= 0 && weekdays[z][x].label.length > 0 && weekdays[z][x].price.length <= 0) {
                    jQuery('.'+z+' .error-menu-'+x).html('必須入力です。');
                    jQuery('.'+z+' .error-price-'+x).html('必須入力です。');
                    jQuery('.'+z+' .error-label-'+x).html('');
                    res = false;

                } else if (weekdays[z][x].menu.length > 0 && weekdays[z][x].label.length > 0 && weekdays[z][x].price.length > 0) {
                    jQuery('.'+z+' .error-menu-'+x).html('');
                    jQuery('.'+z+' .error-label-'+x).html('');

                    if (!isNum(weekdays[z][x].price)) {
                        jQuery('.'+z+' .error-price-'+x).html('価格は半角数字で入力してください。');
                        res = false;

                    } else {
                        jQuery('.'+z+' .error-price-'+x).html('');
                    }
                } else {
                    jQuery('.'+z+' .error-menu-'+x).html('');
                    jQuery('.'+z+' .error-label-'+x).html('');
                    jQuery('.'+z+' .error-price-'+x).html('');
                    // res = true;
                }
            }
        }
    }

    if (typeof param.term_id !== 'undefined') {
        if (param.term_id.length <= 0) {
            jQuery('.error-term').html('選択されていません。');
            res = false;
        } else {
            jQuery('.error-term').html('');
        }
    }

    if (typeof param.datepicker !== 'undefined') {
        if (param.datepicker.length <= 0) {
            jQuery('.error-datepicker').html('選択されていません。');
            res = false;
        } else {
            jQuery('.error-datepicker').html('');
        }
    }

    if (typeof param.label !== 'undefined') {
        for (var i in param.label) {
            if (param.label[i].length <= 0) {
                jQuery('.error-label-'+i).html('必須入力です。');
                res = false;
            } else {
                jQuery('.error-label-'+i).html('');
            }
        }
    }

    if (typeof param.menu !== 'undefined') {
        for (var i in param.menu) {
            if (param.menu[i].length <= 0) {
                jQuery('.error-menu-'+i).html('必須入力です。');
                res = false;
            } else {
                jQuery('.error-menu-'+i).html('');
            }
        }
    }

    if (typeof param.price !== 'undefined') {
        for (var i in param.price) {
            if (param.price[i].length <= 0) {
                jQuery('.error-price-'+i).html('必須入力です。');
                res = false;
            } else if (!isNum(param.price[i])) {
                jQuery('.error-price-'+i).html('価格は半角数字で入力してください。');
                res = false;
            } else {
                jQuery('.error-price-'+i).html('');
            }
        }
    }

    return res;
}

/**
 * Check if number
 *
 * @param int num value
 * @param boolean if value is num true otherwhise false
 */
function isNum(num) {
  var regex = /^[0-9]+$/;
  var res = regex.exec(num) === null ? false : true;

  return res;
}

/**
 * Escapte html
 *
 * @param  string text unescaped text
 * @return strint      escaped text
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
 * Error Console Log
 *
 * @param  text param kind of error
 */
function errorConsole(param) {
    console.log('Error '+param);
}
