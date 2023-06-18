@extends('layouts.app')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.js"></script>

<div id="content">
    <div class="container booking-form-w">
        <div class="row d-flex">
            <div class="col-md-10">
                <div class="form-head">
                    <span>Assign Team</span>
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
            <div class="accordion" id="accordionExample">
                @foreach($foreman as $f)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading{{$f->id}}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{$f->id}}" aria-expanded="true" aria-controls="collapse{{$f->id}}">
                            {{ucfirst($f->name)}}
                        </button>
                    </h2>
                    <div id="collapse{{$f->id}}" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            @foreach($staff as $s)
                            <div class="form-check">
                                <input class="staff-checkbox form-check-input" {{ $f->staff->pluck('id')->contains($s->id)?'checked':'' }} data-foreman="{{$f->id}}" type="checkbox" id="inlineCheckbox{{$s->name}}" value="{{$s->id}}">
                                <label class="form-check-label" for="inlineCheckbox{{$s->name}}">{{ucfirst($s->name)}}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<script>
    $(".staff-checkbox").on("change", function() {
        var foreman_id = $(this).data('foreman');
        var staff_id = $(this).val();
        var status = $(this).prop('checked');
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