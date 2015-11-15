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
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#wallNewMessage">
        Ajouter un message
    </button>
</p>

<div class="infinite-container">
    @include('partials.wall.page', array('page_index' => $page_index))
</div>


<script type="application/javascript">
    function wall_refreshTimeAgo() {
        $('small[data-from-now]').each(function () {
            $(this).html(moment($(this).attr('data-from-now')).fromNow());
        });
    }

    function hideOldWallItems(){
        // dismiss all old items
        $('.social-avatar').each(function () {
            if ($(this).find('small').attr('data-from-now') < Etincelle.User.last_login) {
                $(this).addClass('text-muted');
                $(this).next().addClass('text-muted');
            }
        });

        $('.social-footer > .tree-level-1').each(function () {
            if ($(this).find('small').attr('data-from-now') < Etincelle.User.last_login) {
                $(this).addClass('text-muted');
            }
        });
    }

    docReady(function () {

        var infinite = new Waypoint.Infinite({
            element: $('.infinite-container')[0],
            onAfterPageLoad: function ($items){
                hideOldWallItems();
            }
        });

        hideOldWallItems();



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
                    data.content = data.content.replace('/<img/', '<img class="img-responsive"');
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