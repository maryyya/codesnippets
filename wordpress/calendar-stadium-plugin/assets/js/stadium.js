jQuery(function($) {
    /**
     * Date picker language change to Japanese
     */
    $.fn.datepicker.dates['ja'] = {
        days: ["日曜", "月曜", "火曜", "水曜", "木曜", "金曜", "土曜"],
        daysShort: ["日", "月", "火", "水", "木", "金", "土"],
        daysMin: ["日", "月", "火", "水", "木", "金", "土"],
        months: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
        monthsShort: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
        today: "今日",
        format: "yyyy/mm/dd",
        titleFormat: "yyyy年mm月",
        clear: "クリア"
    };

    /**
     * Datepicker function
     */
    $('#datepicker').datepicker({
        language      : 'ja',
        autoclose     :true,
        orientation   : 'bottom',
        todayHighlight: true,
        format        : "yyyy年mm月",
        startDate     : '2017年11月',
        minViewMode   : 1
    });

    /**
     * If submit button is clicked
     */
    $('.search-box .register-btn .register').on('click', register);
    $('.calendar-display').on('click', '.table_week td a', crud);
    $('.crud').on('click', crudSubmit);

    /**
     * If before and after button is clicked
     */
    $('.calendar-display').on('click', '.before-edit a', before);
    $('.calendar-display').on('click', '.next-edit a', next);

    /**
     * If chooses different stadium type
     */
    $('input[type=radio][name=stadium_type]').on('change', changeType);
    $('#datepicker').on('change', changeDate);
});

/**
 * Register the calendar.
 */
function register() {
    var placeId = jQuery('input[name=stadium_type]:checked').val();
    var param = {
        'site_url'  : jQuery('input[name=site_url]').val(),
        'plugin_dir': jQuery('input[name=plugin_dir]').val(),
        'place'     : typeof placeId === 'undefined' ? '' : placeId,
        'date'      : jQuery('#datepicker').val(),
        'type'      : 'display_calendar',
        'cal_type'  : placeId
    };

    if (validate(param) === false) {
        return;
    }

    jQuery.ajax({
        type: 'POST',
        url: param.site_url + '/ajax.php',
        data : param,
        beforeSend: function() {
            jQuery('.overlay').html('<div class="loader"><img src="'+param.plugin_dir+'assets/images/loader.gif" alt="loader"></div>');
            jQuery('.overlay').removeClass('display-none');
            jQuery('.calendar-display').addClass('display-none');
        },
        success: function(res) {
            jQuery('.overlay').addClass('display-none');
            registerCalendar(res);
        },
        error: function (res) {
            console.log('Error Register');
        }
    });
}

/**
 * Before button
 */
function before() {
    var stadium_type_checked = jQuery('input[name=stadium_type]:checked').val();
    var placeId = typeof stadium_type_checked === 'undefined' ? jQuery('input[name=stadium_type]').val() : jQuery('input[name=stadium_type]:checked').val();
    var param = {
        'site_url'  : jQuery('input[name=site_url]').val(),
        'plugin_dir': jQuery('input[name=plugin_dir]').val(),
        'place'     : placeId,
        'date'      : jQuery('input[name=before]').val(),
        'type'      : 'display_calendar',
        'cal_type'  : placeId
    };

    if (validate(param) === false) {
        return;
    }

    jQuery.ajax({
        type: 'POST',
        url: param.site_url + '/ajax.php',
        data: param,
        success: function(res) {
            registerCalendar(res);
        },
        error: function(res) {
            console.log('Error Before');
        }
    });
}

/**
 * Next button
 */
function next() {
    var stadium_type_checked = jQuery('input[name=stadium_type]:checked').val();
    var placeId = typeof stadium_type_checked === 'undefined' ? jQuery('input[name=stadium_type]').val() : jQuery('input[name=stadium_type]:checked').val();
    var param = {
        'site_url'  : jQuery('input[name=site_url]').val(),
        'plugin_dir': jQuery('input[name=plugin_dir]').val(),
        'place'     : placeId,
        'date'      : jQuery('input[name=next]').val(),
        'type'      : 'display_calendar',
        'cal_type'  : placeId
    };

    if (validate(param) === false) {
        return;
    }

    jQuery.ajax({
        type: 'POST',
        url: param.site_url + '/ajax.php',
        data: param,
        success: function(res) {
            registerCalendar(res);
        },
        error: function(res) {
            console.log('Error After');
        }
    });
}

/**
 * Display the calendar with details
 * @param  json res this is from the database
 */
