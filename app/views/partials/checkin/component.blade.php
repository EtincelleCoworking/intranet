<?php if($timesheet = Auth::user()->getActiveTimesheet()){?>
<div class="ibox">
    <div class="ibox-title">
        <h5>Temps passé</h5>
    </div>
    <div class="ibox-content">
        <a href="{{route('checkin_stop')}}" class="btn btn-danger pull-right"><i class="fa fa-stop"></i></a>
        <h1 class="no-margins" id="checkin-status">
            <?php
            echo $timesheet->getCurrentDuration();
            ?>
        </h1>
    </div>
</div>

<script type="application/javascript">
    docReady(function () {
        setTimeout(function(){
            $.ajax({
                url: '{{route('checkin_status')}}',
                context: document.body
            }).done(function(data) {
                $( '#checkin-status' ).html(data);
            });
        }, 60 * 1000); // refresh every 30 sec
    });
</script>

<?php }else{ ?>
<div class="ibox">
    <div class="ibox-title">
        <h5>Temps passé</h5>
    </div>
    <div class="ibox-content">
        <a href="{{route('checkin_start')}}" class="btn btn-primary pull-right"><i class="fa fa-play"></i></a>
        <h1 class="no-margins">
            -
        </h1>
    </div>
</div>

<?php }?>
