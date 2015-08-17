<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Etincelle Coworking - Accueil</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.css" rel="stylesheet">

    <link href="css/animate.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

</head>

<body class="gray-bg">

<div class="loginColumns animated fadeInDown">
    <div class="row">

        <div class="col-md-6">
            <h2 class="font-bold">Intranet Etincelle Coworking</h2>

            <p>Avant de pouvoir accéder aux ressources merveilleuses que nous vous avons préparés, il vous faut vous identifier avec le formulaire à droite.</p>
            <p>Un problème pour vous connecter? <a href="mailto:contact@coworking-toulouse.com">Contactez nous</a></p>


        </div>
        <div class="col-md-6">
            <div class="ibox-content">

                {{ Form::open(array('route' => 'user_login_check', 'class' => 'form account-form')) }}

                <div class="form-group">
                    <label for="email" class="placeholder-hidden">Email</label>
                    {{ Form::email('email', null, array('placeholder' => "Adresse email", 'class' => 'form-control')) }}
                </div> <!-- /.form-group -->

                <div class="form-group">
                    <label for="login-password" class="placeholder-hidden">Password</label>
                    {{ Form::password('password', array('class' => 'form-control')) }}
                </div> <!-- /.form-group -->

                <div class="form-group clearfix">
                    <div class="pull-left">
                        <label class="checkbox-inline">
                            {{ Form::checkbox('remember') }} <small>Mémoriser la connexion</small>
                        </label>
                    </div>

                    <div class="pull-right">
                        <small><a href="{{ action('RemindersController@getRemind') }}">Mot de passe oublié?</a></small>
                    </div>
                </div> <!-- /.form-group -->

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block btn-lg" tabindex="4">
                        Connexion &nbsp; <i class="fa fa-play-circle"></i>
                    </button>
                </div> <!-- /.form-group -->

                {{ Form::close() }}
            </div>
        </div>
    </div>
    <hr/>
    <div class="row">
        <div class="col-md-6">
            Copyright Sébastien Hordeaux
        </div>
        <div class="col-md-6 text-right">
            <small>&copy; 2014-{{date('Y')}}</small>
        </div>
    </div>
</div>

</body>

</html>
