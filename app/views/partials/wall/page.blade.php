<?php

$messages = WallPost::where('level', 0)
        ->with('user')
        ->orderBy('created_at', 'DESC')->take(WallPost::ITEM_PER_PAGE);
if (!empty($page_index)) {
    $messages->skip(WallPost::ITEM_PER_PAGE * $page_index);
}
$messages = $messages->get();

?>
@if(count($messages) > 0)
    @foreach($messages as $message)
        <div class="social-feed-separated infinite-item">

            <div class="social-avatar">
                {{$message->user->avatarTag}}
            </div>

            <div class="social-feed-box">
                @if ($isSuperAdmin)
                    <div class="pull-right social-action dropdown">
                        <button data-toggle="dropdown" class="dropdown-toggle btn-white">
                            <i class="fa fa-angle-down"></i>
                        </button>
                        <ul class="dropdown-menu m-t-xs">
                            <li><a href="{{ URL::route('wall_delete', $message->id) }}">Supprimer</a></li>
                        </ul>
                    </div>
                @endif
                <div class="social-avatar">
                    <a href="{{URL::route('user_profile', $message->user->id)}}">{{$message->user->fullname}}</a>
                    <small class="text-muted" data-from-now="{{$message->created_at->format('c')}}"></small>
                </div>
                <div class="social-body">
                    {{$message->messageFmt}}

                </div>
                <div class="social-footer">
                    {{--*/ $children = $message->children()->with('user')->get() /*--}}
                    @foreach($children as $child)
                        {{$child->render('div', function ($node) use ($isSuperAdmin){
                            $snippet = '<div class="social-comment ">
                                <a href="#" class="pull-left">'.$node->user->avatarTag.'</a>
                                <div class="media-body">';
                                if($isSuperAdmin){
                                $snippet .= '<a href="/wall/delete-reply/'.$node->id.'" class="btn btn-xs btn-danger btn-outline pull-right ajaxDeleteReply">Supprimer</a>';
                                }
                                $snippet .= '<a href="/profile/'.$node->user->id.'">'.$node->user->fullname.'</a>
                                    <small class="text-muted" data-from-now="'.$node->created_at->format('c').'"></small>
                                '.$node->messageFmt.'

                        </div></div>';
                        return $snippet;


                        },
                        TRUE
                        )}}


                    @endforeach
                    <div class="social-comment">
                        <script type="text/javascript">
                            document.write('<a href="' + Etincelle.User.profileUrl + '" class="pull-left">' + Etincelle.User.avatarTag + '</a>');
                        </script>


                        <div class="media-body">
                            {{ Form::open(array('route' => array('wall_add_check'), 'class' => 'wall_reply_form')) }}
                            {{ Form::hidden('parent_id', $message->id) }}
                            <div class="form-group">
                                        <textarea name="message" class="form-control wallReply"
                                                  data-parent="{{$message->id}}"
                                                  placeholder="Commentez ici"></textarea>

                            </div>
                            <div class="form-group">
                                {{ Form::submit('Commenter', array('class' => 'btn btn-primary')) }}
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>


            </div>


        </div>

    @endforeach

    <a href="{{route('wall_page', array('page_index' => $page_index+1))}}" class="infinite-more-link hide">Voir la
        suite</a>
@endif