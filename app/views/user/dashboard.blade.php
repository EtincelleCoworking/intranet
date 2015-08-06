@extends('layouts.master')

@section('content')
    @if (Auth::user()->role == 'superadmin')
        <div class="row">
            <div class="col-lg-3">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>CA du mois</h5>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins">{{ number_format($totalMonth ? $totalMonth->total : 0, 0, ',', '.') }}
                            €</h1>
                        <small>&nbsp;</small>
                    </div>
                </div>
            </div>

            @if ($chargesMonth && $chargesMonth->total)
                <div class="col-lg-3">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Dépenses du mois</h5>
                        </div>
                        <div class="ibox-content">
                            <h1 class="no-margins">{{ number_format($chargesMonth ? $chargesMonth->total  : 0, 0, ',', '.') }}
                                €</h1>
                            @if ($chargesMonthToPay && $chargesMonthToPay->total)
                                <div class="stat-percent font-bold text-navy">{{ number_format($chargesMonthToPay ? $chargesMonthToPay->total  : 0, 0, ',', '.') }}
                                    €
                                </div>
                                <small>Reste dû</small>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-lg-3">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Encours Clients</h5>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins">{{ number_format($pending['total'], 0, ',', '.') }}€</h1>
                        <small>&nbsp;</small>

                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                @include('partials.active_subscription', array('active_subscription' => $active_subscription, 'subscription_used' => $subscription_used, 'subscription_ratio' => $subscription_ratio))
            </div>

        </div>
    @elseif (Auth::user()->role == 'member')
        <div class="row">
            {{--<div class="col-lg-3">--}}
            {{--<div class="ibox">--}}
            {{--<div class="ibox-content">--}}
            {{--<h5 class="m-b-md">CA du mois</h5>--}}

            {{--<h1 class="no-margins">--}}
            {{--{{ number_format($totalMonth ? $totalMonth->total : 0, 0, ',', '.') }} €--}}
            {{--</h1>--}}
            {{--<small>&nbsp;</small>--}}
            {{--</div>--}}
            {{--</div>--}}
            {{--</div>--}}

            <div class="col-lg-3">
                @include('partials.active_subscription', array('active_subscription' => $active_subscription, 'subscription_used' => $subscription_used, 'subscription_ratio' => $subscription_ratio))
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">

            @foreach($messages as $message)
                <div class="social-feed-separated">

                    <div class="social-avatar">
                        {{$message->user->avatarTag}}
                    </div>

                    <div class="social-feed-box">

                        {{--<div class="pull-right social-action dropdown">--}}
                        {{--<button data-toggle="dropdown" class="dropdown-toggle btn-white">--}}
                        {{--<i class="fa fa-angle-down"></i>--}}
                        {{--</button>--}}
                        {{--<ul class="dropdown-menu m-t-xs">--}}
                        {{--<li><a href="#">Config</a></li>--}}
                        {{--</ul>--}}
                        {{--</div>--}}
                        <div class="social-avatar">
                            <a href="">{{$message->user->fullname}}</a>
                            <small class="text-muted">{{$message->created}}</small>
                        </div>
                        <div class="social-body">
                            {{$message->message}}
                            {{--<div class="clear"></div>--}}
                            {{--<div class="btn-group">--}}
                            {{--<button class="btn btn-white btn-xs"><i class="fa fa-thumbs-up"></i> Like this!</button>--}}
                            {{--<button class="btn btn-white btn-xs"><i class="fa fa-comments"></i> Comment</button>--}}
                            {{--<button class="btn btn-white btn-xs"><i class="fa fa-share"></i> Share</button>--}}
                            {{--</div>--}}


                        </div>
                            <div class="social-footer">
                        {{--*/ $children = $message->children()->get() /*--}}
                        @foreach($children as $child)
                                {{$child->render('div', function ($node) {
                                    return '<div class="social-comment row">
                                    <div class="col-lg-12">
                                        <a href="#" class="pull-left">'.$node->user->avatarTag.'</a>
                                        <div class="media-body">
                                            <a href="#">'.$node->user->fullname.'</a>
                                            <small class="text-muted">'.$node->created.'</small>
                                        <div>'.$node->message.'</div>

                                        <!--
                                        <br/>
                                        <a href="#" class="small"><i class="fa fa-thumbs-up"></i> 26 Like this!</a> -
                                        -->
                                    </div>
                                    </div>
                                </div>';
                                },
                                TRUE
                                )}}


                        @endforeach
                        <div class="social-comment">
                            <a href="#" class="pull-left">
                                {{Auth::user()->avatarTag}}
                            </a>
                            <div class="media-body">
                                {{ Form::open(array('route' => array('wall_add_check'), 'class' => 'wall_reply_form')) }}
                                {{ Form::hidden('parent_id', $message->id) }}
                                <div class="form-group">
                                        <textarea name="message" class="form-control wallReply"
                                                  data-parent="{{$message->id}}"
                                                  placeholder="Commentez ici"></textarea>

                                </div>
                                <div class="form-group">
                                {{ Form::submit('Commenter', array('class' => 'btn btn-success')) }}
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                            </div>





                    </div>


                </div>

            @endforeach
        </div>
    <div class="col-lg-4">
        <div class="ibox ">
            <div class="ibox-title">
                <h5>Nouveau message</h5>
            </div>
            <div class="ibox-content">
                {{ Form::open(array('route' => array('wall_add_check'), 'id' => 'wall_add')) }}

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-control summernote" id="post_message_summernote"></div>
                        {{ Form::hidden('message') }}
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    {{ Form::submit('Enregistrer', array('class' => 'btn btn-success', 'id' => 'wall_submit')) }}
                </div>
                {{ Form::close() }}
            </div>

        </div>
    </div>
    </div>


@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('.summernote').summernote({focus: true});
            $('#post_message_summernote').code($('input[name=message]').val());

            $('#wall_add').submit(function () {
                $('input[name=message]').val($('#post_message_summernote').code());
            });

            $('.wall_reply_form').submit(function(e) {
                e.preventDefault();

                var $formTextarea = $(this).find('textarea');
                $.ajax({
                    dataType: 'json',
                    url: '{{ URL::route('wall_reply') }}',
                    type: "POST",
                    data: {
                        parent_id: $formTextarea.attr('data-parent'),
                        message: $formTextarea.val()
                    },
                    success: function(data) {
                        var snippet = '<div class="tree tree-level-1">'
                                + '<div class="social-comment"><a href="" class="pull-left">{{Auth::user()->avatarTag}}</a>'
                                + '<div class="media-body"><a href="#">{{Auth::user()->fullname}}</a> '
                                + '<small class="text-muted">' + data.created+ '</small>'
                                + '<div>' + $formTextarea.val() + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</div>';
                        $(snippet).insertBefore($formTextarea.closest('.social-comment'));
                        $formTextarea.val('');
                    },
                    error: function(data) {

                    }
                });

            });

            $('.wallReply').keydown(function(event) {
                if (event.keyCode == 13) {
                   $(this.form).submit();
                    return false;
                }
            })

        });
    </script>
@stop


