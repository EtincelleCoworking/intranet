<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
    	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>
			@section('meta_title')
				Intranet Etincelle Coworking
			@show
		</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		{{ HTML::style('css/style.css') }}
	</head>
	<body>
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="{{ URL::route('dashboard') }}">Etincelle Coworking</a>
				</div>
				<div id="navbar" class="collapse navbar-collapse">
					@if (Auth::user())
					<ul class="nav navbar-nav">
						<li><a href="{{ URL::route('dashboard') }}">Tableau de bord</a></li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Utilisateurs <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li><a href="{{ URL::route('user_list') }}">Liste</a></li>
								<li><a href="{{ URL::route('user_add') }}">Ajouter un utilisateur</a></li>
							</ul>
						</li>
					</ul>
					@endif
				</div><!--/.nav-collapse -->
			</div>
		</nav>

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

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
	</body>
</html>