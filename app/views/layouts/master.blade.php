<!DOCTYPE html>
<!--[if lt IE 7]>
<html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>
<html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>
<html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html lang="fr" class="no-js"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @section('meta_title')
            Intranet {{ $_ENV['organisation_name'] }}
        @show
    </title>

    <!-- Google Font: Open Sans -->
    {{--<link rel="stylesheet"--}}
    {{--href="http://fonts.googleapis.com/css?family=Open+Sans:400,400italic,600,600italic,800,800italic">--}}

    {{ HTML::style('fonts/open_sans.css') }}
    {{ HTML::style('fonts/oswald.css') }}

    {{--<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Oswald:400,300,700">--}}
            <!-- Font Awesome CSS -->
    {{ HTML::style('font-awesome/css/font-awesome.min.css') }}

            <!-- Bootstrap CSS -->
    {{ HTML::style('css/plugins/summernote/summernote.css') }}
    {{ HTML::style('css/plugins/summernote/summernote-bs3.css') }}
    {{ HTML::style('css/bootstrap.min.css') }}

    {{--<!-- Jquery UI CSS -->--}}
    {{--{{ HTML::style('css/jquery-ui.min.css') }}--}}
    {{--{{ HTML::style('css/jquery-ui.structure.css') }}--}}
    {{--{{ HTML::style('css/jquery-ui.theme.css') }}--}}

            <!-- Select 2 JS -->
    {{ HTML::style('css/select2.min.css') }}

            <!-- Time Picker JS -->
    {{ HTML::style('css/jquery.timepicker.css') }}

            <!-- DateDropper JS -->
    {{--{{ HTML::style('css/datedropper.css') }}--}}

    {{ HTML::style('css/plugins/datapicker/datepicker3.css') }}

    {{--<!-- App CSS -->--}}
    {{--{{ HTML::style('css/mvpready-admin.css') }}--}}
    {{--{{ HTML::style('css/mvpready-flat.css') }}--}}
    {{ HTML::style('css/animate.css') }}
    {{ HTML::style('css/style.css') }}

    {{ HTML::style('css/select2.min.css') }}

    {{ HTML::style('css/plugins/toastr/toastr.min.css') }}


    {{ HTML::style('css/etincelle.css') }}

    @yield('stylesheets')
    <script type="text/javascript">
                @if(Auth::check())
        var Etincelle = {
                    User: {
                        fullname: '{{str_replace("'", "\\'", Auth::user()->fullname)}}',
                        avatarTag: '{{Auth::user()->avatarTag}}',
                        profileUrl: '{{URL::route('user_profile', Auth::id())}}',
                        last_login: '{{ (new DateTime(Auth::user()->last_login_at))->format('c') }}'
                    }
                };
                @else
        var Etincelle = {
                    User: {
                        fullname: 'Anonyme',
                        avatarTag: '',
                        profileUrl: '#',
                        last_login: 0
                    }
                };
        @endif


        (function(funcName, baseObj) {
            // The public function name defaults to window.docReady
            // but you can pass in your own object and own function name and those will be used
            // if you want to put them in a different namespace
            funcName = funcName || "docReady";
            baseObj = baseObj || window;
            var readyList = [];
            var readyFired = false;
            var readyEventHandlersInstalled = false;

            // call this when the document is ready
            // this function protects itself against being called more than once
            function ready() {
                if (!readyFired) {
                    // this must be set to true before we start calling callbacks
                    readyFired = true;
                    for (var i = 0; i < readyList.length; i++) {
                        // if a callback here happens to add new ready handlers,
                        // the docReady() function will see that it already fired
                        // and will schedule the callback to run right after
                        // this event loop finishes so all handlers will still execute
                        // in order and no new ones will be added to the readyList
                        // while we are processing the list
                        readyList[i].fn.call(window, readyList[i].ctx);
                    }
                    // allow any closures held by these functions to free
                    readyList = [];
                }
            }

            function readyStateChange() {
                if ( document.readyState === "complete" ) {
                    ready();
                }
            }

            // This is the one public interface
            // docReady(fn, context);
            // the context argument is optional - if present, it will be passed
            // as an argument to the callback
            baseObj[funcName] = function(callback, context) {
                // if ready has already fired, then just schedule the callback
                // to fire asynchronously, but right away
                if (readyFired) {
                    setTimeout(function() {callback(context);}, 1);
                    return;
                } else {
                    // add the function and context to the list
                    readyList.push({fn: callback, ctx: context});
                }
                // if document already ready to go, schedule the ready function to run
                if (document.readyState === "complete") {
                    setTimeout(ready, 1);
                } else if (!readyEventHandlersInstalled) {
                    // otherwise if we don't have event handlers installed, install them
                    if (document.addEventListener) {
                        // first choice is DOMContentLoaded event
                        document.addEventListener("DOMContentLoaded", ready, false);
                        // backup is window load event
                        window.addEventListener("load", ready, false);
                    } else {
                        // must be IE
                        document.attachEvent("onreadystatechange", readyStateChange);
                        window.attachEvent("onload", ready);
                    }
                    readyEventHandlersInstalled = true;
                }
            }
        })("docReady", window);

    </script>
</head>
<body>

<div id="wrapper">

    <nav class="navbar-default navbar-static-side" role="navigation">
        <div class="sidebar-collapse">
            @if (Auth::user())
                @if(Auth::user()->role)
                    @include('layouts.menu.'.Auth::user()->role)
                @else
                    @include('layouts.menu.member')
                @endif
            @endif

        </div>
    </nav>

    <div id="page-wrapper" class="gray-bg">

        {{-- HEADER
                    <div class="row border-bottom">
                        <nav class="navbar navbar-static-top  " role="navigation" style="margin-bottom: 0">
                            <div class="navbar-header">
                                <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>

                                <form role="search" class="navbar-form-custom" action="search_results.html">
                                    <div class="form-group">
                                        <input type="text" placeholder="Search for something..." class="form-control" name="top-search" id="top-search">
                                    </div>
                                </form>

                            </div>
                            <ul class="nav navbar-top-links navbar-right">

                                <li>
                                    <span class="m-r-sm text-muted welcome-message">Welcome to INSPINIA+ Admin Theme.</span>
                                </li>
                                <li class="dropdown">
                                    <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                                        <i class="fa fa-envelope"></i>  <span class="label label-warning">16</span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-messages">
                                        <li>
                                            <div class="dropdown-messages-box">
                                                <a href="profile.html" class="pull-left">
                                                    <img alt="image" class="img-circle" src="img/a7.jpg">
                                                </a>
                                                <div class="media-body">
                                                    <small class="pull-right">46h ago</small>
                                                    <strong>Mike Loreipsum</strong> started following <strong>Monica Smith</strong>. <br>
                                                    <small class="text-muted">3 days ago at 7:58 pm - 10.06.2014</small>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="divider"></li>
                                        <li>
                                            <div class="dropdown-messages-box">
                                                <a href="profile.html" class="pull-left">
                                                    <img alt="image" class="img-circle" src="img/a4.jpg">
                                                </a>
                                                <div class="media-body ">
                                                    <small class="pull-right text-navy">5h ago</small>
                                                    <strong>Chris Johnatan Overtunk</strong> started following <strong>Monica Smith</strong>. <br>
                                                    <small class="text-muted">Yesterday 1:21 pm - 11.06.2014</small>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="divider"></li>
                                        <li>
                                            <div class="dropdown-messages-box">
                                                <a href="profile.html" class="pull-left">
                                                    <img alt="image" class="img-circle" src="img/profile.jpg">
                                                </a>
                                                <div class="media-body ">
                                                    <small class="pull-right">23h ago</small>
                                                    <strong>Monica Smith</strong> love <strong>Kim Smith</strong>. <br>
                                                    <small class="text-muted">2 days ago at 2:30 am - 11.06.2014</small>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="divider"></li>
                                        <li>
                                            <div class="text-center link-block">
                                                <a href="mailbox.html">
                                                    <i class="fa fa-envelope"></i> <strong>Read All Messages</strong>
                                                </a>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li class="dropdown">
                                    <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                                        <i class="fa fa-bell"></i>  <span class="label label-primary">8</span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-alerts">
                                        <li>
                                            <a href="mailbox.html">
                                                <div>
                                                    <i class="fa fa-envelope fa-fw"></i> You have 16 messages
                                                    <span class="pull-right text-muted small">4 minutes ago</span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="divider"></li>
                                        <li>
                                            <a href="profile.html">
                                                <div>
                                                    <i class="fa fa-twitter fa-fw"></i> 3 New Followers
                                                    <span class="pull-right text-muted small">12 minutes ago</span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="divider"></li>
                                        <li>
                                            <a href="grid_options.html">
                                                <div>
                                                    <i class="fa fa-upload fa-fw"></i> Server Rebooted
                                                    <span class="pull-right text-muted small">4 minutes ago</span>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="divider"></li>
                                        <li>
                                            <div class="text-center link-block">
                                                <a href="notifications.html">
                                                    <strong>See All Alerts</strong>
                                                    <i class="fa fa-angle-right"></i>
                                                </a>
                                            </div>
                                        </li>
                                    </ul>
                                </li>



                                <li>
                                    <a href="{{ URL::route('user_logout') }}">
                                        <i class="fa fa-sign-out"></i> Déconnexion
                                    </a>
                                </li>
                            </ul>

                        </nav>
                    </div>
        --}}
        @yield('breadcrumb')


        <div class="wrapper wrapper-content">
            @if (Session::has('mSuccess'))
                <div class="alert alert-success" role="alert">
                    {{ Session::get('mSuccess') }}
                </div>
            @endif

            @if (Session::has('mError'))
                <div class="alert alert-danger" role="alert">
                    {{ Session::get('mError') }}
                </div>
            @endif

            @if (Session::has('mWarning'))
                <div class="alert alert-warning" role="alert">
                    {{ Session::get('mWarning') }}
                </div>
            @endif

            @if (Session::has('mInfo'))
                <div class="alert alert-info" role="alert">
                    {{ Session::get('mInfo') }}
                </div>
            @endif

            @if ($errors->has())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </div>
        <div class="footer">
            Tous droits réservés - Sébastien Hordeaux &copy; 2014-{{date('Y')}}
        </div>

    </div>
</div>


{{--<div id="wrapper">--}}








{{--<header class="navbar navbar-inverse" role="banner">--}}

{{--<div class="container">--}}

{{--<div class="navbar-header">--}}
{{--<button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".navbar-collapse">--}}
{{--<span class="sr-only">Toggle navigation</span>--}}
{{--<i class="fa fa-cog"></i>--}}
{{--</button>--}}

{{--<a href="{{ URL::route('dashboard') }}" class="navbar-brand">--}}
{{--Etincelle Coworking--}}
{{--</a>--}}
{{--</div> <!-- /.navbar-header -->--}}


{{--<nav class="collapse navbar-collapse" role="navigation">--}}
{{--<ul class="nav navbar-nav noticebar navbar-left">--}}

{{--</ul>--}}

{{--@if (Auth::user())--}}
{{--<ul class="nav navbar-nav navbar-right">--}}
{{--<li class="dropdown navbar-profile">--}}
{{--<a class="dropdown-toggle" data-toggle="dropdown" href="javascript:;">--}}
{{--@if (Auth::user()->avatar)--}}
{{--{{ HTML::image('uploads/avatars/'.Auth::user()->avatar, '', array('class' => 'navbar-profile-avatar')) }}--}}
{{--@else--}}
{{--{{ HTML::image('img/avatars/avatar.png', '', array('class' => 'navbar-profile-avatar')) }}--}}
{{--@endif--}}
{{--<span class="navbar-profile-label">{{ Auth::user()->fullname }} &nbsp;</span>--}}
{{--<i class="fa fa-caret-down"></i>--}}
{{--</a>--}}
{{--<ul class="dropdown-menu" role="menu">--}}
{{--<li>--}}
{{--<a href="{{ URL::route('user_profile', Auth::user()->id) }}">--}}
{{--<i class="fa fa-user"></i>--}}
{{--&nbsp;&nbsp;Mon profil--}}
{{--</a>--}}
{{--</li>--}}
{{--<li class="divider"></li>--}}
{{--<li>--}}
{{--<a href="{{ URL::route('user_logout') }}">--}}
{{--<i class="fa fa-sign-out"></i>--}}
{{--&nbsp;&nbsp;Logout--}}
{{--</a>--}}
{{--</li>--}}
{{--</ul>--}}
{{--</li>--}}
{{--</ul>--}}
{{--@endif--}}
{{--</nav>--}}
{{--</div> <!-- /.container -->--}}
{{--</header>--}}

{{--@if (Auth::user())--}}
{{--@include('layouts.menu.'.Auth::user()->role)--}}
{{--@endif--}}

{{--<div class="content">--}}
{{--<div class="container">--}}
{{--@if (Session::has('mSuccess'))--}}
{{--<div class="alert alert-success" role="alert">--}}
{{--{{ Session::get('mSuccess') }}--}}
{{--</div>--}}
{{--@endif--}}

{{--@if (Session::has('mError'))--}}
{{--<div class="alert alert-danger" role="alert">--}}
{{--{{ Session::get('mError') }}--}}
{{--</div>--}}
{{--@endif--}}

{{--@if (Session::has('mWarning'))--}}
{{--<div class="alert alert-warning" role="alert">--}}
{{--{{ Session::get('mWarning') }}--}}
{{--</div>--}}
{{--@endif--}}

{{--@if (Session::has('mInfo'))--}}
{{--<div class="alert alert-info" role="alert">--}}
{{--{{ Session::get('mInfo') }}--}}
{{--</div>--}}
{{--@endif--}}

{{--@if ($errors->has())--}}
{{--<div class="alert alert-danger">--}}
{{--@foreach ($errors->all() as $error)--}}
{{--{{ $error }}<br>--}}
{{--@endforeach--}}
{{--</div>--}}
{{--@endif--}}

{{--@yield('content')--}}
{{--</div>--}}
{{--</div>--}}
{{--</div>--}}

{{--<!----}}
{{--<footer class="footer">--}}
{{--<div class="container">--}}
{{--<p class="pull-left">Etincelle Coworking</p>--}}
{{--</div>--}}
{{--</footer>--}}
{{---->--}}

{{--<!-- Mainly scripts -->--}}
{{--{{ HTML::script('js/jquery-2.1.1.js') }}--}}
{{--{{ HTML::script('js/bootstrap.min.js') }}--}}
{{--{{ HTML::script('js/plugins/metisMenu/jquery.metisMenu.js') }}--}}
{{--{{ HTML::script('js/plugins/slimscroll/jquery.slimscroll.min.js') }}--}}
{{--{{ HTML::script('js/inspinia.js') }}--}}
{{--{{ HTML::script('js/plugins/pace/pace.min.js') }}--}}




{{--{{ HTML::script('js/libs/jquery-1.10.2.min.js') }}--}}
{{--{{ HTML::script('js/jquery-ui.min.js') }}--}}
{{--{{ HTML::script('js/datepicker-fr.js') }}--}}
{{--{{ HTML::script('js/libs/bootstrap.min.js') }}--}}

{{--<!--[if lt IE 9]>--}}
{{--{{ HTML::script('js/libs/excanvas.compiled.js') }}--}}
{{--<![endif]-->--}}

{{--{{ HTML::script('js/plugins/flot/jquery.flot.js') }}--}}
{{--{{ HTML::script('js/plugins/flot/jquery.flot.tooltip.min.js') }}--}}
{{--{{ HTML::script('js/plugins/flot/jquery.flot.pie.js') }}--}}
{{--{{ HTML::script('js/plugins/flot/jquery.flot.resize.js') }}--}}

{{--{{ HTML::script('js/mvpready-core.js') }}--}}
{{--{{ HTML::script('js/mvpready-admin.js') }}--}}

{{--{{ HTML::script('js/select2/select2.min.js') }}--}}


{{--{{ HTML::script('js/datedropper.min.js') }}--}}

{{--{{ HTML::script('js/rails.js') }}--}}

{{--<script type="text/javascript">--}}
{{--$('[data-toggle="tooltip"]').tooltip();--}}
{{--$.datepicker.setDefaults( $.datepicker.regional[ "fr" ] );--}}
{{--</script>--}}

{{ HTML::script('js/jquery-2.1.1.js') }}
{{ HTML::script('js/select2.min.js') }}
{{ HTML::script('js/jquery.timepicker.min.js') }}
{{ HTML::script('js/bootstrap.min.js') }}
{{ HTML::script('js/plugins/datapicker/bootstrap-datepicker.js') }}
{{ HTML::script('js/plugins/metisMenu/jquery.metisMenu.js') }}
{{ HTML::script('js/plugins/slimscroll/jquery.slimscroll.min.js') }}
{{ HTML::script('js/inspinia.js') }}
{{ HTML::script('js/plugins/pace/pace.min.js') }}
{{ HTML::script('js/plugins/summernote/summernote.min.js') }}
{{ HTML::script('js/jquery.equalheights.js') }}
{{ HTML::script('js/plugins/fullcalendar/moment.min.js') }}
{{ HTML::script('js/plugins/fullcalendar/moment_fr.js') }}
{{ HTML::script('js/markdown.min.js') }}
{{ HTML::script('js/plugins/toastr/toastr.min.js') }}

<script type="text/javascript">
    $.fn.datepicker.dates['fr'] = {
        days: ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
        daysShort: ["dim.", "lun.", "mar.", "mer.", "jeu.", "ven.", "sam."],
        daysMin: ["d", "l", "ma", "me", "j", "v", "s"],
        months: ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
        monthsShort: ["janv.", "févr.", "mars", "avril", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc."],
        today: "Aujourd'hui",
        clear: "Effacer",
        weekStart: 1,
        format: "dd/mm/yyyy"
    };
    $.fn.datepicker.defaults.zIndexOffset = 3151;
    $.fn.datepicker.defaults.todayHighlight = true;
    $.fn.datepicker.defaults.language = 'fr';
    $.fn.datepicker.defaults.autoclose = true;

    moment.locale('fr');

</script>
@yield('javascript')
</body>
</html>