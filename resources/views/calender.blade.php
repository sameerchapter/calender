@extends('layouts.app')

@section('content')
<style>
    .mbsc-schedule-invalid-text{
        color: red;
    font-size: 15px;
    font-weight: bold;
    }
    .address-txt span,
    .info-txt span,
    .status-txt span,
    .project-txt span {
        font-size: 14px;
        color: #000;
        font-weight: 600;
        text-align: left;
    }

    .mbsc-timeline-day {
        text-align: center;
    }

    #project-details p {
        color: #000;
    }

    .mbsc-timeline-slot-title {
        text-align: center !important;
    }

    .employee-shifts-day {
        font-size: 14px;
        font-weight:
            600;
        opacity: .6;
    }

    .employee-shifts-popup .mbsc-popup .mbsc-popup-header {
        padding-top: 8px;
        padding-bottom: 8px;
    }

    .employee-shifts-cont {
        position: relative;
        padding-left: 42px;
        max-height: 40px;
    }

    .employee-shifts-avatar {
        position: absolute;
        max-height: 40px;
        max-width: 40px;
        top: 18px;
        -webkit-transform: translate(-50%, -50%);
        transform: translate(-50%, -50%);
        left: 20px;
    }

    .employee-shifts-name {
        font-size: 15px;
    }

    .employee-shifts-title {
        font-size: 12px;
    }

    .md-employee-shifts .mbsc-timeline-resource,
    .md-employee-shifts .mbsc-timeline-resource-col {
        width: 200px;
        align-items: center;
        display: flex;
    }

    .md-employee-shifts .mbsc-schedule-event {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 36px;
    }

    .mbsc-timeline-row {
        height: 90px;
    }

    .mbsc-flex-col.mbsc-flex-1-1.mbsc-popup-body.mbsc-popup-body-center.mbsc-ios.mbsc-popup-body-round {
        border-radius:
            0px !important;
        background-color: #f6f6f6 !important;
    }

    .mbsc-flex-none.mbsc-popup-header.mbsc-popup-header-center.mbsc-ios {
        padding: 15px;
    }

    .foreman-pill {
        background-color: #172B4D;
        border: none;
        color: white;
        padding: 10px 20px;
        text-align: center;
        text-decoration:
            none;
        display: inline-block;
        margin: 4px 2px;
        cursor: pointer;
        border-radius: 16px;
    }

    .mbsc-timeline-header-sticky .mbsc-timeline-header-date {
        display: none !important;
    }
