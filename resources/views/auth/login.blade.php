@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row no-flex">
        <div class="col-md-4 wd-100"></div>
        <div class="col-md-4 mtb-100 wd-100">
            <div class="card p-50">
                <div class="text-center mb-40">
                    <img src="img/logo2581-1.png">
                </div>
                <form method="POST" action="{{ route('login') }}" class="inp-brdr">
                @csrf

                    <div class="form-group">
                        <img src="img/frame-11@2x.svg"><input type="email" placeholder="Email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <img class="pos-new" src="img/frame-13@2x.svg"><input type="password" id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password"><img class="pos-new-hid toggle_password" src="img/frame-12@2x.svg">
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="checkbox db-flex fnt-12">
                        <label class="m-none"><input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}> Remember me</label>
                        <a hre="{{ route('password.request') }}"><span class="color-b">Forgot Password?</span></a>
                    </div>
                    <button type="submit" class="btn btn-default btn-styles">LOGIN</button>
                </form>

            </div>
        </div>
        <div class="col-md-4 wd-100"></div>
    </div>
</div>
<script>
    $(".toggle_password").click(function(){
          if($("input[name='password']").attr("type")=='password')
          {
            $("input[name='password']").attr("type","text");
          }else
          {
            $("input[name='password']").attr("type","password");
          }
    });
</script>
@endsection