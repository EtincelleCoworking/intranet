<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $_ENV['organisation_name'] }} - PhoneBox</title>

    {{ HTML::style('css/bootstrap.min.css') }}
    {{ HTML::style('font-awesome/css/font-awesome.min.css') }}

    {{ HTML::style('css/animate.css') }}
    {{ HTML::style('css/style.css') }}

    <style type="text/css">
        div .col-xs-2 {
            height: 100px;
            padding-top: 40px;
        }

        .btn-primary, .btn-primary:visited {
            background-color: #461A5E;
            border-color: #461A5E;
        }

        .btn-primary:hover {
            background-color: #000000;
            border-color: #000000;
        }

        .btn-default {
            background-color: #000000;
            border-color: #000000;
        }
    </style>

</head>

<body class="gray-bg">
<div class="row">
    <div class="col-xs-12 h-200 slide-digits">
        <div class="col-xs-9">
            <h1 style="margin-top: 60px; font-size: 45pt; font-weight: bold">Disponible</h1>
        </div>
        <div class="col-xs-3">
        </div>
    </div>

    <div class="col-xs-12 h-200 slide-user">
        <div class="col-xs-9">
            <h1 style="font-size: 40pt; font-weight: bold" id="user-name"></h1>
            <h2 id="countdown" style="font-size: 40pt; font-weight: bold;">15:00</h2>
        </div>
        <div class="col-xs-3">
            <div class="m-b-md">
                <img alt="image" class="img-circle circle-border" id="user-picture" src="" style="float: right">
            </div>
        </div>
    </div>

</div>


<div class="row">
    <div class="col-xs-12">
        <div class="col-xs-6">
            <p class="slide-digits">Saisissez votre code personnel pour réserver la salle.</p>
            <p class="slide-user">Saisissez votre code personnel pour libérer la salle.</p>
            <div class="row text-center" style="margin-bottom: 20px">
                <div class="col-xs-12">
                    <?php for($i = 0; $i < 6; $i++): ?>
                    <div class="col-xs-2" id="digit{{$i}}">
                        <div class="filled hidden">
                            <i class="fa fa-circle fa-2x"></i>
                        </div>
                        <div class="empty">
                            <i class="fa fa-circle-o fa-2x"></i>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            <p class="text-muted slide-digits">Votre code personnel est disponible depuis l'intranet à l'adresse
                https://intranet.coworking-toulouse.com/</p>
            <p><a href="#" class="btn btn-primary btn-lg btn-add-time slide-user">+15 min</a></p>
            <p class="text-danger" id="feedback"></p>
        </div>
        <div class="col-xs-6">
            <div class="row text-center">
                <?php for($i = 1; $i < 10; $i++): ?>
                <div class="col-xs-4" style="margin-bottom: 10px">
                    <a href="#" class="btn btn-block btn-primary btn-lg btn-digit"
                       data-value="{{$i}}">{{$i}}</a>
                </div>
                <?php endfor; ?>
                <div class="col-xs-4" style="margin-bottom: 10px">
                    <a href="#" class="btn btn-block btn-default btn-lg btn-cancel">C</a>
                </div>
                <div class="col-xs-4" style="margin-bottom: 10px">
                    <a href="#" class="btn btn-block btn-primary btn-lg btn-digit" data-value="0">0</a>
                </div>
                <div class="col-xs-4" style="margin-bottom: 10px">
                    <a href="#" class="btn btn-block btn-default btn-lg btn-empty">X</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{ HTML::script('js/jquery-2.1.1.js') }}