</style>
<div id="content">
    <div class="container main">
        <div class="card-new ptb-50">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-head">
                        <span id="popl">Projects Calendar</span>
                    </div>
                </div>
                <div class="col-md-9"> </div>
                <div class="col-md-3">
                    <div class="form-head">
                        <label>Skip calender to:</label>
                        <input type="date" onchange="moveCalender(this);" class="form-control" id="specific_date">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="Col-md-11">
                        <div id="foreman-staff-modal" class="mbsc-cloak">
                            <div class="mbsc-align-center mbsc-padding">

                            </div>
                        </div>
                        <div id="demo-employee-shifts-calendar" class="md-employee-shifts"></div>

                        <div id="demo-employee-shifts-popup" class="employee-shifts-popup">

                            <div class="mbsc-form-group" id="project-details">

                            </div>
                            <div class="mbsc-form-group search-project"> <label> Search Project <input mbsc-input
                                        id="employee-project-input" data-dropdown="true" /> </label> <select
                                    id="employee-project-name">
                                    <option>Search Project</option>
                                    @foreach($drafts as $draft)
                                    <option value="{{$draft->id}}_1">{{$draft->address}}</option>
                                    @endforeach
                                    @foreach($projects as $project)
                                    <option value="{{$project->id}}_2">{{$project->address}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mbsc-form-group from_to_fields">
                                <label for="employee-shifts-start">
                                    From date
                                    <input mbsc-input data-dropdown="true" id="employee-shifts-start" />
                                </label>
                                <label for="employee-shifts-end">
                                    To date
                                    <input mbsc-input data-dropdown="true" id="employee-shifts-end" />
                                </label>
                                <div id="demo-employee-shifts-date"></div>
                            </div>
                            <div class="mbsc-form-group">
                                <label>
                                    Staff
                                    <input mbsc-input id="employee-staff-input" data-dropdown="false"
                                        data-tags="true" />
                                </label>
                                <select id="employee-staff-select" multiple>
                                    @foreach($staff as $res)
                                    <option value="{{$res->id}}">{{$res->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mbsc-form-group">
                                <label>
                                    Notes
                                    <textarea mbsc-textarea id="employee-project-notes"></textarea>
                                </label>
                            </div>
                            @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Project Manager'))
                            <div class="mbsc-button-group">
                                <button class="mbsc-button-block" id="employee-shifts-delete" mbsc-button
                                    data-color="danger" data-variant="outline">Delete Project</button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var calendar;

    function moveCalender(ele) {
        var day = new Date($(ele).val()).getDay();
        calendar.setOptions({
            view: {
                timeline: {
                    type: 'week',
                    startDay: day,
                    endDay: day - 1,
                }
            }
        })
        setTimeout(function () { calendar.navigate($(ele).val(), false); }, 500)

    }


    mobiscroll.setOptions({
        theme: 'ios',
        themeVariant: 'light',
        @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Project Manager'))
        clickToCreate: true,
        dragToCreate: true,
        dragToMove: true,
        @endif
        dragToResize: true,
        eventDelete: true,
    });

    $(function () {
        var popup;
        var oldShift;
        var tempShift;
        var range;
        var deleteShift;
        var formatDate = mobiscroll.util.datetime.formatDate;
        var $notes = $('#employee-project-notes');
        var $name = $('#employee-project-input');
        var $staff = $("#employee-staff-select");
        var $deleteButton = $('#employee-shifts-delete');
        var $projectDetails = $('#project-details');
        var latest_id = <?php echo $latest_id; ?>;
        var staff = [
            <?php foreach($foreman as $res) { ?> {
                id: "<?php echo $res['id']; ?>",
                staff_name: <?php print(json_encode($res -> staff -> pluck('name'))); ?>,
                    staff_key: <?php print(json_encode($res -> staff -> pluck('id'))); ?>,
                        name: "<?php echo ucfirst($res['name']); ?>",
                            color: '#80cff7'
                },
            <?php
            }
            ?>
        ];
    
        var invalid = [ <?php foreach($leaves as $res) { ?> {
                start: "<?php echo $res['from_date']; ?>",
                end: "<?php echo $res['to_date']; ?>",
                    title: "On Leave",
                        resource: "<?php echo $res['user_id']; ?>",
                            slot: 1
                },
                {
                    start: "<?php echo $res['from_date']; ?>",
                end: "<?php echo $res['to_date']; ?>",
                    title: "On Leave",
                        resource: "<?php echo $res['user_id']; ?>",
                            slot: 1
                },
            <?php
            }
            ?>];


    var shifts = [
            <?php foreach($schedules as $res) { ?> {
            id: "<?php echo $res->id; ?>",
            start: "<?php echo $res->start; ?>",
            end: "<?php echo $res->end; ?>",
            overlap: false,
            title: "<?php echo $res->project_name; ?>",
            notes: <?php echo json_encode($res-> notes); ?>,
                resource: "<?php echo $res->foreman_id; ?>",
                    staff: <?php print(json_encode($res -> staff_id)); ?>,
                        slot: <?php echo $res -> slot; ?>,
                            color: "<?php $f_staff_collection =  $foreman->filter(function ($f) use ($res) {
    return $f -> id == $res -> foreman_id;
                            }) -> values();
    $f_staff_array = (count($f_staff_collection) > 0) ? $f_staff_collection[0] -> staff -> pluck('id') -> toArray() : [];
    $staff_array = is_array($res -> staff_id) ? $res -> staff_id : [];
                            echo empty(array_diff($f_staff_array, $staff_array)) ? "blue" : "red"; ?> "
                },
            <?php
            }
            ?>
        ];
    var slots = [{
        id: 1,
        name: 'AM',
    }, {
        id: 2,
        name: 'PM',
    }];

    

    function array_diff(array1, array2) {
        var difference = $.grep(array1, function (el) {
            return $.inArray(el, array2) < 0
        });
        return difference.concat($.grep(array2, function (el) {
            return $.inArray(el, array1) < 0
        }));;
    }

    function createAddPopup(args) {
        // hide delete button inside add popup
        $(".from_to_fields").show();
        $deleteButton.hide();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ url('foreman-staff') }}",
            data: {
                foreman_id: tempShift.resource
            },
            type: 'POST',
            dataType: 'json',
            success: function (result) {
                staffpicker.setVal(result.map(String));
                tempShift.staff = result.map(String);
            }
        });
        deleteShift = false;
        restoreShift = false;
        var slot = slots.find(function (s) {
            return s.id === tempShift.slot
        });

        // set popup header text and buttons for adding
        popup.setOptions({
            headerText: '<div>Add Project</div><div class="employee-shifts-day">' +
                formatDate('DDDD', new Date(tempShift.start)) + ' ' + slot.name + ',' + formatDate('DD MMMM YYYY', new Date(tempShift.start)) + '</div>',
            buttons: [
                'cancel',
                {
                    text: 'Add',
                    keyCode: 'enter',
                    handler: function () {
                        var msg = "";
                        var color = "";
                        var override = "";
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: "{{ url('check-leave') }}",
                            async: false,
                            data: {
                                slot: tempShift.slot,
                                foreman_id: tempShift.resource,
                                staff_id: tempShift.staff,
                                from_date: dateFormat(tempShift.start),
                                to_date: dateFormat(tempShift.end)
                            },
                            type: 'POST',
                            dataType: 'json',
                            success: function (result) {
                                if (result.success == "true") {
                                    msg = result.msg;
                                    color = result.color;
                                    if (result.override == "true") {
                                        override = "true";
                                    }
                                }
                            }
                        });
                        if (msg != "") {
                            mobiscroll.snackbar({
                                duration: 3000,
                                message: msg,
                                color: color,
                                display: 'top',
                                button: override == "true" ? {
                                    text: 'Override',
                                    action: function () {

                                        var foremans_staff = staff.filter(x => x.id == tempShift.resource);
                                        let difference = array_diff(tempShift.staff, foremans_staff[0].staff_key.map(String));
                                        tempShift.color = difference.length > 0 ? "red" : "blue";

                                        tempShift.id = "";
                                        saveProject(tempShift);

                                        let event_array = [];
                                        let endDate = new Date(tempShift.end); // today
                                        let startDate = new Date(tempShift.start); // Jan 1st 2017
                                        let daysOfYear = [];
                                        for (let day = startDate; day <= endDate; day.setDate(day.getDate() + 1)) {
                                            setTimeout(function () {
                                                calendar.updateEvent({
                                                    "allDay": false,
                                                    "end": '2023-10-11T13:00',
                                                    "id": ++latest_id,
                                                    "resource": tempShift.resource,
                                                    "slot": tempShift.slot,
                                                    "start": '2023-10-11T07:00',
                                                    "title": tempShift.title,
                                                    "staff": tempShift.staff,
                                                    "color": tempShift.color,
                                                });
                                            }, 200);


                                        }



                                        deleteShift = false;
                                        popup.close();
                                    }
                                } : false

                            });
                            return false;
                        }
                        var foremans_staff = staff.filter(x => x.id == tempShift.resource);
                        let difference = array_diff(tempShift.staff, foremans_staff[0].staff_key.map(String));
                        tempShift.color = difference.length > 0 ? "red" : "blue";

                        tempShift.id = "";
                        saveProject(tempShift);

                        let event_array = [];
                        let endDate = new Date(tempShift.end); // today
                        let startDate = new Date(tempShift.start); // Jan 1st 2017
                        let daysOfYear = [];
                        for (let day = startDate; day <= endDate; day.setDate(day.getDate() + 1)) {
                            setTimeout(function () {
                                calendar.updateEvent({
                                    "allDay": false,
                                    "end": '2023-10-11T13:00',
                                    "id": ++latest_id,
                                    "resource": tempShift.resource,
                                    "slot": tempShift.slot,
                                    "start": '2023-10-11T07:00',
                                    "title": tempShift.title,
                                    "staff": tempShift.staff,
                                    "color": tempShift.color,
                                });
                            }, 200);


                        }



                        deleteShift = false;
                        popup.close();
                    },
                    cssClass: 'mbsc-popup-button-primary'
                }
            ]
        });

        $("#project-details").html("")
        $(".search-project").show();
        range.setVal([tempShift.start, tempShift.end]);
        popup.open();

    }

    function createEditPopup(args) {
        var ev = args.event;
        var resource = staff.find(function (r) {
            return r.id === ev.resource
        });
        var slot = slots.find(function (s) {
            return s.id === ev.slot
        });
        var headerText = '<div>' + ev.title + '</div>';

        // show delete button inside edit popup
        $(".from_to_fields").hide();
        $deleteButton.show();

        deleteShift = false;
        restoreShift = true;

        // // set popup header text and buttons for editing
        popup.setOptions({
            headerText: headerText,
            buttons: [
                'cancel',
                @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Project Manager'))
                {
                    text: 'Save',
                    keyCode: 'enter',
                    handler: function () {
                        var msg = "";
                        var color = "";
                        var override = "";
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: "{{ url('check-leave') }}",
                            async: false,
                            data: {
                                id: ev.id,
                                foreman_id: resource.id,
                                slot: tempShift.slot,
                                staff_id: tempShift.staff,
                                from_date: dateFormat(tempShift.start),
                                to_date: dateFormat(tempShift.end)
                            },
                            type: 'POST',
                            dataType: 'json',
                            success: function (result) {
                                if (result.success == "true") {
                                    msg = result.msg;
                                    color = result.color;
                                    if (result.override == "true") {
                                        console.log(override);
                                        override = "true";
                                    }
                                }
                            }
                        });
                        if (msg != "") {
                            mobiscroll.snackbar({
                                duration: 3000,
                                message: msg,
                                color: color,
                                display: 'top',
                                button: override == "true" ? {

                                    text: 'Override',
                                    action: function () {
                                        // update event with the new properties on save button click
                                        var data = {
                                            id: ev.id,
                                            title: $name.val(),
                                            notes: $notes.val(),
                                            start: new Date(tempShift.start),
                                            end: new Date(tempShift.end),
                                            staff: tempShift.staff,
                                            resource: resource.id,
                                            color: resource.color,
                                            slot: slot.id,
                                        }


                                        if (typeof tempShift.staff !== 'undefined') {
                                            var foremans_staff = staff.filter(x => x.id == tempShift.resource);
                                            let difference = array_diff(tempShift.staff, foremans_staff[0].staff_key.map(String));
                                            data.color = difference.length > 0 ? "red" : "blue";
                                        } else {
                                            data.color = "red";
                                        }
                                        calendar.updateEvent(data);
                                        setTimeout(function () {
                                            saveProject(data);
                                        }, 100);
                                        restoreShift = false;;
                                        popup.close();
                                    }
                                } : false

                            });
                            return false;
                        }
                        // update event with the new properties on save button click
                        var data = {
                            id: ev.id,
                            title: $name.val(),
                            notes: $notes.val(),
                            start: new Date(tempShift.start),
                            end: new Date(tempShift.end),
                            staff: tempShift.staff,
                            resource: resource.id,
                            color: resource.color,
                            slot: slot.id,
                        }


                        if (typeof tempShift.staff !== 'undefined') {
                            var foremans_staff = staff.filter(x => x.id == tempShift.resource);
                            let difference = array_diff(tempShift.staff, foremans_staff[0].staff_key.map(String));
                            data.color = difference.length > 0 ? "red" : "blue";
                        } else {
                            data.color = "red";
                        }
                        calendar.updateEvent(data);
                        setTimeout(function () {
                            saveProject(data);
                        }, 100);
                        restoreShift = false;;
                        popup.close();
                    },
                    cssClass: 'mbsc-popup-button-primary'
                }
                @endif
            ]
        });

        // fill popup with the selected event data
        $notes.mobiscroll('getInst').value = ev.notes || '';
        $name.mobiscroll('getInst').value = ev.title || '';
        if (ev.staff != "" && ev.staff != null) {
            staffpicker.setVal(ev.staff.map(String));

        }
        range.setVal([ev.start, ev.end]);
        modalData(ev.id);
        $(".search-project").hide();
        popup.open();
    }
    var now = new Date();

    calendar = $('#demo-employee-shifts-calendar').mobiscroll().eventcalendar({

        view: {
            timeline: {
                type: 'week',
                startDay: now.getDay(),
                endDay: now.getDay() - 1,
            }
        },
        colors: [{
            background: '#f0f8ff',
            recurring: {
                repeat: 'weekly',
                weekDays: 'SU'
            }
        },
        {
            background: '#f0f8ff',
            recurring: {
                repeat: 'weekly',
                weekDays: 'SA'
            }
        }
        ],
        eventOverlap: false,
        data: shifts,
        @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Project Manager'))
        dragToCreate: true,
        dragToResize: true,
        dragToMove: true,
        clickToCreate: true,
        @endif
        resources: staff,
        invalid: invalid,
        slots: slots,
        extendDefaultEvent: function (ev) {
            var d = ev.start;
            var start = new Date(d.getFullYear(), d.getMonth(), d.getDate(), ev.slot == 1 ? 7 : 12);
            var end = new Date(d.getFullYear(), d.getMonth(), d.getDate(), ev.slot == 1 ? 13 : 18);

            return {
                title: "New Project",
                start: start,
                end: end,
                resource: ev.resource,
            };
        },

        onEventUpdate: function (args, inst) {
            var msg = "";
            var color = "";
            var override = "";
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ url('check-leave') }}",
                async: false,
                data: {
                    id: args.event.id,
                    foreman_id: args.event.resource,
                    slot: args.event.slot,
                    staff_id: args.event.staff,
                    from_date: dateFormat(args.event.start),
                    to_date: dateFormat(args.event.end)
                },
                type: 'POST',
                dataType: 'json',
                success: function (result) {
                    if (result.success == "true") {
                        msg = result.msg;
                        color = result.color;
                        if (result.override == "true") {
                            console.log(override);
                            override = "true";
                        }
                    }
                }
            });
            if (msg != "") {
                mobiscroll.snackbar({
                    duration: 3000,
                    message: msg,
                    color: color,
                    display: 'top',
                    button: override == "true" ? {
                        text: 'Override',
                        action: function () {
                            setTimeout(function () {
                                calendar.updateEvent(args.event);
                                saveProject(args.event);
                            }, 500);
                        }
                    } : false
                });
                return false;
            }
            saveProject(args.event);
        },
        onEventCreate: function (args) {
            var samedayEvent = calendar.getEvents(args.event.start);
            if (samedayEvent.length > 0) {
                if (samedayEvent.filter(v => v.resource == args.event.resource && v.slot == args.event.slot).length > 0) {
                    mobiscroll.snackbar({
                        duration: 2000,
                        message: 'Already assigned to project !',
                        color: 'warning',
                        display: 'top'
                    });
                    return false;
                }
            }

            $name.val('');
            $notes.val('');
            projectpicker.setVal('');
            $staff.find("option").prop("selected", false);
            tempShift = args.event;
            setTimeout(function () {
                createAddPopup(args);
            }, 100);
        },
        onEventClick: function (args,) {
            oldShift = $.extend({}, args.event);
            tempShift = args.event;

            if (!popup.isVisible()) {
                createEditPopup(args);
            }
        },
        renderResource: function (resource) {
            var hidden_html = '';
            resource.staff_name.forEach(function (item) {
                hidden_html += '<button class="foreman-pill">' + item + '</button>';
            });
            return '<div class="employee-shifts-cont links">' +
                '<div class="employee-shifts-name">' + resource.name + '<div>' +
                '<div class="hidden_staff" style="display:none">' + hidden_html + '</div>' +
                '</div>';
        },
    }).mobiscroll('getInst');

    range = $('#demo-employee-shifts-date').mobiscroll().datepicker({
        controls: ['date'],
        select: 'range',
        display: 'anchored',
        showRangeLabels: false,
        touchUi: false,
        startInput: '#employee-shifts-start',
        endInput: '#employee-shifts-end',
        timeWheels: '|Y-m-d h:mm A|',
        onChange: function (args) {
            var date = args.value;

            // update shift's start/end date
            tempShift.start = date[0];
            tempShift.end = date[1] ? date[1] : date[0];
        }
    }).mobiscroll('getInst');

    popup = $('#demo-employee-shifts-popup').mobiscroll().popup({
        display: 'bottom',
        contentPadding: false,
        fullScreen: false,
        maxWidth: 850,
        onClose: function () {
            if (deleteShift) {
                calendar.removeEvent(tempShift);
            } else if (restoreShift) {
                calendar.updateEvent(oldShift);
            }
        },
        responsive: {
            xlarge: {
                display: 'center',
                layout: 'fixed',
                height: 850,
                maxHeight: 950,
                width: 850,
                closeOnOverlayTap: false,
                fullScreen: false,
                touchUi: false,
                showOverlay: true
            }
        }
    }).mobiscroll('getInst');

    var projectpicker = $('#employee-project-name').mobiscroll().select({
        inputElement: document.getElementById('employee-project-input'),
        display: 'anchored',
        filter: true,
        touchUi: false,

    }).mobiscroll('getInst');

    var staffpicker = $('#employee-staff-select').mobiscroll().select({
        inputElement: document.getElementById('employee-staff-input'),
        selectMultiple: true,
        touchUi: false,
        filter: true,
    }).mobiscroll('getInst');

    $notes.on('change', function (ev) {
        // update current event's title
        tempShift.notes = ev.target.value;
    });

    $staff.on('change', function (e) {
        // update current event's title
        var options = e.target.options;
        tempShift.staff = [];
        for (var i = 0, l = options.length; i < l; i++) {
            if (options[i].selected) {
                tempShift.staff.push(options[i].value);
            }
        }
    });

    $name.on('change', function (ev) {
        // update current event's title
        tempShift.title = ev.target.value;
    });

    $deleteButton.on('click', function () {
        // delete current event on button click
        calendar.removeEvent(tempShift);

        // save a local reference to the deleted event
        var deletedShift = tempShift;

        popup.close();
        deleteProject(tempShift.id);
        mobiscroll.snackbar({
            button: {
                action: function () {
                    calendar.addEvent(deletedShift);
                },
                text: 'Undo'
            },
            duration: 10000,
            message: 'Shift deleted'
        });
    });
    });

    function dateFormat(d) {
        const padL = (nr, len = 2, chr = `0`) => `${nr}`.padStart(2, chr);
        var dt = new Date(d);
        var new_date = `${dt.getFullYear()}-${padL(dt.getMonth() + 1)}-${padL(dt.getDate())}T${padL(dt.getHours())}:${padL(dt.getMinutes())}`
        return new_date;
    }

    function saveProject(data) {
        data.start = dateFormat(data.start);
        data.end = dateFormat(data.end);

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ url('saveProjectSchedule') }}",
            data: data,
            type: 'POST',
            dataType: 'json',
            success: function (result) {

                console.log("===== " + result + " =====");

            }
        });
    }

    function deleteProject(id) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ url('deleteProjectSchedule') }}",
            data: {
                id: id
            },
            type: 'POST',
            dataType: 'json',
            success: function (result) {

                console.log("===== " + result + " =====");

            }
        });
    }


    function modalData(id) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ url('modal-data') }}",
            data: {
                id: id
            },
            type: 'POST',
            success: function (result) {
                // $projectDetails.html(result);
                $("#project-details").html(result)
            }
        });
    }

    var popup1;
    setTimeout(function () {
        popup1 = $('#foreman-staff-modal').mobiscroll().popup({
            display: 'bubble',
            anchor: $(".links")[1],
            buttons: []
        }).mobiscroll('getInst');

    }, 500)

    $(document).on("mouseenter", '.links', function () {
        var ind = $(".links").index($(this));
        var html = $(this).find(".hidden_staff").html();
        $("#foreman-staff-modal").find("div").html(html);
        popup1.setOptions({
            anchor: $(".links")[ind]
        });
        popup1.open();
        return false;
    });

    $(document).on("mouseleave", '#foreman-staff-modal', function () {
        console.log("yes")
        popup1.close();
        return false;
    });
</script>
@endsection