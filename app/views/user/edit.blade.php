@extends('layouts.master')

@section('meta_title')
	Modification de {{ $user->fullname }}
@stop

@section('content')
	<h1>Modifier un utilisateur</h1>

	{{ Form::model($user, array('route' => array('user_edit', $user->id))) }}
        <ul id="tabUserAdd" class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#connexion" aria-controls="connexion" role="tab" data-toggle="tab">Informations de connexion</a>
            </li>
            <li role="presentation">
                <a href="#bio" aria-controls="bio" role="tab" data-toggle="tab">Biographie</a>
            </li>
            <li role="presentation">
                <a href="#socials" aria-controls="socials" role="tab" data-toggle="tab">Réseaux sociaux</a>
            </li>
            <li role="presentation">
                <a href="#competence" aria-controls="competence" role="tab" data-toggle="tab">Compétences</a>
            </li>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="connexion">
                {{ Form::label('email', 'Adresse email') }}
                <p>{{ Form::email('email', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('firstname', 'Prénom') }}
                <p>{{ Form::text('firstname', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('lastname', 'Nom de famille') }}
                <p>{{ Form::text('lastname', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('password', 'Mot de passe') }}
                <p>{{ Form::password('password', array('class' => 'form-control')) }}</p>
                {{ Form::label('role', 'Rôle (droits)') }}
                <p>{{ Form::select('role', User::SelectRoles(), null, array('class' => 'form-control')) }}</p>
                <div class="checkbox">
                    {{ Form::label('is_member', 'Il est membre') }}
                    {{ Form::checkbox('is_member', true) }}
                </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="bio">
                {{ Form::label('bio_short', 'Courte bio') }}
                <p>{{Form::textarea('bio_short', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('bio_long', 'Longue bio') }}
                <p>{{Form::textarea('bio_long', null, array('class' => 'form-control')) }}</p>
            </div>
            <div role="tabpanel" class="tab-pane" id="socials">
                {{ Form::label('twitter', 'Twitter') }}
                <p>{{ Form::text('twitter', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('website', 'Site internet') }}
                <p>{{ Form::text('website', null, array('class' => 'form-control')) }}</p>
                {{ Form::label('phone', 'Téléphone') }}
                <p>{{ Form::text('phone', null, array('class' => 'form-control')) }}</p>
            </div>
            <div role="tabpanel" class="tab-pane" id="competence">
                <div class="row" id="skills">
                @if(count($skills) != 0)
                @foreach($skills as $skill)
                    <input type="hidden" name="modif[{{ $skill->id }}]" value="{{ $skill->id }}"/>
                    <div class="col-md-6" id="skill">
                        <div class="row">
                            <div class="col-md-8">
                                {{ Form::label('nameExist['.$skill->id.']', 'Compétence') }}
                                <p>{{ Form::text('nameExist['.$skill->id.']', $skill->name, array('class' => 'form-control')) }}</p>
                            </div>
                            <div class="col-md-2">
                                {{ Form::label('valueExist['.$skill->id.']', 'Valeur') }}
                                <p>{{ Form::number('valueExist['.$skill->id.']', $skill->value, array('class' => 'form-control', 'min' => 0, 'max' => 100)) }}</p>
                            </div>
                            <div class="col-md-2">
                                <label>Supprimer</label>
                                <button class="btn btn-danger btn-s col-lg-2" value="{{ $skill->id }}" id="delete[{{$skill->id}}]"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                @endforeach
                @else
                    <div class="col-md-6" id="skill">
                        <div class="row">
                            <div class="col-md-9">
                                <label>Compétence</label>
                                <p><input class="form-control" type="text" value="" name="name[0]" /></p>
                            </div>
                            <div class="col-md-3">
                                <label>Valeur</label>
                                <p><input class="form-control" type="number" min="0" max="100" value="" name="value[0]" /></p>
                            </div>
                        </div>
                    </div>
                @endif
                </div>
                <div align="left">
                    <button class="btn btn-info btn-xs col-lg-1" id="add_skill">+</button>
                </div>
                <br />
            </div>
        </div>

        <div align="center">
            {{ Form::submit('Modifier cet utilisateur', array('class' => 'btn btn-success')) }}
        </div>
	{{ Form::close() }}
@stop

@section('javascript')
<script type="text/javascript">
var cpt=0;

$('#add_skill').click(function(e){
    e.preventDefault();
    cpt = cpt+1;
    //row = '<div class="col-md-6"><div class="row"><div class="col-md-9" id="count'+cpt+'">{{ Form::label('nom', 'Compétence') }}<p>{{ Form::text('name[]', null, array('class' => 'form-control')) }}</p></div><div class="col-md-3">{{ Form::label('valeur', 'Valeur') }}<p>{{ Form::number('value[]', null, array('class' => 'form-control', 'min' => 0, 'max' => 100)) }}</p></div></div></div>';
    row = '<div class="col-md-6"><div class="row"><div class="col-md-9" id="count'+cpt+'"><label>Compétence</label><p><input class="form-control" type="text" value="" name="name['+cpt+']" /></p></div><div class="col-md-3"><label>Compétence</label><p><input class="form-control" type="number" min="0" max="100" value="" name="value['+cpt+']" /></p></div></div></div>';
    $('#skills').append(row);
});
</script>
@endsection