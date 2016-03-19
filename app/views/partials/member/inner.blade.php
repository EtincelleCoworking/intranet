<?php
$member = User::whereIsMember(true)
        ->where('default_location_id', '=', Auth::user()->default_location_id)
        ->orderByRaw("RAND()")->first();
?>
@if($member)
    <div class="ibox">
        <div class="ibox-title">
            <h5>Connaissez-vous ?</h5>
        </div>
        <div class="ibox-content text-center">
            <a href="{{route('user_profile', $member->id)}}">
                <h1 style="color: #676a6c;">{{$member->fullname}}</h1>

                <div class="m-b-sm">
                    <a href="{{route('user_profile', $member->id)}}"><img alt="image" class="img-circle"
                                                                          src="{{$member->getAvatarUrl(96)}}"></a>
                </div>
                <p class="font-bold">{{$member->bio_short}}</p>

                {{--<div class="text-center">--}}
                {{--<a class="btn btn-xs btn-white"><i class="fa fa-thumbs-up"></i> Like </a>--}}
                {{--<a class="btn btn-xs btn-primary"><i class="fa fa-heart"></i> Love</a>--}}
                {{--</div>--}}
            </a>
        </div>
    </div>
@endif
