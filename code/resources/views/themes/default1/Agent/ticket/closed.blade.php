@extends('themes.default1.layouts.agentblank')
@section('Tickets')
class="active"
@stop
@section('ticket-bar')
active
@stop
@section('closed')
class="active"
@stop
@section('content')
<h3>
    Tickets
</h3>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Inbox </h3> <small> 5 new messages</small>
    </div>
    <div class="box-body no-padding">
        <div class="mailbox-controls">
            <button class="btn btn-default btn-sm checkbox-toggle"><i class="fa fa-square-o"></i></button>
            <button class="btn btn-default btn-sm"><i class="fa fa-trash-o"></i></button>
            <button class="btn btn-default btn-sm" onclick="click()" id="click"><i class="fa fa-refresh"></i></button>
            <div class="pull-right">
                <?php
$counted = count(App\Model\Ticket\Tickets::where('status', '>', 1)->where('status', '<', 4)->get());
if ($counted < 10) {
	echo $counted . "/" . $counted;
} else {
	echo "10/" . $counted;
}
?>
            </div>
        </div>
        <div class=" table-responsive mailbox-messages"  id="refresh">
            <table class="table table-hover table-striped">
                <thead>
                <th></th>
                <th>subject</th>
                <th>Ticket ID</th>
                <th>last Replier</th>
                <th>Replies</th>
                <th>Priority</th>
                <th>Last Activity</th>
                <th>Reply Due</th>
                </thead>
                <tbody id="hello">
                    <?php $tickets = App\Model\Ticket\Tickets::where('status', '>', 1)->where('status', '<', 4)->paginate(2);?>
                    @foreach ($tickets  as $ticket )
                    <tr>
                        <td><input type="checkbox" value="{{$ticket->id}}"/></td>
                        <?php $title = App\Model\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->first();?>
                        <td class="mailbox-name"><a href="{!! route('ticket.thread',[$ticket->id]) !!}">{{$title->title}}</a></td>
                        <td class="mailbox-Id">#{!! $ticket->ticket_number !!}</td>
                        <td class="mailbox-last-reply">client</td>
                        <td class="mailbox-replies">11</td>
                        <?php $priority = App\Model\Ticket\Ticket_Priority::where('priority_id', '=', $ticket->priority_id)->first();?>
                        <td class="mailbox-priority"><spam class="btn btn-{{$priority->priority_color}} btn-xs">{{$priority->priority}}</spam></td>
                <td class="mailbox-last-activity">11h 59m 23s</td>
                <td class="mailbox-date">5h 23m 03s</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            <div class="pull-right">
                <?php echo $tickets->setPath(url('/ticket'))->render();?>&nbsp;
            </div>
        </div>
    </div>
</div>
<script>
    $(function() {
        //Enable iCheck plugin for checkboxes
        //iCheck for checkbox and radio inputs
        $('input[type="checkbox"]').iCheck({
            checkboxClass: 'icheckbox_flat-blue',
            radioClass: 'iradio_flat-blue'
        });
        //Enable check and uncheck all functionality
        $(".checkbox-toggle").click(function() {
            var clicks = $(this).data('clicks');
            if (clicks) {
                //Uncheck all checkboxes
                $("input[type='checkbox']", ".mailbox-messages").iCheck("uncheck");
            } else {
                //Check all checkboxes
                $("input[type='checkbox']", ".mailbox-messages").iCheck("check");
            }
            $(this).data("clicks", !clicks);
        });
        //Handle starring for glyphicon and font awesome
        $(".mailbox-star").click(function(e) {
            e.preventDefault();
            //detect type
            var $this = $(this).find("a > i");
            var glyph = $this.hasClass("glyphicon");
            var fa = $this.hasClass("fa");
            //Switch states
            if (glyph) {
                $this.toggleClass("glyphicon-star");
                $this.toggleClass("glyphicon-star-empty");
            }
            if (fa) {
                $this.toggleClass("fa-star");
                $this.toggleClass("fa-star-o");
            }
        });
    });
    $(document).ready(function() { /// Wait till page is loaded
        $('#click').click(function() {
            $('#refresh').load('closed #refresh');
        });
    });
    // //  check box get data
    // jQuery(function($) {
    //     $("form input[id='check_all']").click(function() { // triggred check
    //         var inputs = $("form input[type='checkbox']"); // get the checkbox
    //         for(var i = 0; i < inputs.length; i++) { // count input tag in the form
    //             var type = inputs[i].getAttribute("type"); //  get the type attribute
    //                 if(type == "checkbox") {
    //                     if(this.checked) {
    //                         inputs[i].checked = true; // checked
    //                     } else {
    //                         inputs[i].checked = false; // unchecked
    //                      }
    //                 }
    //         }
    //     });
    //     $("form input[id='submit']").click(function() {  // triggred submit
    //         var count_checked = $("[name='data[]']:checked").length; // count the checked
    //         if(count_checked == 0) {
    //             alert("Please select a product(s) to delete.");
    //             return false;
    //         }
    //         if(count_checked == 1) {
    //             return confirm("Are you sure you want to delete these product?");
    //         } else {
    //             return confirm("Are you sure you want to delete these products?");
    //           }
    //     });
    // }); // jquery end
</script>
@stop
