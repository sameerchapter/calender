@extends('layouts.app')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.js"></script>

<div id="content">
    <div class="container booking-form-w">
        <div class="row d-flex">
            <div class="col-md-10">
                <div class="form-head">
                    <span>Notification</span>
                </div>
            </div>
            <div class="col-md-2 book-draft-btn">
            </div>
        </div>
        <!-- <div class="row">
      <div class="col-md-10"></div>
      <div class="col-md-2">
        
      </div>
    </div> -->
        <br>
        <div class="row">
            <form action="{{route('send.notification')}}" method="post">
            {{ csrf_field() }}

                <div class="form-group row">
                    <label for="message" class="col-sm-2 col-form-label">Message</label>
                    <div class="col-sm-10">
                      <textarea name="notification" class="form-control" rows="6"></textarea>  
                    </div>
                </div>
                <button type="submit" class="btn btn-color btn-secondary">Send</button>

            </form>

        </div>
    </div>
</div>
<script>
    $(".save-team").on("click", function() {
        var foreman_id = $(this).data('foreman');
        var staff_id = [];
        $(this).parents(".accordion-body").find("input:checkbox[name=staff_id]:checked").each(function() {
            staff_id.push($(this).val());
        });


        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ url('/save-team') }}",
            method: "post",
            dataType: "json",
            data: {
                foreman_id: foreman_id,
                staff_id: staff_id,
                status: status
            },
            success: function(id) {}
        });
    });
</script>
@endsection