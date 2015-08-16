<div class="ibox">
    <div class="ibox-title">
        <h5>Prochains anniversaires</h5>
    </div>
    <div class="ibox-content">
        <div class="feed-activity-list">
            @foreach($users as $user)
                <div class="feed-element">
                    <a href="{{ URL::route('user_profile', $user->id) }}" class="pull-left">
                        {{$user->avatarTag}}
                    </a>

                    <div class="media-body ">
                        <small class="pull-right text-navy">{{date('d/m', strtotime($user->birthday))}}</small>
                        <strong>
                            <a href="{{ URL::route('user_profile', $user->id) }}">{{ $user->fullname }}</a>
                        </strong><br/>
                        <small class="text-muted">aura alors {{ date('Y') - date('Y', strtotime($user->birthday)) + 1 }}
                            ans
                        </small>
                        {{--<div class="actions">--}}
                        {{--<a class="btn btn-xs btn-white"><i class="fa fa-thumbs-up"></i> Like </a>--}}
                        {{--<a class="btn btn-xs btn-danger"><i class="fa fa-heart"></i> Love</a>--}}
                        {{--</div>--}}
                    </div>
                </div>
            @endforeach
        </div>


    </div>
</div>