function registerCalendar(res) {
    var data = res.data.calendar;
    var header = '',
        weekly = '',
        month = '',
        end = '',
        html = '',
        content = '';

    header += '<div class="blockpad">';
    header += '<div class="calendarnav">';
    header += '<p>'+res.data.calendarnm+'予約状況カレンダー</p>';
    header += '<ul>';
    header += '<li>'+res.data.date+'</li>';
    header += '<input type="hidden" name="before" value="'+res.data.before+'">';
    header += '<input type="hidden" name="next" value="'+res.data.next+'">';
    header += '<li class="before-edit"><a href="javascript:void(0)"><img src="'+res.data.home_url+'/common/images/icons/icon_arrow_week_prev.png" alt="prev"></a></li>';
    header += '<li class="next-edit"><a href="javascript:void(0)"><img src="'+res.data.home_url+'/common/images/icons/icon_arrow_week_next.png" alt="next"></a></li>';
    header += '</ul>';
    header += '</div>';
    header += '<table class="table_week mab10">';

    weekly += '<tr>';
    weekly += '<th>月</th>';
    weekly += '<th>火</th>';
    weekly += '<th>水</th>';
    weekly += '<th>木</th>';
    weekly += '<th>金</th>';
    weekly += '<th>土</th>';
    weekly += '<th>日</th>';
    weekly += '</tr>';

    for (var j=0; j<6; j++) {
        month += '<tr class="day">';
        for (var i in data) {
            if (typeof data[i][j] === 'undefined' || data[i][j].length <= 0) {
                month += '<td class="prevmonth"></td>';
            } else {
                    var day = '';

                    if (typeof data[i][j][0] === 'string') {
                        day = data[i][j][0];
                    } else {
                        for (var x=0;x<4;x++) {
                            var days = data[i][j][x].date;
                            if (typeof days !== 'undefined') {
                                day = days;
                            }
                        }
                    }

                    var d = new Date(day.replace(/-/g, "/"));
                    var months = d.getMonth()+1;
                    var years = '';
                    var fDay = d.getDate();
                    finalDay = ('0' + fDay).slice(-2);
                    if (d.getFullYear()+'-'+('0' + months).slice(-2) !== res.data.datenormal) {
                        month += '<td class="prevmonth">';
                    } else if (day.length > 0) {
                        month += '<td>';
                        month += '<a href="javascript:void(0)" id="status_'+ finalDay +'" data="' + res.data.date + finalDay +'">';
                    } else {
                        month += '<td>';
                        month += '<a href="javascript:void(0)" id="status_'+ finalDay +'" data="' + res.data.date + finalDay +'">';
                    }

                    month += '<span class="daynum" style="text-align:left">' + finalDay + '</span>';


                    for (var b in data[i][j]) {
                        var stadium_status = b+'|'+data[i][j][b].status;
                            var status = data[i][j][b].status;
                        if (b < 4) {
                            if (typeof status === 'undefined') {
                                month += '<span class="status status2"><input type="hidden" name="stadium_status'+b+'" value="'+stadium_status+'"></span>';
                            } else if (parseInt(status) === 1) {
                                month += '<span class="status status1"><input type="hidden" name="stadium_status'+b+'" value="'+stadium_status+'"></span>';
                            } else if (parseInt(status) === 2) {
                                month += '<span class="status status2"><input type="hidden" name="stadium_status'+b+'" value="'+stadium_status+'"></span>';
                            } else if (parseInt(status) === 0) {
                                month += '<span class="status status2"><input type="hidden" name="stadium_status'+b+'" value="'+stadium_status+'"></span>';
                            }
                        }
                    }

                    if (day.length > 0) {
                        month += '</a>';
                    } else {
                        month += '</a>';
                    }


                month += '</td>';
            }
        }
        month += '</tr>';
    }

    end += '</table>';
    end += '<p class="legendp">';
    end += '<span class="bold">＜カレンダーの見方＞</span><br>';
    end += '日付枠は上から「午前6時から午前9時」「午前9時から午後12時」「午後12時から午後3時」「午後3時から午後6時」の時間帯の予約状況を表します。';
    end += '</p>';
    end += '<ul class="legend">';
    end += '<li><span class="status status1"></span> 予約可</li>';
    end += '<li><span class="status status2"></span> 予約不可</li>';
    end += '</ul>';
    end += '</div>';
    content += header + weekly + month + end;
    var html = content.split('<tr class="menu"><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>').join('');
    var htmls = html.split('<span style="width: 136px; height: 19px; display: block;"><input type="hidden" name="stadium_status0" value="0|undefined"></span>').join('');
    var final = htmls.split('<tr class="day"><td class="prevmonth"></td><td class="prevmonth"></td><td class="prevmonth"></td><td class="prevmonth"></td><td class="prevmonth"></td><td class="prevmonth"></td><td class="prevmonth"></td></tr>').join('');
    jQuery('.calendar-display').removeClass('display-none');
    jQuery('.calendar-display').html(final);
}

