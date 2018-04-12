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
        div .col-md-2 {
            height: 100px;
            padding-top: 40px;
        }
    </style>

</head>

<body class="gray-bg">
<div id="slide-digits">
    <div class="row text-center" style="margin-bottom: 20px">
        <?php for($i = 0; $i < 6; $i++): ?>
        <div class="col-md-2" id="digit{{$i}}">
            <div class="filled hidden">
                <i class="fa fa-circle fa-2x"></i>
            </div>
            <div class="empty">
                -
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <div class="row text-center">
        <?php for($i = 1; $i < 10; $i++): ?>
        <div class="col-md-4" style="margin-bottom: 10px">
            <a href="#" class="btn btn-block btn-primary btn-lg btn-digit" data-value="{{$i}}">{{$i}}</a>
        </div>
        <?php endfor; ?>
        <div class="col-md-4" style="margin-bottom: 10px">
            <a href="#" class="btn btn-block btn-default btn-lg" id="btn-cancel">C</a>
        </div>
        <div class="col-md-4" style="margin-bottom: 10px">
            <a href="#" class="btn btn-block btn-primary btn-lg btn-digit" data-value="0">0</a>
        </div>
        <div class="col-md-4" style="margin-bottom: 10px">
            <a href="#" class="btn btn-block btn-default btn-lg" id="btn-empty">X</a>
        </div>
    </div>
</div>
<div id="slide-user" class="row hidden">
    <div class="col-md-12">
        <div class="m-b-md">
            <img alt="image" class="img-circle circle-border" style="float: right" id="user-picture" src="">
        </div>
        <h1 id="user-name">Sébastien Hordeaux</h1>
        <h2 id="countdown">15:00</h2>
        <a href="#" class="btn btn-primary btn-lg" id="btn-add-time">+15 min</a>
        <a href="#" class="btn btn-primary btn-lg" id="btn-leave">Libérer</a>
    </div>
</div>
{{ HTML::script('js/jquery-2.1.1.js') }}

<script type="application/javascript">
    var currentIndex = 0;
    var code = [];
    var codeLength = 6;
    var remainingMinuts = 15;
    var remainingSeconds = 0;
    $().ready(function () {
        $('.btn-digit').click(newDigit);
        $('#btn-cancel').click(canceDigits);
        $('#btn-empty').click(emptyDigits);
        $('#btn-leave').click(leave);
        $('#btn-add-time').click(addMoreTime);
    });

    function newDigit() {
        if (currentIndex < codeLength + 1) {
            code[currentIndex] = $(this).attr('data-value');
            var div = $('#digit' + currentIndex);
            div.find('.filled').removeClass('hidden');
            div.find('.empty').addClass('hidden');
            currentIndex++;
            if (currentIndex === codeLength) {
                var user_code = code.join('');
                emptyDigits();

                $.ajax({
                    dataType: 'json',
                    url: '{{ URL::route('phonebox_auth', array('location_slug'=> $location_slug, 'key'=>$key, 'box_id'=>$box_id)) }}',
                    type: "POST",
                    data: {
                        code: code.join('')
                    },
                    success: function (data) {
                        console.log(data);
                        if (data.status == 'error') {
                            alert(data.message);
                        } else {
                            $('#user-name').text(data.username);
                            $('#user-picture').attr('src', data.picture);
                            showUserSlide();
                        }
                    },
                    error: function (data) {
                        // afficher un message générique?
                        console.log(data);
                    }
                });
            }
        }
        return false;
    }

    function refreshCountdown() {
        if (remainingSeconds === 0) {
            if (remainingMinuts > 0) {
                remainingMinuts--;
            } else {
                // timeout
                leave();
            }
            remainingSeconds = 59;
        } else {
            remainingSeconds--;
        }
        var txt = remainingMinuts + ':';
        if (remainingSeconds < 10) {
            txt += '0';
        }
        txt += remainingSeconds;
        $('#countdown').text(txt);
        setTimeout(refreshCountdown, 1000);
        return false;
    }

    function showUserSlide() {
        $('#slide-digits').addClass('hidden');
        $('#slide-user').removeClass('hidden');
        remainingMinuts = 15;
        remainingSeconds = 0;
        setTimeout(refreshCountdown, 1000);
        return false;
    }


    function showDigitSlide() {
        $('#slide-digits').removeClass('hidden');
        $('#slide-user').addClass('hidden');
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

    function addMoreTime() {
        remainingMinuts += 15;
        return false;
    }

    function leave() {
        emptyDigits();
        showDigitSlide();
        return false;
    }
</script>
</body>

</html>
