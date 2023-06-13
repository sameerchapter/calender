@extends('layouts.app')

@section('content')
<style>
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
</style>

<div id="content">
    <div class="container main">
        <div class="card-new ptb-50">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-head">
                        <span>Projects</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="Col-md-11">

                        <div id="demo-employee-shifts-calendar" class="md-employee-shifts"></div>

                        <div id="demo-employee-shifts-popup" class="employee-shifts-popup">

                            <div class="mbsc-form-group">
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
                                    Notes
                                    <textarea mbsc-textarea id="employee-project-notes"></textarea>
                                </label>
                            </div>
                            <div class="mbsc-button-group">
                                <button class="mbsc-button-block" id="employee-shifts-delete" mbsc-button data-color="danger" data-variant="outline">Delete shift</button>
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
        themeVariant: 'light'
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
        var $deleteButton = $('#employee-shifts-delete');

        var staff = [
            <?php foreach ($foreman as $res) { ?> {
                    id: "<?php echo $res['id']; ?>",
                    name: "<?php echo ucfirst($res['name']); ?>",
                    color: '#80cff7',
                },
            <?php
            }
            ?>
        ];

        var shifts = [
            <?php foreach ($schedules as $res) { ?> {
                id:"<?php echo $res->event_id; ?>",
                start: "<?php echo $res->start; ?>",
            end: "<?php echo $res->end; ?>",
            title: "<?php echo $res->project_name; ?>",
            notes: "<?php echo $res->notes; ?>",
            resource: "<?php echo $res->foreman_id; ?>",
            slot: <?php echo $res->slot; ?>
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

        function createAddPopup(args) {
            // hide delete button inside add popup
            $deleteButton.hide();
            deleteShift = true;
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
                            calendar.updateEvent(tempShift);
                            saveProject(tempShift)
                            deleteShift = false;
                            popup.close();
                        },
                        cssClass: 'mbsc-popup-button-primary'
                    }
                ]
            });
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
            var headerText = '<div>Edit ' + resource.name + '\'s project</div><div class="employee-shifts-day">' +
                formatDate('DDDD', new Date(ev.start)) + ' ' + slot.name + ',' + formatDate('DD MMMM YYYY', new Date(ev.start)) + '</div>';

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
                            var data={
                                id: ev.id,
                                title: $name.val(),
                                notes: $notes.val(),
                                start: new Date(tempShift.start),
                            end: new Date(tempShift.end),
                            resource: resource.id,
                            color: resource.color,
                            slot: slot.id,
                            }
                            calendar.updateEvent(data);
                            console.log(data);
                            saveProject(data);
                            restoreShift = false;
                            popup.close();
                        },
                        cssClass: 'mbsc-popup-button-primary'
                    }
                ]
            });

            // fill popup with the selected event data
            $notes.mobiscroll('getInst').value = ev.notes || '';
            $name.mobiscroll('getInst').value = ev.title || '';
            popup.open();
        }

        calendar = $('#demo-employee-shifts-calendar').mobiscroll().eventcalendar({
            view: {
                timeline: {
                    type: 'week',
                    eventList: true,
                    startDay: 1,
                    endDay: 5
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
            extendDefaultEvent: function (ev) {
            var d = ev.start;
            console.log(d)
            var start = new Date(d.getFullYear(), d.getMonth(), d.getDate(), ev.slot == 1 ? 7 : 12);
            var end = new Date(d.getFullYear(), d.getMonth(), d.getDate(), ev.slot == 1 ? 13 : 18);

            return {
                title:"New Project",
                start: start,
                end: end,
                resource: ev.resource
            };
        },
            onEventDragEnd: function (args, inst) { 
                console.log(inst)
                saveProject(args.event);
            },
            onEventCreate: function(args, inst) {
                console.log("test");
                $name.val('');
                $notes.val('');
                tempShift = args.event;
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
                return '<div class="employee-shifts-cont">' +
                    '<div class="employee-shifts-name">' + resource.name + '</div>' +
                    '</div>';
            },
        }).mobiscroll('getInst');

        popup = $('#demo-employee-shifts-popup').mobiscroll().popup({
            display: 'bottom',
            contentPadding: false,
            fullScreen: false,
            onClose: function() {
                if (deleteShift) {
                    calendar.removeEvent(tempShift);
                } else if (restoreShift) {
                    calendar.updateEvent(oldShift);
                }
            },
            responsive: {
                medium: {
                    display: 'center',
                    width: 400,
                    closeOnOverlayTap: false,
                    fullScreen: false,
                    touchUi: false,
                    showOverlay: false
                }
            }
        }).mobiscroll('getInst');

        var selectpicker = $('#employee-project-name').mobiscroll().select({
            inputElement: document.getElementById('employee-project-input'),
            display: 'anchored',
            filter: true,
            touchUi: false,

        }).mobiscroll('getInst');
        $notes.on('change', function(ev) {
            // update current event's title
            tempShift.notes = ev.target.value;
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

    function saveProject(data)
    {
        data.start=data.start.toISOString();
        data.end=data.end.toISOString();
        $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url : "{{ url('saveProjectSchedule') }}",
        data : data,
        type : 'POST',
        dataType : 'json',
        success : function(result){

            console.log("===== " + result + " =====");

        }
    });
    }

    function deleteProject(id)
    {
        $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url : "{{ url('deleteProjectSchedule') }}",
        data : {id:id},
        type : 'POST',
        dataType : 'json',
        success : function(result){

            console.log("===== " + result + " =====");

        }
    });
    }
</script>
@endsection