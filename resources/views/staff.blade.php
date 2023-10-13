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
            <span>Staff Management</span>
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
        <div class="col-md-3">
          <div class="add-new-c">
            <img src="img/plus.png"><span id="add_staff">Add New Staff</span>
          </div>
        </div>
        <div class="col-md-4 text-r select-style">

        </div>
        <div class="col-md-2 text-r select-style">
          <a href="{{url('assign-team')}}" class="btn btn-color btn-secondary">Assign team</a>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12" id="staffs">
          @if(count($staffs)>0)
          <table class="table table-w-80">
            <thead class="border-n">
              <tr>
                <th>Name</th>
                <th>Email ID</th>
              </tr>
            </thead>
            <tbody class="tr-border td-styles tr-hover">
              @foreach($staffs as $staff)
              <tr>
                <td><b>{{ucfirst($staff->name)}}</b></td>
                <td>{{$staff->email}}</td>
                <td><img src="img/dots.png" id="dropdownMenuButton" data-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                  <div class="dropdown-menu">
                    <a href="javascript:void(0)" data-id='{{$staff->id}}' class="edit dropdown-item">Edit</a>
                    <a href="javascript:void(0)" data-id='{{$staff->id}}' class="delete dropdown-item">Delete</a>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          @else
          <p>No staff found here.</p>
          @endif
        </div>
      </div>
    </div>
  </div>

</div>

<div class="modal fade" tabindex="-1" role="dialog" id="staff_form">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><span id="modal_title"></span> Staff</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div style="display:none" id="modal_staff_id"></div>
          <div class="form-group">
            <label for="name" class="col-form-label">Name:</label>
            <input type="text" name="name" class="form-control" id="name">
          </div>
          <div class="form-group">
            <label for="email" class="col-form-label">Email:</label>
            <input type="email" name="email" class="form-control" id="email">
          </div>
          <div class="form-group">
            <label for="contact" class="col-form-label">Contact:</label>
            <input type="text" name="contact" class="form-control" id="contact">
          </div>
          <div class="form-group">
            <label for="password" class="col-form-label">Password:</label>
            <input type="password" name="new_password" autocomplete="off" class="form-control" id="password">
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="submit_staff" class="save_button btn btn-secondary">Save</button>
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
    refreshtable();
  });

  function refreshtable() {
    var search = $("#search").val();
    $.ajax({
      type: 'POST',
      url: "{{ route('staff.get') }}",
      data: {
        search: search,
      },
      success: function(data) {
        $("#staffs").html(data)
      }
    });
  }


  $(document).on("click", ".edit", function() {
    let id = $(this).data('id');
    jQuery.ajax({
      type: 'POST',
      url: "{{ route('staff.edit') }}",
      data: {
        id: id,
      },
      success: function(data) {
        $("#modal_title").html("Edit");
        $("#modal_staff_id").text(data.id);
        $("#name").val(data.name);
        $("#email").val(data.email);
        $("#contact").val(data.contact);
        $("#password").val("");


        $(".save_button").attr("id", "update_staff")
        $("#staff_form").modal('show');

      }
    })

  });

  $(document).on("click", "#add_staff", function() {
    $(".save_button").attr("id", "submit_staff")
    $("#modal_staff_id").text("");
    $("#modal_title").html("Add");
    $("#name").val("");
    $("#email").val("");
    $("#contact").val("");
    $("#password").val("");
    $("#staff_form").modal('show');
  });

  $(document).on("click", ".close", function() {
    $("#staff_form").modal('hide');
  });




  $(document).ready(function() {

    $(document).on('click', "#submit_staff", function() {
      var name = $("#name").val();
      var email = $("#email").val();
      var contact = $("#contact").val();
      var password = $("#password").val();
      if (name == "") {
        alert("Please enter name.");
        return false;
      }
      if (email == "") {
        alert("Please enter email.");
        return false;
      }

      jQuery.ajax({
        type: 'POST',
        url: "{{ route('staff.add') }}",
        data: {
          name: name,
          email: email,
          contact: contact,
          password: password,
        },
        success: function(data) {
          $("#staff_form").modal('hide');
          $("#name").val("");
          $("#email").val("");
          $("#contact").val("");
          $("#password").val("");
          Toast.fire({
            icon: 'success',
            title: 'Staff has been saved successfully'
          }).then((res) => {
            window.location.reload();
          });
          refreshtable();

        }
      });
    });
    $(document).on('click', "#update_staff", function() {
      console.log("yes");
      var name = $("#name").val();
      var email = $("#email").val();
      var contact = $("#contact").val();
      var password = $("#password").val();
      var id = $("#modal_staff_id").text();

      jQuery.ajax({
        type: 'POST',
        url: "{{ route('staff.update') }}",
        data: {
          id: id,
          name: name,
          email: email,
          contact: contact,
          password: password,
        },
        success: function(data) {
          $("#staff_form").modal('hide');
          $("#name").val("");
          $("#email").val("");
          $("#contact").val("");
          $("#password").val("");
          $("#modal_staff_id").text("");
          Toast.fire({
            icon: 'success',
            title: 'Staff has been updated successfully'
          }).then((res) => {
            window.location.reload();
          });
          refreshtable();
        }
      });
    });
  });

  $(document).on("click", ".delete", function() {
    let id = $(this).data('id');
    if(confirm("Do you want to delete this staff ?"))
    {
    jQuery.ajax({
      type: 'POST',
      url: "{{ route('staff.delete') }}",
      data: {
        id: id,
      },
      success: function(data) {
        refreshtable();

      }
    })
  }
  });
</script>
@endsection