@extends('layouts.master')

@section('meta_title')
    Liste des utilisateurs
@stop

@section('content')
    <h1>Liste des utilisateurs</h1>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Nom complet</th>
                <th>Organisations</th>
                <th>Compétences</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($users as $user)
            <tr>
                <td>
                    @if ($user->avatar)
                        {{ HTML::image('uploads/avatars/'.$user->avatar, '', array('class' => 'navbar-profile-avatar')) }}
                    @else
                        {{ HTML::image('img/avatars/avatar.png', '', array('class' => 'navbar-profile-avatar')) }}
                    @endif
                    <a href="{{ URL::route('user_profile', $user->id) }}">{{ $user->fullname }}</a>
                </td>
                <td>
                    @foreach ($user->organisations as $k => $organisation)
                        @if ($k > 0)
                            ,
                        @endif
                        {{ $organisation->name }}
                    @endforeach
                </td>
                <td>
                  @foreach ($user->all_skills['major'] as $key=>$skill)
                    @if ($key != 0)
                      ,
                    @endif
                    {{ $skill['name'] }} ({{ $skill['value'] }}%)
                  @endforeach
                  @if ($user->all_skills['major'])
                    ,
                  @endif
                  {{ $user->all_skills['minor'] }}
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">{{ $users->links() }}</td>
            </tr>
        </tfoot>
    </table>
@stop
