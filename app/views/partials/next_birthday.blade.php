@if(count($users)>0)
    <div class="ibox">
        <div class="ibox-title">
            <h5>Prochains anniversaires</h5>
        </div>
        <div class="ibox-content">
            <table class="table">
                <thead>
                <tr>
                    <th>Membre</th>
                    <th>Date</th>
                    <th>Age</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            @if (Auth::user()->role == 'superadmin')
                                <a href="{{ URL::route('user_modify', $user->id) }}">{{ $user->fullname }}</a>
                            @else
                                <a href="{{ URL::route('user_profile', $user->id) }}">{{ $user->fullname }}</a>
                            @endif
                        </td>
                        <td>
                            {{date('d/m', strtotime($user->birthday))}}
                        </td>
                        <td>
                            {{ date('Y') - date('Y', strtotime($user->birthday)) + 1 }} ans
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif