<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="fr" class="no-js"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>
		@section('meta_title')
			Intranet Etincelle Coworking
		@show
	</title>

	<!-- Google Font: Open Sans -->
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400,400italic,600,600italic,800,800italic">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Oswald:400,300,700">

    <!-- Font Awesome CSS -->
    {{ HTML::style('css/font-awesome.min.css') }}

    <!-- Bootstrap CSS -->
    {{ HTML::style('css/bootstrap.min.css') }}

    <!-- App CSS -->
    {{ HTML::style('css/mvpready-admin.css') }}
    {{ HTML::style('css/mvpready-flat.css') }}
	{{ HTML::style('css/style.css') }}
</head>
<body>
    <div id="wrapper">
        <header class="navbar navbar-inverse" role="banner">

            <div class="container">

              <div class="navbar-header">
                <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <i class="fa fa-cog"></i>
                </button>

                <a href="{{ URL::route('dashboard') }}" class="navbar-brand navbar-brand-img">
                    {{ HTML::image('img/logo.jpg', $_ENV['organisation_name']) }}
                </a>
              </div> <!-- /.navbar-header -->


                <nav class="collapse navbar-collapse" role="navigation">
                    <ul class="nav navbar-nav noticebar navbar-left">

                    </ul>

                    @if (Auth::user())
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown navbar-profile">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:;">
                                {{ HTML::image('img/avatars/avatar-1-xs.jpg', Auth::user()->fullname, array('class' => 'navbar-profile-avatar')) }}
                                <span class="navbar-profile-label">{{ Auth::user()->fullname }} &nbsp;</span>
                                <i class="fa fa-caret-down"></i>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <a href="{{ URL::route('user_profile', Auth::user()->id) }}">
                                        <i class="fa fa-user"></i>
                                        &nbsp;&nbsp;Mon profil
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="{{ URL::route('user_logout') }}">
                                        <i class="fa fa-sign-out"></i>
                                        &nbsp;&nbsp;Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    @endif
                </nav>
            </div> <!-- /.container -->
        </header>

        @if (Auth::user())
        <div class="mainnav">
            <div class="container">
                <a class="mainnav-toggle" data-toggle="collapse" data-target=".mainnav-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <i class="fa fa-bars"></i>
                </a>

                <nav class="collapse mainnav-collapse" role="navigation">
                    <form class="mainnav-form pull-right" role="search">
                        <input type="text" class="form-control input-md mainnav-search-query" placeholder="Search">
                        <button class="btn btn-sm mainnav-form-btn"><i class="fa fa-search"></i></button>
                    </form>

                    <ul class="mainnav-menu">
                        <li class="{{ ((Request::is('/')) ? 'active' : '') }}">
                            <a href="{{ URL::route('dashboard') }}">
                                Dashboard
                            </a>
                        </li>

                        <li class="dropdown {{ ((Request::is('user*') || Request::is('organisation*')) ? 'active' : '') }}">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown">
                                <i class="fa fa-users"></i>
                                Utilisateurs
                                <i class="mainnav-caret"></i>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <a href="{{ URL::route('user_list') }}">
                                        <i class="fa fa-list"></i>
                                        &nbsp;&nbsp;Liste
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ URL::route('user_add') }}">
                                        <i class="fa fa-plus"></i>
                                        &nbsp;&nbsp;Ajouter un utilisateur
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="{{ URL::route('organisation_list') }}">
                                        <i class="fa fa-list"></i>
                                        &nbsp;&nbsp;Liste
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ URL::route('organisation_add') }}">
                                        <i class="fa fa-plus"></i>
                                        &nbsp;&nbsp;Ajouter une organisation
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="dropdown {{ ((Request::is('invoice*')) ? 'active' : '') }}">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown">
                                <i class="fa fa-money"></i>
                                Factures
                                <i class="mainnav-caret"></i>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <a href="{{ URL::route('invoice_list') }}">
                                        <i class="fa fa-list"></i>
                                        &nbsp;&nbsp;Liste
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ URL::route('invoice_add') }}">
                                        <i class="fa fa-plus"></i>
                                        &nbsp;&nbsp;Ajouter une facture
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="dropdown {{ ((Request::is('ressource*') || Request::is('country*')) ? 'active' : '') }}">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown">
                                <i class="fa fa-cogs"></i>
                                Configuration
                                <i class="mainnav-caret"></i>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                <li class="dropdown-submenu">
                                    <a tabindex="-1" href="{{ URL::route('ressource_list') }}">
                                        <i class="fa fa-barcode"></i>
                                        &nbsp;&nbsp;Ressources
                                    </a>

                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="{{ URL::route('ressource_list') }}">
                                                <i class="fa fa-list"></i>
                                                &nbsp;&nbsp;Liste
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ URL::route('ressource_add') }}">
                                                <i class="fa fa-plus"></i>
                                                &nbsp;&nbsp;Ajouter une ressource
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                               <li class="dropdown-submenu">
                                    <a tabindex="-1" href="{{ URL::route('country_list') }}">
                                        <i class="fa fa-flag"></i>
                                        &nbsp;&nbsp;Pays
                                    </a>

                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="{{ URL::route('country_list') }}">
                                                <i class="fa fa-list"></i>
                                                &nbsp;&nbsp;Liste
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ URL::route('country_add') }}">
                                                <i class="fa fa-plus"></i>
                                                &nbsp;&nbsp;Ajouter un pays
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        @endif

        <div class="content">
    		<div class="container">
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

    			@yield('content')
    		</div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p class="pull-left">Etincelle Coworking</p>
        </div>
    </footer>

    {{ HTML::script('js/libs/jquery-1.10.2.min.js') }}
    {{ HTML::script('js/libs/bootstrap.min.js') }}

    <!--[if lt IE 9]>
        {{ HTML::script('js/libs/excanvas.compiled.js') }}
    <![endif]-->

    {{ HTML::script('js/plugins/flot/jquery.flot.js') }}
    {{ HTML::script('js/plugins/flot/jquery.flot.tooltip.min.js') }}
    {{ HTML::script('js/plugins/flot/jquery.flot.pie.js') }}
    {{ HTML::script('js/plugins/flot/jquery.flot.resize.js') }}

    {{ HTML::script('js/mvpready-core.js') }}
    {{ HTML::script('js/mvpready-admin.js') }}

	{{ HTML::script('js/rails.js') }}
	@yield('javascript')
</body>
</html>