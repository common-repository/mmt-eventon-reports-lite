/**jslint browser:true, devel:true */
/*global jQuery*/
/*global define */
/*global window */
/*jslint this*/
/*global tinymce*/
/*global document*/
/*global mmt_eo_reports_admin*/
/*global wp*/
/*global Chart*/

var {__} = wp.i18n;
jQuery(document).ready(function ($) {
    "use strict";
    var UpcomingChart;
    var RSVPChart;
    var TicketTicketChart;
    var TicketSalesChart;
    var TablePieChart;
    var AuthorPieChart;
    function cleanPopBox($popbox) {
        $popbox.find('.mer-popbox-body').html('');
        $popbox.find('.mer-popbox-header').html('');
    }
    $('body').on('click', '.mer-popbox-opener', function (e) {
        e.preventDefault();
        var target = $(this).data('target');
        var data = $(this).data();
        cleanPopBox($(target));
        $(target).find('.mer-popbox-header').html(data.title);
        var ajaxdata = {};
        ajaxdata.data = data;
        ajaxdata.action = 'mmt_eo_report_' + data.action;
        ajaxdata.mmt_eo_reports_nonce = mmt_eo_reports_admin.mmt_eo_reports_ajax_nonce;
        $.ajax({
            beforeSend: function () {
                $(target).addClass('show');
                $(target).find('.mer-popbox-body').addClass('mer-popbox-loader');
            },
            type: 'POST',
            dataType: 'json',
            url: mmt_eo_reports_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                if ('good' === data.status) {
                    $(target).find('.mer-popbox-body').html(data.html);
                }
            },
            complete: function () {
                $(target).find('.mer-popbox-body').removeClass('mer-popbox-loader');
            }
        });
    });
    $('body').on('click', '.mer-popbox-close', function (e) {
        e.preventDefault();
        $(this).closest('.mer-popbox').removeClass('show');
        $(this).find('.mer-popbox-body').removeClass('mer-popbox-loader');
        cleanPopBox($(this));
    });
    $('body').on('click', '#mmt_eo_reports_select_all_rsvp', function () {
        $('input[name="mmt_eo_reports_single_rsvp_event"]').not(this).prop('checked', this.checked);
    });
    $('body').on('click', '.mer-download-csv-rsvp-single', function () {
        var url = $(this).data('url');
        window.location.href = url;
    });
    $('body').on('click', '.mer-download-csv-ticket-single', function () {
        var url = $(this).data('url');
        window.location.href = url;
    });
    $('body').on('click', '.mer-download-csv-rsvp', function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        var $parent = $(this).closest('.event-rsvp-container-data');
        var $table = $parent.find('.upcoming-events-rsvp-list');
        var selected = [];
        $table.find('input[name="mmt_eo_reports_single_rsvp_event"]:checked').each(function () {
            var eid = $(this).val();
            var ri = $(this).data('ri');
            var event = eid + '_' + ri;
            selected.push(event);
        });
        if (selected.length === 0) {
            $table.find('input[name="mmt_eo_reports_single_rsvp_event"]').css('border', '1px solid red');
            $table.find('input[name="mmt_eo_reports_select_all_rsvp"]').css('border', '1px solid red');
            $table.find('input[name="mmt_eo_reports_single_rsvp_event"]').prop('checked', 'checked');
            $table.find('input[name="mmt_eo_reports_select_all_rsvp"]').prop('checked', 'checked');
            $table.find('input[name="mmt_eo_reports_single_rsvp_event"]:checked').each(function () {
                var eid = $(this).val();
                var ri = $(this).data('ri');
                var event = eid + '_' + ri;
                selected.push(event);
            });
        }
        selected.join(', ');
        var fullurl = url + '&selected=' + selected;
        window.location.href = fullurl;
    });
    $('body').on('click', '.mer-download-csv-ticket', function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        var $parent = $(this).closest('.event-ticket-container-data');
        var $table = $parent.find('.upcoming-events-ticket-list');
        var selected = [];
        $table.find('input[name="mmt_eo_reports_single_ticket_event"]:checked').each(function () {
            var eid = $(this).val();
            var ri = $(this).data('ri');
            var event = eid + '_' + ri;
            selected.push(event);
        });
        if (selected.length === 0) {
            $table.find('input[name="mmt_eo_reports_single_ticket_event"]').css('border', '1px solid red');
            $table.find('input[name="mmt_eo_reports_select_all_ticket"]').css('border', '1px solid red');
            $table.find('input[name="mmt_eo_reports_single_ticket_event"]').prop('checked', 'checked');
            $table.find('input[name="mmt_eo_reports_select_all_ticket"]').prop('checked', 'checked');
            $table.find('input[name="mmt_eo_reports_single_ticket_event"]:checked').each(function () {
                var eid = $(this).val();
                var ri = $(this).data('ri');
                var event = eid + '_' + ri;
                selected.push(event);
            });
        }
        selected.join(', ');
        var fullurl = url + '&selected=' + selected;
        window.location.href = fullurl;
    });
    function generate_graph_upcoming(data) {
        var days = [];
        var days_total = [];
        $.each(data.result, function (i, e) {
            days.push(i);
        });
        $.each(data.result, function (i, e) {
            days_total.push(e);
        });
        var ctx = document.getElementById('DashboardUpcomingReport').getContext('2d');
        if (UpcomingChart) {
            UpcomingChart.destroy();
        }
        UpcomingChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: days,
                tooltips: '',
                datasets: [{
                    label: data.label,
                    //backgroundColor: '#f79191',
                    backgroundColor: 'rgba(247, 145, 145, 0.4)',
                    borderColor: '#f79191',
                    pointBorderWidth: 3,
                    pointRadius: 3,
                    pointHoverBorderWidth: 4,
                    pointHoverRadius: 7,
                    fill: true,
                    data: days_total,
                    lineTension: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: data.title,
                        fontSize: 16,
                        lineHeight: 1.8,
                        fontColor: '#555'
                    }
                },
                animation: {
                    duration: 750
                },
                scales: {
                    x: {
                        gridLines: {
                            display: false
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: __('Event Count', 'mmt-eo-reports')
                        },
                        ticks: {
                            maxTicksLimit: 10,
                            stepSize: 1,
                            precision: 1,
                            beginAtZero: true,
                            userCallback: function (label) {
                                if (Math.floor(label) === label) {
                                    return label;
                                }
                            }
                        }
                    }
                },
                legend: {
                    align: 'end'
                }
            }
        });
    }
    function generate_graph_rsvp(data) {
        var days = [];
        var days_total = [];
        $.each(data.result, function (i, e) {
            days.push(i);
        });
        $.each(data.result, function (i, e) {
            days_total.push(e);
        });
        var element = document.getElementById('DashboardRSVPReport');

        if (element === null) {
            return;
        }
        var ctxrt = document.getElementById('DashboardRSVPReport').getContext('2d');
        if (RSVPChart) {
            RSVPChart.destroy();
        }
        RSVPChart = new Chart(ctxrt, {
            type: 'line',
            data: {
                labels: days,
                tooltips: '',
                datasets: [{
                    label: data.label,
                    //backgroundColor: '#f79191',
                    backgroundColor: 'rgba(247, 145, 145, 0.4)',
                    borderColor: '#f79191',
                    pointBorderWidth: 3,
                    pointRadius: 3,
                    pointHoverBorderWidth: 4,
                    pointHoverRadius: 7,
                    fill: true,
                    data: days_total,
                    lineTension: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: data.title,
                        fontSize: 16,
                        lineHeight: 1.8,
                        fontColor: '#555'
                    }
                },
                animation: {
                    duration: 750
                },
                scales: {
                    x: {
                        gridLines: {
                            display: false
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: __('Event Count', 'mmt-eo-reports')
                        },
                        ticks: {
                            maxTicksLimit: 10,
                            stepSize: parseInt(data.stepSize),
                            precision: 1,
                            beginAtZero: true,
                            userCallback: function (label) {
                                if (Math.floor(label) === label) {
                                    return label;
                                }
                            }
                        }
                    }
                },
                legend: {
                    align: 'end'
                }
            }
        });
    }
    function generate_rsvp_single_line_pie($table) {
        $table.find('canvas.rsvp_small_chart').each(function () {
            var $canvas = $(this);
            var yes = parseInt($canvas.data('y'));
            var no = parseInt($canvas.data('n'));
            var maybe = parseInt($canvas.data('mb'));
            if (0 === yes && 0 === no && 0 === maybe) {
                $canvas.remove();
            } else {
                var data = {
                    labels: [
                        "Yes",
                        "No",
                        "Maybe"
                    ],
                    datasets: [
                        {
                            data: [yes, no, maybe],
                            backgroundColor: [
                                "#7fadc4",
                                "#E89090",
                                "#bbafaf"
                            ]
                        }
                    ]
                };
                TablePieChart = new Chart($canvas, {
                    type: 'pie',
                    data: data,
                    options: {
                        rotation: -90,
                        circumference: 180,
                        responsive: false,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: false
                            },
                            tooltip: {
                                display: false
                            }
                        }
                    }
                });
            }
        });
    }
    function generate_pie_chart_ticket($table) {
        var symbol = $table.find('table').data('symbol');
        var element1 = document.getElementById('DashboardTicketTicketPie');
        var element2 = document.getElementById('DashboardTicketSalesPie');
        if (element1 === null || element2 === null) {
            return;
        }
        var txtxpie = document.getElementById('DashboardTicketTicketPie').getContext('2d');
        var txtxsales = document.getElementById('DashboardTicketSalesPie').getContext('2d');
        if (TicketTicketChart) {
            TicketTicketChart.destroy();
        }
        if (TicketSalesChart) {
            TicketSalesChart.destroy();
        }
        var event = [];
        var ticket_cost = [];
        var ticket_sold = [];
        var colors = [];
        $table.find('tr').each( function () {
            var $tr = $(this);
            event.push($tr.data('title'));
            ticket_sold.push($tr.data('ts'));
            ticket_cost.push($tr.data('tc'));

            var r = Math.floor(Math.random() * 255);
            var g = Math.floor(Math.random() * 255);
            var b = Math.floor(Math.random() * 255);
            colors.push("rgb(" + r + "," + g + "," + b + ")");
        });
        var data = {
            labels: event,
            datasets: [
                {
                    data: ticket_sold,
                    backgroundColor: colors
                }]
        };
        TicketTicketChart = new Chart(txtxpie, {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                var label = tooltipItem.label || '';
                                return label;
                            },
                            afterLabel: function(tooltipItem) {
                                var label = '';
                                var data_ = tooltipItem.parsed || '';
                                if (data_) {
                                    label = __('Ticket', 'mmt-eo-reports' ) + ' : ' + data_;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
        var tickdata = {
            labels: event,
            datasets: [
                {
                    data: ticket_cost,
                    backgroundColor: colors
                }]
        };
        TicketSalesChart = new Chart(txtxsales, {
            type: 'pie',
            data: tickdata,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label = label + symbol + ' ' + context.parsed;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
    function generate_pie_chart_author(result) {
        var element = document.getElementById('DashboardAuthorReport');

        if (element === null) {
            return;
        }
        var authorpie = document.getElementById('DashboardAuthorReport').getContext('2d');
        if (AuthorPieChart) {
            AuthorPieChart.destroy();
        }
        var authors = [];
        var events = [];
        var colors = [];
        $.each( result.chart,function (author, event) {
            var $tr = $(this);
            authors.push(author);
            events.push(event);

            var r = Math.floor(Math.random() * 255);
            var g = Math.floor(Math.random() * 255);
            var b = Math.floor(Math.random() * 255);
            colors.push("rgb(" + r + "," + g + "," + b + ")");
        });
        var data = {
            labels: authors,
            datasets: [
                {
                    data: events,
                    backgroundColor: colors
                }]
        };
        AuthorPieChart = new Chart(authorpie, {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                    title: {
                        display: true,
                        text: result.title
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                var label = tooltipItem.label || '';
                                return label;
                            },
                            afterLabel: function(tooltipItem) {
                                var label = '';
                                var data_ = tooltipItem.parsed || '';
                                if (data_) {
                                    label = __('Event(s) Submitted', 'mmt-eo-reports' ) + ' : ' + data_;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
    function generate_rsvp_counter(counter, $container) {
        var $counter = $container.find('.mer-counters-container');
        var $yes = $counter.find('.counter.yes').find('.count');
        var $no = $counter.find('.counter.no').find('.count');
        var $maybe = $counter.find('.counter.maybe').find('.count');
        countimate($yes, counter.yes);
        countimate($no, counter.no);
        countimate($maybe, counter.maybe);
    }
    function generate_ticket_counter(counter, $container) {
        var $counter = $container.find('.mer-counters-container');
        var $sales = $counter.find('.counter.sales').find('.count');
        var $order = $counter.find('.counter.orders').find('.count');
        var $cancelled = $counter.find('.counter.cancelled').find('.count');
        countimate($sales, counter.sales, counter.symbol);
        countimate($order, counter.orders);
        countimate($cancelled, counter.cancelled);
    }
    function generate_author_counter(counter, $container) {
        var $counter = $container.find('.mer-counters-container');
        var $authors = $counter.find('.counter.authors').find('.count');
        var $events = $counter.find('.counter.events').find('.count');
        countimate($authors, counter.authors);
        countimate($events, counter.events);
    }
    function countimate($holder, count, symbol = '') {
        //Animate my counter from 0 to set number (6)
        $({counter: 0}).animate(
            {counter: count},
            {
            duration: 3000,
            easing:'linear',
            step: function() {
                $holder.html( symbol + Math.ceil(this.counter))
            },
            complete: function() {
            }
        });
    }
    function get_upcoming_report($chart) {
        var $container = $chart.closest('.upcoming-container-data');
        var $elist = $container.find('.upcoming-events-list');
        var type = $container.data('ranger');
        var index = $container.data('index');
        var ajaxdata = {};
        ajaxdata.type = type;
        ajaxdata.index = index;
        ajaxdata.action = 'mmt_eo_report_get_upcoming_chart';
        ajaxdata.mmt_eo_reports_nonce = mmt_eo_reports_admin.mmt_eo_reports_ajax_nonce;
        $.ajax({
            beforeSend: function () {
                $chart.addClass('mer-chart-loading');
                $elist.addClass('mer-chart-loading');
            },
            type: 'POST',
            dataType: 'json',
            url: mmt_eo_reports_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                generate_graph_upcoming(data);
                $elist.find('tbody').html(data.html);
                var $header = $elist.closest('div').find('.mer-table-date-range');
                $header.html(data.title);
            },
            complete: function () {
                $chart.removeClass('mer-chart-loading');
                $elist.removeClass('mer-chart-loading');
            }
        });
    }
    function get_upcoming_views($container) {
        var $elist = $container.find('.upcoming-events-list');
        var type = $container.data('ranger');
        var index = $container.data('index');
        var ajaxdata = {};
        ajaxdata.type = type;
        ajaxdata.index = index;
        ajaxdata.action = 'mmt_eo_report_get_upcoming_views_count';
        ajaxdata.mmt_eo_reports_nonce = mmt_eo_reports_admin.mmt_eo_reports_ajax_nonce;
        $.ajax({
            beforeSend: function () {
                $container.addClass('mer-chart-loading');
                //$elist.addClass('mer-chart-loading');
            },
            type: 'POST',
            dataType: 'json',
            url: mmt_eo_reports_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                var $date = $container.find('.mer-upcoming-views-dates');
                $date.html(data.title);
                $elist.find('tbody').html(data.html);
            },
            complete: function () {
                $container.removeClass('mer-chart-loading');
                //$elist.removeClass('mer-chart-loading');
            }
        });
    }
    function get_search_keyword_log($container) {
        var $logTable = $container.find('.search-log-table');
        var index = $container.data('index');
        var ajaxdata = {};
        ajaxdata.index = index;
        ajaxdata.action = 'mmt_eo_report_get_recent_search_log';
        ajaxdata.mmt_eo_reports_nonce = mmt_eo_reports_admin.mmt_eo_reports_ajax_nonce;
        $.ajax({
            beforeSend: function () {
                $container.addClass('mer-chart-loading');
                //$elist.addClass('mer-chart-loading');
            },
            type: 'POST',
            dataType: 'json',
            url: mmt_eo_reports_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                $logTable.find('tbody').html(data.html);
            },
            complete: function () {
                $container.removeClass('mer-chart-loading');
            }
        });
    }
    function get_rsvp_enabled_events($container) {
        var $chart = $container.find('.mer-rsvp-chart');
        var $elist = $container.find('.upcoming-events-rsvp-list');
        var type = $container.data('ranger');
        var index = $container.data('index');
        var ajaxdata = {};
        ajaxdata.type = type;
        ajaxdata.index = index;
        ajaxdata.action = 'mmt_eo_report_get_upcoming_rsvp';
        ajaxdata.mmt_eo_reports_nonce = mmt_eo_reports_admin.mmt_eo_reports_ajax_nonce;
        $.ajax({
            beforeSend: function () {
                $chart.addClass('mer-chart-loading');
                $elist.addClass('mer-chart-loading');
            },
            type: 'POST',
            dataType: 'json',
            url: mmt_eo_reports_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                generate_graph_rsvp(data);
                $elist.find('tbody').html(data.html);
                var $header = $elist.closest('div').find('.mer-table-date-range');
                $header.html(data.title);
                generate_rsvp_single_line_pie($elist);
                generate_rsvp_counter(data.counter, $container);
            },
            complete: function () {
                $chart.removeClass('mer-chart-loading');
                $elist.removeClass('mer-chart-loading');
            }
        });
    }
    function assign_ticket_topseller($table, topseller) {
        $table.find('tr').each( function () {
            var event_id = $(this).data('event_id');
            var ri = $(this).data('ri');
            if (topseller.id === event_id && topseller.ri === ri ) {
                $(this).find('.title').append(topseller.icon);
                $(this).css('background', topseller.style);
            }
        });
    }
    function get_ticket_enabled_events($container) {
        var $chart = $container.find('.mer-ticket-chart');
        var $elist = $container.find('.upcoming-events-ticket-list');
        var type = $container.data('ranger');
        var index = $container.data('index');
        var ajaxdata = {};
        ajaxdata.type = type;
        ajaxdata.index = index;
        ajaxdata.action = 'mmt_eo_report_get_upcoming_ticket';
        ajaxdata.mmt_eo_reports_nonce = mmt_eo_reports_admin.mmt_eo_reports_ajax_nonce;
        $.ajax({
            beforeSend: function () {
                $chart.addClass('mer-chart-loading');
                $elist.addClass('mer-chart-loading');
            },
            type: 'POST',
            dataType: 'json',
            url: mmt_eo_reports_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                $elist.find('tbody').html(data.html);
                assign_ticket_topseller($elist, data.topseller);
                generate_pie_chart_ticket($elist);
                var $header = $elist.closest('div').find('.mer-table-date-range');
                $header.html(data.title);
                generate_ticket_counter(data.counter, $container);
            },
            complete: function () {
                $chart.removeClass('mer-chart-loading');
                $elist.removeClass('mer-chart-loading');
            }
        });
    }
    function get_authors_event_report($container){
        var $chart = $container.find('.mer-action-user-chart');
        var $elist = $container.find('.author-submitted-events-list');
        var type = $container.data('ranger');
        var index = $container.data('index');
        var ajaxdata = {};
        ajaxdata.type = type;
        ajaxdata.index = index;
        ajaxdata.action = 'mmt_eo_report_get_event_submitted_group_by_author';
        ajaxdata.mmt_eo_reports_nonce = mmt_eo_reports_admin.mmt_eo_reports_ajax_nonce;
        $.ajax({
            beforeSend: function () {
                $chart.addClass('mer-chart-loading');
                $elist.addClass('mer-chart-loading');
            },
            type: 'POST',
            dataType: 'json',
            url: mmt_eo_reports_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                $elist.find('tbody').html(data.html);
                //assign_ticket_topseller($elist, data.topseller);
                generate_pie_chart_author(data.result);
                var $header = $elist.closest('div').find('.mer-table-date-range');
                $header.html(data.title);
                generate_author_counter(data.counter, $container);
            },
            complete: function () {
                $chart.removeClass('mer-chart-loading');
                $elist.removeClass('mer-chart-loading');
            }
        });
    }
    function mmt_eo_reports_admin_tab(hash) {
        var $activeTab = $('body').find(hash);
        /* var tab = $activeTab.data('nav'); */
        var $parent = $activeTab.closest('.mer-body-main');
        $parent.find('.mer-navbar .mer-nav-link').removeClass('active');
        $parent.find('.mer-nav-link[data-nav*=\\' + hash + ']').addClass('active');
        var $prevActiveContainer = $parent.find('.mer-nav-content.active');
        var $activeContainer = $activeTab;
        $prevActiveContainer.fadeOut(250, function() {
            $prevActiveContainer.removeClass('active');
        });
        $activeContainer.delay(50).fadeIn(250).addClass('active');
        $("html, body").animate({
            scrollTop: 0
        }, 1000);
    }
    function init() {
        if( $('body').find('.mer-body-main').length == 0 ) {
            return false;
        }
        var hash = window.location.hash;
        if (hash === '' || hash === 'undefined') {
        } else {
            mmt_eo_reports_admin_tab(hash);
        }
        var $chart = $('body').find('.mer-upcoming-chart');
        get_upcoming_report($chart);
        var $views = $('body').find('.event-views-container-data');
        get_upcoming_views($views);
        var $search = $('body').find('.search-log-container-data');
        get_search_keyword_log($search);
        var $rsvp = $('body').find('.event-rsvp-container-data');
        get_rsvp_enabled_events($rsvp);
        var $ticket = $('body').find('.event-ticket-container-data');
        get_ticket_enabled_events($ticket);
        var $author = $('body').find('.action-user-container-data');
        get_authors_event_report($author);
    }
    function runPageAjax(tab) {
        switch (tab) {
            case '#mer-nav-general':
                var $chart = $('body').find('.mer-upcoming-chart');
                get_upcoming_report($chart);
            break;
            case '#mer-nav-views':
            break;
        }
    }
    $('body').on('click', '.mer-range', function (e) {
        e.preventDefault();
        var $li = $(this);
        var type = $li.data('range');
        var $ul = $(this).closest('.mer-range-picker');
        $ul.find('li').each( function (i){
            $(this).removeClass('current');
        });
        $li.addClass('current');

        var $container = $li.closest('.mer-data-body-container');
        $container.data('ranger', type);
        $container.data('index', 0);
        var master = $container.data('type');
        if ('general' === master) {
            var $chart = $container.find('.mer-upcoming-chart');
            get_upcoming_report($chart);
        }
        if ('views' === master) {
            get_upcoming_views($container);
        }
        if ('rsvp' === master) {
            get_rsvp_enabled_events($container);
        }
        if ('ticket' === master) {
            get_ticket_enabled_events($container);
        }
        if ('author' === master) {
            get_authors_event_report($container);
        }
    });
    init();
    $('body').on('click', '.mer-range-lr', function (e) {
        var $btn = $(this);
        var $container = $btn.closest('.mer-data-body-container');
        var index = $container.data('index');
        var master = $container.data('type');
        var sym = $btn.data('sym');
        if ( 'search' === master ) {
            var total = $btn.closest('.mer-card-left').data('total');
            var pages = total % 10;
            if('+' == sym) {
                if ( ( index + 1 ) < pages  ) {
                    index = index + 1;
                }
            } else {
                if ( index >= 1 ) {
                    index = index - 1;
                }
            }
        } else {
            if('+' == sym) {
                index = index + 1;
            } else {
                index = index - 1;
            }
        }
        $container.data('index', index);
        if ('general' === master) {
            var $chart = $container.find('.mer-upcoming-chart');
            get_upcoming_report($chart);
        }
        if ('views' === master) {
            get_upcoming_views($container);
        }
        if ('search' === master) {
            var $search = $('body').find('.search-log-container-data');
            var $btnHolder = $btn.closest('.mer-card-left');
            $btnHolder.find('.mer-range-pn').html(index + 1);
            get_search_keyword_log($search);
        }
        if ('rsvp' === master) {
            get_rsvp_enabled_events($container);
        }
        if ('ticket' === master) {
            get_ticket_enabled_events($container);
        }
        if ('author' === master) {
            get_authors_event_report($container);
        }
    });
    $('body').on('click', '.mer-nav-link', function (e) {
        e.preventDefault();
        var href = $(this).data('nav');
        mmt_eo_reports_admin_tab(href);
        window.location.hash = href;
    });


    /****** Email Preview ******** */
    var step = 0;
    function runStepByStepPreviewProgress($parent, added) {
        var $textarea = $parent.find('.mer-rsvp-email-preview-area').find('textarea');
        var ajaxdata = {};
        ajaxdata.action = 'mmteorc_rsvp_email_preview';
        ajaxdata.step = step;
        ajaxdata.added = added;
        ajaxdata.mmt_eo_reports_nonce = mmt_eo_reports_admin.mmt_eo_reports_ajax_nonce;
        $.ajax({
            beforeSend: function () {
                if (0 === step) {
                    $textarea.val( __('Generating events....', 'mmt-eo-reports'));
                }
            },
            type: 'POST',
            dataType: 'json',
            async: true,
            url:  mmt_eo_reports_admin.ajaxurl,
            data: ajaxdata,
            success: function (data) {
                if (0 === step) {
                    if (data.status === 'good') {
                        $textarea.val($textarea.val() + '\n' + data.msg + '\n' + data.next);
                        step++;
                        runStepByStepPreviewProgress($parent, data.data);
                        return;
                    } else {
                        $textarea.val($textarea.val() + '\n' + data.msg);
                        step = 1;
                    }
                }
                if (1 === step) {
                    if (data.status === 'bad' ) {
                        $textarea.val($textarea.val() + '\n' + data.msg);
                    } else {
                        var $merPopbox = $('#mer-email-preview-popbox');
                        $textarea.val($textarea.val() + '\n' + data.msg);
                        cleanPopBox($merPopbox);
                        $merPopbox.addClass('show');
                        $merPopbox.find('.mer-popbox-body').html(data.html);
                        $merPopbox.find('.mer-popbox-header').html(data.title);
                    }
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(thrownError);
            },
            complete: function () {
                if (2 === step) {
                    $parent.find('.eo_228522_preview_working').removeClass('evoloading');
                }
            }
        });
    }
    $('body').on('click', '.mer-preview-rsvp-email', function (e) {
        step = 0;
        e.preventDefault();
        var $parent = $(this).closest('div.mer-preview-rsvp-holder');
        runStepByStepPreviewProgress($parent);
    });

    /**** ends Email Preview */
});