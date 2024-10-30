/**jslint browser:true, devel:true */
/*global jQuery*/
/*global define */
/*global window */
/*jslint this*/
/*global tinymce*/
/*global document*/
/*global mmt_eo_reports*/
/*global wp*/
jQuery(document).ready(function ($) {
    "use strict";
    $('body').on('click', '.eventon_events_list .desc_trig', function () {
        var obj = $(this);
        var attr = obj.closest('.evo_lightbox').attr('data-cal_id');
        var calendar = $('');
        if (typeof attr !== typeof undefined && attr !== false) {
            var cal_id = attr;
            calendar = $('#' + cal_id);
        } else {
            calendar = obj.closest('.ajde_evcal_calendar');
        }
        var shortcode = calendar.evo_shortcode_data();
        var cal_ux_val = shortcode.ux_val;
        var ux_val = obj.data('ux_val');
        if (cal_ux_val !== '' && cal_ux_val !== undefined && cal_ux_val !== '0') {
            ux_val = cal_ux_val;
        }
        if (ux_val !== '4') {
            var event_id = obj.closest('.eventon_list_event').data('event_id');
            var repeat_interval = parseInt(obj.closest('.eventon_list_event').data('ri'));
            repeat_interval = (repeat_interval) ? repeat_interval : '0';
            var ajaxdata = {};
            ajaxdata.event_id = event_id;
            ajaxdata.repeat_interval = repeat_interval;
            ajaxdata.mmteor_nonce = mmt_eo_reports.mmt_eo_reports_nonce;
            ajaxdata.action = 'mmt_eo_reports_add_count_from_event_top';
            $.ajax({
                beforeSend: function () {},
                type: 'POST',
                url: mmt_eo_reports.ajaxurl,
                data: ajaxdata,
                dataType: 'json',
                success: function (data) {
                    if ('good' === data.status) {
                        console.log(data.counter);
                    }
                }
            });
        }
    });
    function mmt_eo_reports_add_keywords_to_db($object, type) {
        var keyword = '';
        if ('normal' === type) {
            keyword = $object.closest('.evosr_search_box').find('input').val();
        } else if('calendar' === type) {
            keyword = $object.val();
        }
        if (keyword === undefined || keyword === '') {
            return false;
        }
        var ajaxdata = {};
        ajaxdata.keyword = keyword;
        ajaxdata.mmteor_nonce = mmt_eo_reports.mmt_eo_reports_nonce;
        ajaxdata.action = 'mmt_eo_reports_ajax_store_keyword_to_db';
        $.ajax({
            beforeSend: function () {},
            type: 'POST',
            url: mmt_eo_reports.ajaxurl,
            data: ajaxdata,
            dataType: 'json',
            success: function (data) {
                if ('good' === data.status) {
                    console.log(data.message);
                }
            }
        });
    }
    $.fn.mmtEnterKey = function (fnc) {
        return this.each(function () {
            $(this).keypress(function (ev) {
                var keycode = (ev.keyCode ? ev.keyCode : ev.which);
                if (keycode == '13') {
                    fnc.call(this, ev);
                }
            });
        })
    };
    $('body').on('click','.evo_do_search', function () {
        mmt_eo_reports_add_keywords_to_db($(this), 'normal');
    });
    $(".evosr_search_box input").mmtEnterKey(function () {
        mmt_eo_reports_add_keywords_to_db($(this).siblings('.evo_do_search'), 'normal');
    });
    $('body').on('click','.evosr_search_btn',function(){
        mmt_eo_reports_add_keywords_to_db( $(this).siblings('input'), 'calendar');
    });
    $(".evo_search_bar_in input").mmtEnterKey(function () {
        mmt_eo_reports_add_keywords_to_db($(this), 'calendar');
    });

    
});
