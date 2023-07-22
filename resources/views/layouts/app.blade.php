<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Boxit</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/responsive.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('js/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.33/dist/sweetalert2.all.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="{{ asset('js/calender/css/mobiscroll.jquery.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('js/calender/js/mobiscroll.jquery.min.js') }}"></script>
</head>

<body>
    <div id="app">
        <div id="overlay">
            <div class="cv-spinner">
                <span class="spinner"></span>
            </div>
        </div>
        @auth
        <div class="container-fluid">
            <div class="row">


                <div class="topnav mob-nav">
                    <!-- <a href="#home" class="active"><img src="/img/logo2581-1.png"></a> -->
                    <div id="myLinks">
                        <a href="{{url('/')}}" class="nav_link"><img src="/img/calendar.png">Calendar</a>
                        @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Project Manager'))
                        <a href="{{url('/staff-management')}}" class="nav_link"><img src="/img/users.png">Calendar</a>
                        @endif
                    </div>
                    <a href="javascript:void(0);" class="icon" onclick="myFunction()">
                        <i class="fa fa-bars"></i>
                    </a>
                </div>



                <div class="col-md-2 blue-bg pos-rel p-none z-in1 desktop-nav">
                    <div id="sidebar">
                        <div class="logo-sec">
                            <img src="/img/logo2581-1.png">
                        </div>
                        <ul class="li-flex li-styles p-none list-none">
                            <li class="{{ request()->routeIs('calender') ? 'active' : '' }}"><a href="{{url('/')}}" class="nav_link"><img src="/img/calendar.png">Calendar</a></li>
                            @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Project Manager'))
                            <li class="{{ request()->routeIs('staff.list') || request()->routeIs('team.assign') ? 'active' : '' }}"><a href="{{url('/staff-management')}}" class="nav_link"><img src="/img/users.png">Staff Management</a></li>
                            <li class="{{ request()->routeIs('notification') ? 'active' : ''  }}"><a href="{{url('/send-notification')}}" class="nav_link"><img src="/img/smartphone-line.svg">Mobile App Broadcast</a></li>
                            <li class="{{ request()->routeIs('team.notification') ? 'active' : '' }}"><a href="{{url('/team-notification')}}" class="nav_link"><img src="/img/notification-line.svg">Team Notification</a></li>
                            @endif
                        </ul>

                    </div>
                </div>
                <div class="col-md-10 mt-40 mb-100 p-none">
                    <div class="container pl-none pr-60">
                        <div id="header" class="mb-40 prl-30 marg-bot-n">
                            <div class="row d-flex flex-n">
                                <div class="col-md-8">
                                </div>
                                <div class="col-md-1 bell-icon-w pr-none text-right">
                                </div>
                                <div class="col-md-1 name-icon-w text-right">
                                    <div>
                                        <span class="profile_letter">{{mb_substr(strtoupper(Auth::user()->name), 0, 1)}}</span>
                                    </div>
                                </div>
                                <div class="col-md-2 name-email-w p-none">
                                    <div>
                                        <p class="admin-s">{{ucfirst(Auth::user()->name)}}<br>
                                            <span>{{Auth::user()->email}}</span>
                                        </p>
                                    </div>
                                    <div>
                                        <img src="/img/arrow.png" data-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                        <div class="dropdown-menu">
                                            <!-- <a href="javascript:void(0)"  class="dropdown-item">Edit</a> -->
                                            <a href="javascript:void(0)" class=" dropdown-item" onclick="event.preventDefault();document.getElementById('frm-logout').submit();">Logout</a>
                                            <form id="frm-logout" action="{{ route('logout') }}" method="POST" style="display: none;">
                                                {{ csrf_field() }}
                                            </form>
                                        </div>
                                    </div>
                                    <!-- <div class="col-md-1 down-icon-w">
                                    
                                    </div> -->
                                </div>
                            </div>
                        </div>
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
        @endauth
        @guest
        <main class="py-4">
            @yield('content')
        </main>
        @endguest
    </div>
</body>
<script type="text/javascript">
    function myFunction() {
        var x = document.getElementById("myLinks");
        if (x.style.display === "block") {
            x.style.display = "none";
        } else {
            x.style.display = "block";
        }
    }
    const Toast = Swal.mixin({
        toast: true,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    })
</script>

</html>