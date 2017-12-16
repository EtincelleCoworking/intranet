<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @section('meta_title')
            Intranet {{ $_ENV['organisation_name'] }}
        @show
    </title>

    {{ HTML::style('css/bootstrap.min.css') }}
    {{ HTML::style('font-awesome/css/font-awesome.min.css') }}

    {{ HTML::style('css/animate.css') }}
    {{ HTML::style('css/style.css') }}

</head>

<body class="gray-bg">

<div class="border-left-right p-lg" style="border-color: #1ab394; border-width:30px; height: 100%;">
    <div class="jumbotron">
        <h1>{{$ressource->name}}</h1>
        <h2>Disponible</h2>
        <h3>Disponible toute la journée</h3>
    </div>
</div>

{{ HTML::script('js/jquery-2.1.1.js') }}
{{ HTML::script('js/bootstrap.min.js') }}

</body>

</html>




