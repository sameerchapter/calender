@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<div id="content">
  <div class="container">
    <div class="card-new ptb-50">
      <div class="row">
        <div class="col-md-12">
          <div class="form-head">
            <span>Leave Management</span>
          </div>
        </div>
      </div>
      <div class="row d-flex pb-40">
        <div class="col-md-3">
          <div class="inp-relv">
            <img src="img/frame-2@2x.svg">
            <input type="seach" name="search" id="search" placeholder="Search">
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12" id="staffs">
          <table class="table table-w-80">
            <thead class="border-n">
              <tr>
                <th>Name</th>
                <th>Email ID</th>
                <th>User type</th>
                <th>Leaves</th>
              </tr>
            </thead>
            <tbody class="tr-border td-styles tr-hover">
              @foreach($foremans as $foreman)
              <tr>
                <td><b>{{ucfirst($foreman->name)}}</b></td>
                <td>{{$foreman->email}}</td>
                <td>Foreman</td>
                <td><button class="btn btn-color btn-primary leaves" data-name="{{$foreman->name}}" data-id="{{$foreman->id}}" data-type="1">Manage</button></td>
              </tr>
              @endforeach
              @foreach($staffs as $staff)
              <tr>
                <td><b>{{ucfirst($staff->name)}}</b></td>
                <td>{{$staff->email}}</td>
                <td>Staff</td>
                <td><button class="btn btn-color btn-primary leaves" data-name="{{$staff->name}}" data-id="{{$staff->id}}" data-type="2">Manage</button></td>
              </tr>
              @endforeach
            </tbody>
          </table>

        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="leaves_form">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"> Staff Leaves</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div style="display:none" id="leave_user_id"></div>
      <div style="display:none" id="leave_user_type"></div>
      <div style="display:none" id="leave_user_name"></div>

      <form onsubmit="event.preventDefault();saveLeaves()">
        <div class="modal-body">
          <div id="repeater">
            <!-- Repeater Heading -->
            <div class="repeater-heading">
              <button type="button" class="pull-right btn btn-primary btn-color repeater-add-btn"> Add</button>
            </div>
            <br>

          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="save_leaves" class=" btn btn-secondary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="hidden_html" style="display:none">
  <div class="items leave_items" style="margin-bottom: 45px;margin-top: 13%;">
    <div class="item-content">
      <div class="row">
        <div class="col-md-1"></div>
        <label class="col-md-5">From Date:<input type="date" required name="from_date[]" class="from_date form-control"></label>
        <label class="col-md-5">To Date:<input type="date" required name="to_date[]" class="to_date form-control"></label>
        <div class="col-md-1"></div>
      </div>
      <div class="pull-right mt-1 mb-3 repeater-remove-btn">
        <button id="remove-btn" class="btn btn-danger " onclick="$(this).parents('.items').remove();getIframehtml();">
          Remove
        </button>
      </div>
    </div>

  </div>
</div>

<script>
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  $('#search').on('keyup change', function() {
    var search = $("#search").val();
    $.ajax({
      type: 'POST',
      url: "{{ route('leaves.get') }}",
      data: {
        search: search,
      },
      success: function(data) {
        $("#staffs").html(data)
      }
    });
  });

  $('.repeater-add-btn').click(function() {
    $("#repeater").append($(".hidden_html").html());
  })

  $(document).on("click", ".leaves", function() {

    let id = $(this).data('id');
    let type = $(this).data('type');
    let name = $(this).data('name');
    
 

    $("#leave_user_id").text(id);
    $("#leave_user_type").text(type);
    $("#leave_user_name").text(name);


    jQuery.ajax({
      type: 'POST',
      url: "{{ route('user.leaves') }}",
      dataType: "json",
      data: {
        user_id: id,
        user_type: type
      },
      success: function(data) {
        $("#leaves_form").modal('show');
        $("#leaves_form").find(".leave_items").remove();
        data.map(function(item) {
          $("#repeater").append($(".hidden_html").html());
          $("#repeater").find(".from_date:last").val(new Date(item.from_date).toISOString().split('T')[0]);
          $("#repeater").find(".to_date:last").val(new Date(item.to_date).toISOString().split('T')[0]);
        });
      }
    })

  });


  function saveLeaves() {
    let id = $("#leave_user_id").text();
    let type = $("#leave_user_type").text();
    let name = $("#leave_user_name").text();

    var to_dates = $("#leaves_form").find("input[name='to_date[]']")
      .map(function() {
        return $(this).val();
      }).get();
    var from_dates = $("#leaves_form").find("input[name='from_date[]']")
      .map(function() {
        return $(this).val();
      }).get();

    jQuery.ajax({
      type: 'POST',
      url: "{{ route('userleaves.save') }}",
      data: {
        user_id: id,
        user_type: type,
        user_name: name,
        to_dates: to_dates,
        from_dates: from_dates
      },
      success: function(data) {
        $("#leaves_form").modal('hide');

        Toast.fire({
          icon: 'success',
          title: "User leave saved successfuly."
        })
      }
    })

  }
</script>
<script></script>
@endsection
