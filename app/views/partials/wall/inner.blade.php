<?php


$messages = WallPost::where('level', 0)
        ->with('user')
        ->orderBy('created_at', 'DESC')->limit(5)->get();

?>



<div class="modal inmodal fade" id="wallNewMessage" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {{ Form::open(array('route' => array('wall_add_check'), 'id' => 'wall_add')) }}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span
                            aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Nouveau message</h4>
                {{--<small class="font-bold">Lorem Ipsum is simply dummy text of the printing and typesetting--}}
                {{--industry.--}}
                {{--</small>--}}
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Message</label>
                            <small class="text-muted">Syntaxe <a href="https://daringfireball.net/projects/markdown/" target="_blank">Markdown</a></small>
                            {{ Form::textarea('message', '', array('class'=> 'form-control')) }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Apercu</label>
                            <div class="form-control" id="wall-preview" style="height: 214px; overflow: auto;">

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Annuler</button>
                {{ Form::submit('Enregistrer', array('class' => 'btn btn-primary', 'id' => 'wall_submit')) }}
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
<p>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#wallNewMessage">Ajouter
        un message
    </button>
</p>

@foreach($messages as $message)
    <div class="social-feed-separated">

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
                {{--<div class="clear"></div>--}}
                {{--<div class="btn-group">--}}
                {{--<button class="btn btn-white btn-xs"><i class="fa fa-thumbs-up"></i> Like this!</button>--}}
                {{--<button class="btn btn-white btn-xs"><i class="fa fa-comments"></i> Comment</button>--}}
                {{--<button class="btn btn-white btn-xs"><i class="fa fa-share"></i> Share</button>--}}
                {{--</div>--}}


            </div>
            <div class="social-footer">
                {{--*/ $children = $message->children()->with('user')->get() /*--}}
                @foreach($children as $child)
                    {{$child->render('div', function ($node) use ($isSuperAdmin){
                        $snippet = '<div class="tree tree-level-1"><div class="social-comment row">
                        <div class="col-lg-12">
                            <a href="#" class="pull-left">'.$node->user->avatarTag.'</a>
                            <div class="media-body">';
                            if($isSuperAdmin){
                            $snippet .= '<a href="/wall/delete-reply/'.$node->id.'" class="btn btn-xs btn-danger btn-outline pull-right ajaxDeleteReply">Supprimer</a>';
                            }
                            $snippet .= '<a href="/profile/'.$node->user->id.'">'.$node->user->fullname.'</a>
                                <small class="text-muted" data-from-now="'.$node->created_at->format('c').'"></small>
                            <div>'.$node->messageFmt.'</div>

                            <!--
                            <br/>
                            <a href="#" class="small"><i class="fa fa-thumbs-up"></i> 26 Like this!</a> -
                            -->
                        </div>
                        </div>
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


<script type="application/javascript">
    function wall_refreshTimeAgo() {
        $('small[data-from-now]').each(function () {
            $(this).html(moment($(this).attr('data-from-now')).fromNow());
        });
    }

    docReady(function () {
//        $('.summernote').summernote({focus: true});
//        $('#post_message_summernote').code($('input[name=message]').val());

//        $('#wall_add').submit(function () {
//            $('input[name=message]').val($('#post_message_summernote').code());
//        });

        $('#wall_add textarea').on('keyup', function(){
            $('#wall-preview').html(markdown.toHTML($(this).val()))
        });

        $('.wall_reply_form').submit(function (e) {
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
                success: function (data) {
                    var snippet = '<div class="tree tree-level-1"><div class="tree tree-level-1">'
                            + '<div class="social-comment"><a href="' + Etincelle.User.profileUrl + '" class="pull-left">' + Etincelle.User.avatarTag + '</a>'
                            + '<div class="media-body">'
                            @if ($isSuperAdmin)
                  + '<a href="/wall/delete-reply/' + data.id + '" class="btn btn-xs btn-danger btn-outline pull-right ajaxDeleteReply">Supprimer</a>'
                            @endif
              + '<a href="' + Etincelle.User.profileUrl + '">' + Etincelle.User.fullname + '</a> '
                            + '<small class="text-muted" data-from-now="' + data.created_at + '"></small>'
                            + '<div>' + data.content + '</div>'
                            + '</div>'
                            + '</div>'
                            + '</div>'
                            + '</div>';
                    $(snippet).insertBefore($formTextarea.closest('.social-comment'));
                    $formTextarea.val('');
                    wall_refreshTimeAgo();
                },
                error: function (data) {

                }
            });

        });

        $(document).on("click", ".ajaxDeleteReply", function (e) {
            e.preventDefault();
            var $reply = $(this).closest('.social-comment');
            $.ajax({
                url: $(this).attr('href'),
                type: "GET",
                success: function (data) {
                    $reply.hide('slow');
                },
                error: function (data) {

                }
            });


            return false;
        });

        setTimeout(wall_refreshTimeAgo, 30 * 1000); // refresh every 30 sec

        wall_refreshTimeAgo();
    });
</script>