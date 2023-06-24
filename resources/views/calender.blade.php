@extends('layouts.app')

@section('content')
<style>
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
        font-weight: 600;
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
        border-radius: 0px !important;
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
        text-decoration: none;
        display: inline-block;
        margin: 4px 2px;
        cursor: pointer;
        border-radius: 16px;
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
                            <div class="mbsc-form-group search-project">
                                <label>
                                    Search Project
                                    <input mbsc-input id="employee-project-input" data-dropdown="true" />
                                </label>
                                <select id="employee-project-name">
                                    <option>Search Project</option>
                                    @foreach($projects as $project)
                                    <option value="{{$project->id}}">{{$project->address}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mbsc-form-group">
                                <label>
                                    Staff
                                    <input mbsc-input id="employee-staff-input" data-dropdown="false" data-tags="true" />
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
                            <div class="mbsc-button-group">
                                <button class="mbsc-button-block" id="employee-shifts-delete" mbsc-button data-color="danger" data-variant="outline">Delete Project</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    mobiscroll.setOptions({
        theme: 'ios',
        themeVariant: 'light',
    });

    $(function() {
        var calendar;
        var popup;
        var oldShift;
        var tempShift;
        var deleteShift;
        var formatDate = mobiscroll.util.datetime.formatDate;
        var $notes = $('#employee-project-notes');
        var $name = $('#employee-project-input');
        var $staff = $("#employee-staff-select");
        var $deleteButton = $('#employee-shifts-delete');
        var $projectDetails = $('#project-details');
        var latest_id = <?php echo $latest_id; ?>;
        var staff = [
            <?php foreach ($foreman as $res) { ?> {
                    id: "<?php echo $res['id']; ?>",
                    staff_name: <?php print(json_encode($res->staff->pluck('name'))); ?>,
                    staff_key: <?php print(json_encode($res->staff->pluck('id'))); ?>,
                    name: "<?php echo ucfirst($res['name']); ?>",
                    color: '#80cff7',
                },
            <?php
            }
            ?>
        ];

        var shifts = [
            <?php foreach ($schedules as $res) { ?> {
                    id: "<?php echo $res->id; ?>",
                    start: "<?php echo $res->start; ?>",
                    end: "<?php echo $res->end; ?>",
                    title: "<?php echo $res->project_name; ?>",
                    notes: "<?php echo $res->notes; ?>",
                    resource: "<?php echo $res->foreman_id; ?>",
                    staff_id: <?php print(json_encode($res->staff_id)); ?>,
                    slot: <?php echo $res->slot; ?>,
                    color: "<?php $f_staff_collection =  $foreman->filter(function ($f) use ($res) {
                                return $f->id == $res->foreman_id;
                            })->values();
                            $f_staff_array = (count($f_staff_collection) > 0) ? $f_staff_collection[0]->staff->pluck('id')->toArray() : [];
                            $staff_array = is_array($res->staff_id) ? $res->staff_id : [];
                            echo empty(array_diff($f_staff_array, $staff_array)) ? "blue" : "red"; ?>"
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

        var invalid = [{
            start: '2023-06-11T00:00',
            end: '2023-06-11T23:59',
            resource: 4,
            slot: 1
        }, {
            start: '2023-06-09T00:00',
            end: '2023-06-09T23:59',
            resource: 2,
            slot: 2
        }];

        function array_diff(array1, array2) {
            var difference = $.grep(array1, function(el) {
                return $.inArray(el, array2) < 0
            });
            return difference.concat($.grep(array2, function(el) {
                return $.inArray(el, array1) < 0
            }));;
        }

        function createAddPopup(args) {
            // hide delete button inside add popup
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
                success: function(result) {
                    staffpicker.setVal(result.map(String));
                    tempShift.staff = result.map(String);
                }
            });
            deleteShift = false;
            restoreShift = false;
            var slot = slots.find(function(s) {
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
                        handler: function() {
                            var foremans_staff = staff.filter(x => x.id == tempShift.resource);
                            let difference = array_diff(tempShift.staff, foremans_staff[0].staff_key.map(String));
                            tempShift.color = difference.length > 0 ? "red" : "blue";
                            calendar.updateEvent(tempShift);
                            setTimeout(function() {
                                tempShift.id = "";
                                saveProject(tempShift);
                            }, 100);
                            deleteShift = false;
                            popup.close();
                        },
                        cssClass: 'mbsc-popup-button-primary'
                    }
                ]
            });

            $("#project-details").html("")
            $(".search-project").show();
            popup.open();

        }

        function createEditPopup(args) {
            var ev = args.event;
            var resource = staff.find(function(r) {
                return r.id === ev.resource
            });
            var slot = slots.find(function(s) {
                return s.id === ev.slot
            });
            var headerText = '<div>' + $name.val() + '</div>';

            // show delete button inside edit popup
            $deleteButton.show();

            deleteShift = false;
            restoreShift = true;

            // // set popup header text and buttons for editing
            popup.setOptions({
                headerText: headerText,
                buttons: [
                    'cancel',
                    {
                        text: 'Save',
                        keyCode: 'enter',
                        handler: function() {

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
                            var foremans_staff = staff.filter(x => x.id == tempShift.resource);
                            let difference = array_diff(tempShift.staff, foremans_staff[0].staff_key.map(String));
                            data.color = difference.length > 0 ? "red" : "blue";
                            calendar.updateEvent(data);
                            setTimeout(function() {
                                saveProject(data);
                            }, 100);
                            restoreShift = false;;
                            popup.close();
                        },
                        cssClass: 'mbsc-popup-button-primary'
                    }
                ]
            });

            // fill popup with the selected event data
            $notes.mobiscroll('getInst').value = ev.notes || '';
            $name.mobiscroll('getInst').value = ev.title || '';
            if (ev.staff_id != "" && ev.staff_id != null) {
                staffpicker.setVal(ev.staff_id.map(String));

            }
            modalData(ev.id);
            $(".search-project").hide();
            popup.open();
        }
        var now = new Date();
        calendar = $('#demo-employee-shifts-calendar').mobiscroll().eventcalendar({
            view: {
                timeline: {
                    type: 'week',
                    eventList: true,
                    startDay: now.getDay(),
                    endDay: now.getDay() - 1
                }
            },
            data: shifts,
            dragToCreate: false,
            dragToResize: false,
            dragToMove: true,
            clickToCreate: true,
            resources: staff,
            invalid: invalid,
            slots: slots,
            extendDefaultEvent: function(ev) {
                var d = ev.start;
                console.log(d)
                var start = new Date(d.getFullYear(), d.getMonth(), d.getDate(), ev.slot == 1 ? 7 : 12);
                var end = new Date(d.getFullYear(), d.getMonth(), d.getDate(), ev.slot == 1 ? 13 : 18);

                return {
                    title: "New Project",
                    start: start,
                    end: end,
                    resource: ev.resource
                };
            },
            onEventDragEnd: function(args, inst) {

                setTimeout(function() {
                    saveProject(args.event);
                }, 500);
            },
            onEventCreate: function(args, inst) {
                $name.val('');
                $notes.val('');
                $staff.find("option").prop("selected", false);
                tempShift = args.event;
                tempShift.id = ++latest_id;
                setTimeout(function() {
                    createAddPopup(args);
                }, 100);
            },
            onEventClick: function(args, inst) {
                oldShift = $.extend({}, args.event);
                tempShift = args.event;

                if (!popup.isVisible()) {
                    createEditPopup(args);
                }
            },
            renderResource: function(resource) {
                var hidden_html = '';
                resource.staff_name.forEach(function(item) {
                    hidden_html += '<button class="foreman-pill">' + item + '</button>';
                });
                return '<div class="employee-shifts-cont links">' +
                    '<div class="employee-shifts-name">' + resource.name + '<div>' +
                    '<div class="hidden_staff" style="display:none">' + hidden_html + '</div>' +
                    '</div>';
            },
        }).mobiscroll('getInst');

        popup = $('#demo-employee-shifts-popup').mobiscroll().popup({
            display: 'bottom',
            contentPadding: false,
            fullScreen: false,
            maxWidth: 850,
            onClose: function() {
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
                    height:850,
                    maxHeight:950,
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

        $notes.on('change', function(ev) {
            // update current event's title
            tempShift.notes = ev.target.value;
        });

        $staff.on('change', function(e) {
            // update current event's title
            var options = e.target.options;
            tempShift.staff = [];
            for (var i = 0, l = options.length; i < l; i++) {
                if (options[i].selected) {
                    tempShift.staff.push(options[i].value);
                }
            }
        });

        $name.on('change', function(ev) {
            // update current event's title
            tempShift.title = ev.target.value;
        });

        $deleteButton.on('click', function() {
            // delete current event on button click
            calendar.removeEvent(tempShift);

            // save a local reference to the deleted event
            var deletedShift = tempShift;

            popup.close();
            deleteProject(tempShift.id);
            mobiscroll.snackbar({
                button: {
                    action: function() {
                        calendar.addEvent(deletedShift);
                    },
                    text: 'Undo'
                },
                duration: 10000,
                message: 'Shift deleted'
            });
        });
    });

    function saveProject(data) {
        const padL = (nr, len = 2, chr = `0`) => `${nr}`.padStart(2, chr);
        var dt = new Date(data.start);
        data.start = `${dt.getFullYear()}-${
    padL(dt.getMonth()+1)}-${
    padL(dt.getDate())}T${
    padL(dt.getHours())}:${
    padL(dt.getMinutes())}`
        var dt = new Date(data.end);
        data.end = `${dt.getFullYear()}-${
    padL(dt.getMonth()+1)}-${
    padL(dt.getDate())}T${
    padL(dt.getHours())}:${
    padL(dt.getMinutes())}`;
        console.log(new Date(data.start));
        console.log(new Date(data.end));
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ url('saveProjectSchedule') }}",
            data: data,
            type: 'POST',
            dataType: 'json',
            success: function(result) {

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
            success: function(result) {

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
            success: function(result) {
                // $projectDetails.html(result);
                $("#project-details").html(result)
            }
        });
    }

    var popup1;
    setTimeout(function() {
        popup1 = $('#foreman-staff-modal').mobiscroll().popup({
            display: 'bubble',
            anchor: $(".links")[1],
            buttons: []
        }).mobiscroll('getInst');

    }, 500)

    $(document).on("mouseenter", '.links', function() {
        var ind = $(".links").index($(this));
        var html = $(this).find(".hidden_staff").html();
        $("#foreman-staff-modal").find("div").html(html);
        popup1.setOptions({
            anchor: $(".links")[ind]
        });
        popup1.open();
        return false;
    });

    $(document).on("mouseleave", '#foreman-staff-modal', function() {
        console.log("yes")
        popup1.close();
        return false;
    });
</script>
@endsection