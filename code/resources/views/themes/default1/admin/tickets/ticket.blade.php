!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Faveo | HELP DESK</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="http://code.ionicframework.com/ionicons/2.0.0/css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <link href="plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet" type="text/css" />
        <link href="plugins/fullcalendar/fullcalendar.print.css" rel="stylesheet" type="text/css" media='print' />
        <link href="dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
        <link href="dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
        <link href="plugins/iCheck/flat/blue.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="dist/css/tabby.css" type="text/css">
        <link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
    </head>
    <body class="skin-blue">
        <div class="wrapper">
            <header class="main-header">
                <a href="../../index2.html" class="logo"><b>Faveo</b> HELP DESK</a>
                <nav class="navbar navbar-static-top" role="navigation">
                    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <div class="collapse navbar-collapse" id="navbar-collapse">
                        <ul class="tabs tabs-horizontal nav navbar-nav">
                            <li class="active"><a data-target="#tabA" href="#">Home</a></li>
                            <li><a data-target="#tabB" href="#">Staff</a></li>
                            <li><a data-target="#tabC" href="#">Department</a></li>
                            <li><a data-target="#tabD" href="#">Users</a></li>
                        </ul>
                        <ul class="nav navbar-nav navbar-right">
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img src="dist/img/user2-160x160.jpg" class="user-image" alt="User Image"/>
                                    <span class="hidden-xs">Alexander Pierce</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="user-header">
                                        <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image" />
                                        <p>
                                            Alexander Pierce - Web Developer
                                            <small>Member since Nov. 2012</small>
                                        </p>
                                    </li>
                                    <li class="user-body">
                                        <div class="col-xs-4 text-center">
                                            <a href="#">Followers</a>
                                        </div>
                                        <div class="col-xs-4 text-center">
                                            <a href="#">Sales</a>
                                        </div>
                                        <div class="col-xs-4 text-center">
                                            <a href="#">Friends</a>
                                        </div>
                                    </li>
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="#" class="btn btn-default btn-flat">Profile</a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="#" class="btn btn-default btn-flat">Sign out</a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <aside class="main-sidebar">
                <section class="sidebar">
                    <form action="#" method="get" class="sidebar-form">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control" placeholder="Search..."/>
                            <span class="input-group-btn">
                                <button type='submit' name='seach' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
                            </span>
                        </div>
                    </form>
                    <ul class="sidebar-menu">
                        <li class="header">TICKETS</li>
                        <li>
                            <a href="../widgets.html">
                                <i class="fa fa-envelope"></i> <span>Inbox</span> <small class="label pull-right bg-green">5</small>
                            </a>
                        </li>
                        <li>
                            <a href="../widgets.html">
                                <i class="fa fa-user"></i> <span>My Tickets</span> <small class="label pull-right bg-green">2</small>
                            </a>
                        </li>
                        <li>
                            <a href="../widgets.html">
                                <i class="fa fa-th"></i> <span>Unassigned</span> <small class="label pull-right bg-green">4</small>
                            </a>
                        </li>
                        <li>
                            <a href="../widgets.html">
                                <i class="fa fa-trash-o"></i> <span>Trash</span> <small class="label pull-right bg-green">89</small>
                            </a>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-folder-open"></i> <span>General</span> <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href=""><i class="fa fa-circle-o"></i>Open<small class="label pull-right bg-green">4</small></a></li>
                                <li><a href=""><i class="fa fa-circle-o"></i>Inprogress<small class="label pull-right bg-green">3</small></a></li>
                                <li><a href=""><i class="fa fa-circle-o"></i>Closed<small class="label pull-right bg-green">55</small></a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-folder-open"></i> <span>Support</span> <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href=""><i class="fa fa-circle-o"></i>Open<small class="label pull-right bg-green">1</small></a></li>
                                <li><a href=""><i class="fa fa-circle-o"></i>Inprogress<small class="label pull-right bg-green">6</small></a></li>
                                <li><a href=""><i class="fa fa-circle-o"></i>Closed<small class="label pull-right bg-green">88</small></a></li>
                            </ul>
                        </li>
                        <li class="header">LABELS</li>
                        <li><a href="#"><i class="fa fa-circle-o text-danger"></i> Important</a></li>
                        <li><a href="#"><i class="fa fa-circle-o text-warning"></i> Warning</a></li>
                        <li><a href="#"><i class="fa fa-circle-o text-info"></i> Information</a></li>
                    </ul>
                </section>
            </aside>
            <div class="content-wrapper">
                <div class="tab-content" style="background-color: white;padding: 0 20px 0 20px">
                    <div class="collapse navbar-collapse" id="navbar-collapse">
                        <div class="tabs-content">
                            <div class="tabs-pane active" id="tabA">
                                <ul class="nav navbar-nav">
                                    <li><a href="#">Link1</a></li>
                                    <li><a href="#">Link2</a></li>
                                </ul>
                            </div>
                            <div class="tabs-pane" id="tabB">
                                <ul class="nav navbar-nav">
                                    <li><a href="#">Link3</a></li>
                                    <li><a href="#">Link4</a></li>
                                </ul>
                            </div>
                            <div class="tabs-pane" id="tabC">
                                <ul class="nav navbar-nav">
                                    <li><a href="#">Link5</a></li>
                                    <li><a href="#">Link6</a></li>
                                </ul>
                            </div>
                            <div class="tabs-pane" id="tabD">
                                <ul class="nav navbar-nav">
                                    <li><a href="#">Link7</a></li>
                                    <li><a href="#">Link8</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <section class="content-header">
                    <h1>
                        Tickets
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Mailbox</li>
                    </ol>
                </section>
                <section class="content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Inbox </h3> <small> 5 new messages</small>
                                    <div class="box-tools pull-right">
                                        <div class="has-feedback">
                                            <input type="text" class="form-control input-sm" placeholder="Search Mail"/>
                                            <span class="glyphicon glyphicon-search form-control-feedback"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-body no-padding">
                                    <div class="mailbox-controls">
                                        <button class="btn btn-default btn-sm checkbox-toggle"><i class="fa fa-square-o"></i></button>
                                        <div class="btn-group">
                                            <button class="btn btn-default btn-sm"><i class="fa fa-trash-o"></i></button>
                                            <button class="btn btn-default btn-sm"><i class="fa fa-reply"></i></button>
                                            <button class="btn btn-default btn-sm"><i class="fa fa-share"></i></button>
                                        </div>
                                        <button class="btn btn-default btn-sm"><i class="fa fa-refresh"></i></button>
                                        <div class="pull-right">
                                            1-5 /5
                                            <div class="btn-group">
                                                <button class="btn btn-default btn-sm"><i class="fa fa-chevron-left"></i></button>
                                                <button class="btn btn-default btn-sm"><i class="fa fa-chevron-right"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive mailbox-messages">
                                        <table class="table table-hover table-striped">
                                            <thead>
                                            <th>
                                            </th>
                                            <th>
                                            </th>
                                            <th>
                                                subject
                                            </th>
                                            <th>
                                                Ticket ID
                                            </th>
                                            <th>
                                                last Replier
                                            </th>
                                            <th>
                                                Replies
                                            </th>
                                            <th>
                                                Priority
                                            </th>
                                            <th>
                                                Last Activity
                                            </th>
                                            <th>
                                                Reply Due
                                            </th>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><input type="checkbox" /></td>
                                                    <td class="mailbox-star"><a href="#"><i class="fa fa-star text-yellow"></i></a></td>
                                                    @foreach($threads as $thread)
                                                    <td class="mailbox-name"><a href="timeline.html">{{$thread->ticket_subject}}</a></td>
                                                    <td class="mailbox-Id">{{$thread->ticket_id}}</td>
                                                     @endforeach
                                                    <td class="mailbox-last-reply">client</td>
                                                    <td class="mailbox-replies">11</td>
                                                    <td class="mailbox-priority"><spam class="text-green">Low</spam></td>
                                            <td class="mailbox-last-activity">11h 59m 23s</td>
                                            <td class="mailbox-date">5h 23m 03s</td>
                                            </tr>
                                            <tr>
                                                <td><input type="checkbox" /></td>
                                                <td class="mailbox-star"><a href="#"><i class="fa fa-star text-yellow"></i></a></td>
                                                <td class="mailbox-name"><a href="timeline.html">Testing</a></td>
                                                <td class="mailbox-Id">#12345</td>
                                                <td class="mailbox-last-reply">client</td>
                                                <td class="mailbox-replies">11</td>
                                                <td class="mailbox-priority"><spam class="text-yellow">Medium</spam></td>
                                            <td class="mailbox-last-activity">11h 59m 23s</td>
                                            <td class="mailbox-date">5h 23m 03s</td>
                                            </tr>
                                            <tr>
                                                <td><input type="checkbox" /></td>
                                                <td class="mailbox-star"><a href="#"><i class="fa fa-star text-yellow"></i></a></td>
                                                <td class="mailbox-name"><a href="timeline.html">Testing</a></td>
                                                <td class="mailbox-Id">#12345</td>
                                                <td class="mailbox-last-reply">client</td>
                                                <td class="mailbox-replies">11</td>
                                                <td class="mailbox-priority"><spam class="text-red">High</spam></td>
                                            <td class="mailbox-last-activity">11h 59m 23s</td>
                                            <td class="mailbox-date">5h 23m 03s</td>
                                            </tr>
                                            <tr>
                                                <td><input type="checkbox" /></td>
                                                <td class="mailbox-star"><a href="#"><i class="fa fa-star text-yellow"></i></a></td>
                                                <td class="mailbox-name"><a href="timeline.html">Testing</a></td>
                                                <td class="mailbox-Id">#12345</td>
                                                <td class="mailbox-last-reply">client</td>
                                                <td class="mailbox-replies">11</td>
                                                <td class="mailbox-priority"><spam class="text-yellow">Medium</spam></td>
                                            <td class="mailbox-last-activity">11h 59m 23s</td>
                                            <td class="mailbox-date">5h 23m 03s</td>
                                            </tr>
                                            <tr>
                                                <td><input type="checkbox" /></td>
                                                <td class="mailbox-star"><a href="#"><i class="fa fa-star text-yellow"></i></a></td>
                                                <td class="mailbox-name"><a href="timeline.html">Testing</a></td>
                                                <td class="mailbox-Id">#12345</td>
                                                <td class="mailbox-last-reply">client</td>
                                                <td class="mailbox-replies">11</td>
                                                <td class="mailbox-priority"><spam class="text-red">High</spam></td>
                                            <td class="mailbox-last-activity">11h 59m 23s</td>
                                            <td class="mailbox-date">5h 23m 03s</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="box-footer no-padding">
                                    <div class="mailbox-controls">
                                        <button class="btn btn-default btn-sm checkbox-toggle"><i class="fa fa-square-o"></i></button>                    
                                        <div class="btn-group">
                                            <button class="btn btn-default btn-sm"><i class="fa fa-trash-o"></i></button>
                                            <button class="btn btn-default btn-sm"><i class="fa fa-reply"></i></button>
                                            <button class="btn btn-default btn-sm"><i class="fa fa-share"></i></button>
                                        </div>
                                        <button class="btn btn-default btn-sm"><i class="fa fa-refresh"></i></button>
                                        <div class="pull-right">
                                            1-5/5
                                            <div class="btn-group">
                                                <button class="btn btn-default btn-sm"><i class="fa fa-chevron-left"></i></button>
                                                <button class="btn btn-default btn-sm"><i class="fa fa-chevron-right"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    <b>Version</b> 0.1
                </div>
                <strong>Copyright &copy; 2014-2015 <a href="http://www.ladybirdweb.com">Ladybird Web Solution</a>.</strong> All rights reserved.
            </footer>
        </div>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
        <script src='plugins/fastclick/fastclick.min.js'></script>
        <script src="dist/js/app.min.js" type="text/javascript"></script>
        <script src="dist/js/demo.js" type="text/javascript"></script>
        <script src="plugins/iCheck/icheck.min.js" type="text/javascript"></script>
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
        </script>
        <script src="dist/js/tabby.js"></script>
    </body>
</html>