<script type="application/javascript">
    // -- code
    var currentIndex = 0;
    var code = [];
    var codeLength = 6;
    // -- time
    var started_at;
    var requested_duration = 0;
    var max_duration = {{Phonebox::QUOTA}};
    var timerId;
    var lock_code = '';
    var session_id = false;


    $().ready(function () {
        $('.btn-digit').click(newDigit);
        $('.btn-cancel').click(canceDigits);
        $('.btn-empty').click(emptyDigits);
        $('.btn-add-time').click(addMoreTime);

        @if($phonebox->active_session)
        <?php $user = $phonebox->active_session->user; ?>
        max_duration = {{Phonebox::QUOTA}} - {{$user->getTotalPhoneboxUsageOverLastPeriod()}};
        lock_code = '{{$user->personnal_code}}';
        started_at = new Date('{{$phonebox->active_session->started_at}}').getTime();
        requested_duration = parseInt('{{ (strtotime($phonebox->active_session->ended_at) - strtotime($phonebox->active_session->started_at)) / 60 }}');

        showUserSlide({{$phonebox->active_session->id}}, '{{$user->fullname}}', '{{$user->getAvatarUrl(150)}}');

        @else

        showDigitSlide();

        @endif
    });

    function newDigit() {
        $('#feedback').hide();
        if (currentIndex < codeLength + 1) {
            code[currentIndex] = $(this).attr('data-value');
            console.log(code.join(''));
            var div = $('#digit' + currentIndex);
            div.find('.filled').removeClass('hidden');
            div.find('.empty').addClass('hidden');
            currentIndex++;
            if (currentIndex === codeLength) {
                if (session_id) {
                    if (lock_code == code.join('')) {
                        $.ajax({
                            dataType: 'json',
                            url: '{{ URL::route('phonebox_stop', array('location_slug'=> $location_slug, 'key'=>$key, 'box_id'=>$phonebox->id)) }}',
                            type: "POST",
                            data: {
                                session_id: session_id
                            },
                            success: function (data) {
                                leave();
                            },
                            error: function (data) {
                                // afficher un message générique?
                                console.log(data);
                            }
                        });
                    } else {
                        $('#feedback').text('Code incorrect').show();
                        console.log('Code incorrect (' + lock_code + ' vs ' + code.join('') + ')');
                        emptyDigits();
                    }
                } else {
                    $.ajax({
                        dataType: 'json',
                        url: '{{ URL::route('phonebox_auth', array('location_slug'=> $location_slug, 'key'=>$key, 'box_id'=>$phonebox->id)) }}',
                        type: "POST",
                        data: {
                            code: code.join('')
                        },
                        success: function (data) {
                            lock_code = code.join('');
                            console.log(data);
                            if (data.status == 'error') {
                                $('#feedback').text(data.message).show();
                            } else {
                                started_at = new Date().getTime();
                                requested_duration = Math.min(data.max_duration, {{Phonebox::DEFAULT_DURATION}});
                                showUserSlide(data.session_id, data.username, data.picture);
                            }
                            emptyDigits();
                        },
                        error: function (data) {
                            // afficher un message générique?
                            console.log(data);
                        }
                    });
                }

            }
        }
        return false;
    }

    function refreshCountdown() {
        timerId = setInterval(function () {
            var now = new Date().getTime();

            var distance = started_at + requested_duration * 1000 * 60 - now;

            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            txt = minutes.toString() + ':';
            if (seconds < 10) {
                txt += '0';
            }
            txt += seconds.toString();
            $('#countdown').text(txt);

            if (distance < 0) {
                clearInterval(timerId);
                leave();
            }
        }, 1000);
        return false;
    }

    function showUserSlide(sess_id, username, picture_url) {
        session_id = sess_id;
        $('#user-name').text(username);
        $('#user-picture').attr('src', picture_url);

        $('.slide-digits').hide();
        $('.slide-user').show();
        updateAddMoreButtonStatus();
        refreshCountdown();
        return false;
    }


    function showDigitSlide() {
        $('.slide-digits').show();
        $('.slide-user').hide();
        return false;
    }

    function canceDigits() {
        if (currentIndex > 0) {
            currentIndex--;
            var div = $('#digit' + currentIndex);
            div.find('.filled').addClass('hidden');
            div.find('.empty').removeClass('hidden');
        }
        return false;
    }

    function emptyDigits() {
        if (currentIndex > 0) {
            for (; currentIndex >= 0; currentIndex--) {
                var div = $('#digit' + currentIndex);
                div.find('.filled').addClass('hidden');
                div.find('.empty').removeClass('hidden');
            }
        }
        currentIndex = 0;
        code = [];
        return false;
    }

    function updateAddMoreButtonStatus(){
        if (requested_duration >= max_duration) {
            $('.btn-add-time').attr("disabled", "disabled")
                .addClass('btn-default')
                .removeClass('btn-primary');
        } else {
            $('.btn-add-time').removeAttr("disabled")
                .removeClass('btn-default')
                .addClass('btn-primary');
        }
    }

    function addMoreTime() {
        if (requested_duration + {{Phonebox::DEFAULT_DURATION}} <= max_duration) {
            requested_duration += {{Phonebox::DEFAULT_DURATION}};
        }
        console.log(requested_duration);
        updateAddMoreButtonStatus();
        $.ajax({
            dataType: 'json',
            url: '{{ URL::route('phonebox_update', array('location_slug'=> $location_slug, 'key'=>$key, 'box_id'=>$phonebox->id)) }}',
            type: "POST",
            data: {
                session_id: session_id,
                duration: requested_duration
            },
            success: function (data) {
                if (data.status == 'error') {
                    $('#feedback').text(data.message).show();
                    requested_duration = max_duration;
                } else {
                    requested_duration = data.duration;
                }

            },
            error: function (data) {
                // afficher un message générique?
                console.log(data);
            }
        });
        return false;
    }

    function leave() {
        session_id = false;
        emptyDigits();
        showDigitSlide();
        return false;
    }
</script>
</body>

</html>