/**
 * Get the status of every data
 * for each date in the calendar
 */
function crud() {
    var html = table = '';
    var date = jQuery(this).attr('data');
    var id = jQuery(this).attr('id');

    var scheds = {
        '午前6時から午前9時' : jQuery('#'+id+' input[name="stadium_status0"]').val(),
        '午前9時から午前12時' : jQuery('#'+id+' input[name="stadium_status1"]').val(),
        '午後12時から午後3時': jQuery('#'+id+' input[name="stadium_status2"]').val(),
        '午後3時から午後6時' : jQuery('#'+id+' input[name="stadium_status3"]').val(),
    };

    table += '<input type="hidden" name="scheddate" value="'+date+'">';
    table += '<table class="table table-bordered table-crud">';
    table += '<tbody>';
    for (var j in scheds) {
        var splits = scheds[j].split('|');
        var id = typeof splits[1] === 'undefined' ? '' : splits[1];
        var selected1 = id === '1' ? 'selected': '';
        var selected2 = id === '2' ? 'selected': '';
        var selected3 = id === '3' ? 'selected': '';

        table += '<tr>';
        table += '<td>'+j+'</td>';
        table += '<td>';
        table += '<select class="form-control" id="datestatus'+splits[0]+'">';
        table += '<option></option>';
        table += '<option value="1" '+selected1+'>予約可</option>';
        table += '<option value="2" '+selected2+'>予約不可</option>';
        table += '</select>';
        table += '</td>';
        table += '</tr>';
    }

    table += '</tbody>';
    table += '</table>';

    var title = '';
    title += '登録<span style="margin-left:120px">'+date+'日</span>';
    jQuery('#crudModal .modal-title').html(title);
    jQuery('#crudModal .modal-body').html(table);
    jQuery('#crudModal').modal();
}

/**
 * Submit the updating of the status of each date
 */
function crudSubmit() {
    var stadium_type_checked = jQuery('input[name=stadium_type]:checked').val();
    var placeId = stadium_type_checked === 'undefined' ? jQuery('input[name=stadium_type]').val() : jQuery('input[name=stadium_type]:checked').val();
    var sel   = {
        'sel0'      : [1, jQuery('#datestatus0').val()],
        'sel1'      : [2, jQuery('#datestatus1').val()],
        'sel2'      : [3, jQuery('#datestatus2').val()],
        'sel3'      : [4, jQuery('#datestatus3').val()],
    };
    var param = {
        'site_url'  : jQuery('input[name=site_url]').val(),
        'plugin_dir': jQuery('input[name=plugin_dir]').val(),
        'place'     : typeof placeId === 'undefined' ? '' : placeId,
        'date'      : jQuery('input[name=scheddate]').val(),
        'sel'       : sel,
        'type'      : 'stadium_crud',
        'cal_type'  : placeId
    };

    jQuery.ajax({
        type: 'POST',
        url : param.site_url + '/ajax.php',
        data : param,
        success: function(res) {
            jQuery('#crudModal').modal('toggle');
            registerCalendar(res);
        },
        error: function (res) {
            console.log('Error Crud');
        }
    });
}

/**
 * Change the type
 */
function changeType() {
    if (this.value > 1) {
        jQuery('#datepicker').val('');
        jQuery('.calendar-display').addClass('display-none');
    } else {
        jQuery('#datepicker').val('');
        jQuery('.calendar-display').addClass('display-none');
    }
};

/**
 * Change the date
 */
function changeDate() {
    jQuery('.calendar-display').addClass('display-none');
}

/**
 * Validate any parameters
 *
 * @param  object  param input by the user
 * @return boolean res   if there's error then false otherwise true.
 */
function validate(param) {
    var res = true;
    if (typeof param.place !== 'undefined') {
        if (param.place.length <= 0) {
            jQuery('.error-type').html('選択されていません。');
            res = false;
        } else {
            jQuery('.error-type').html('');
        }
    }

    if (typeof param.date !== 'undefined') {
        if (param.date.length <= 0) {
            jQuery('.error-date').html('選択されていません。');
            res = false;
        } else {
            jQuery('.error-date').html('');
        }
    }

    return res;
}
