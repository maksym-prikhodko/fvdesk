<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Faveo | HELP DESK</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="http://code.ionicframework.com/ionicons/2.0.0/css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <link href="dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
        <link href="dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
        <link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="dist/css/tabby.css" type="text/css">
        <link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
        <link href="dist/css/editor.css" type="text/css" rel="stylesheet"/>
        <script src="dist/js/jquery-2.1.0.min.js"></script>        
        <link href="http://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
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
                                        <div class="col-xs-6 text-center">
                                            <a href="#">Department:Sales</a>
                                        </div>
                                        <div class="col-xs-6 text-center">
                                            <a href="#">Level 1 Support</a>
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
                        <li class="header">TICKET INFORMATION</li>
                        <li>
                            <a href="">
                                <span>TICKET ID</span> 
                                </br><b>#123456</b>
                            </a>
                        </li>
                        <li>
                            <a href="">
                                <span>USER</span> 
                                </br><i class="fa fa-user"> </i> <b>Username</b>
                            </a>
                        </li>
                        <li>
                            <a href="">
                                <span>ASSIGNED TO</span> 
                                </br> <b>Name</b>
                            </a>
                        </li>
                        <li class="header">TICKETS</li>
                        <li>
                            <a href="ticket.html">
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
                                    <li><a href="#">Home</a></li>
                                    <li><a href="#">My Preferences</a></li>
                                    <li><a href="#">Notification</a></li>
                                    <li><a href="#">Comments</a></li>
                                </ul>
                            </div>
                            <div class="tabs-pane" id="tabB">
                                <ul class="nav navbar-nav">
                                    <li><a href="#">Manage Tickets</a></li>
                                    <li><a href="#">Search</a></li>
                                    <li><a href="#">New Ticket</a></li>
                                    <li><a href="#">Views</a></li>
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
                        Timeline
                        <small>example</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li><a href="#">UI</a></li>
                        <li class="active">Timeline</li>
                    </ol>
                </section>
                <section class="content">
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title"><i class="fa fa-user"> </i> Username </h3> ( organisation )
                            <div class="pull-right">
                                <button type="button" class="btn btn-default"><i class="fa fa-edit" style="color:green;"> </i> Edit</button>
                                <button type="button" class="btn btn-default"><i class="fa fa-print" style="color:blue;"> </i> Print</button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-exchange" style="color:teal;"> </i> 
                                        Change Status <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="#"><i class="fa fa-check" style="color:green;"> </i>Closed</a></li>
                                        <li><a href="#"><i class="fa fa-check-circle-o " style="color:green;"> </i> Resolved</a></li>
                                    </ul>
                                </div>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cogs" style="color:teal;"> </i> 
                                        More <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu pull-right">
                                        <li><a href="#"><i class="fa fa-users" style="color:green;"> </i>Change Owner</a></li>
                                        <li><a href="#"><i class="fa fa-edit" style="color:blue;"> </i>Manage Forms</a></li>
                                        <li><a href="#"><i class="fa fa-trash" style="color:red;"> </i>Delete Ticket</a></li>
                                        <li><a href="#"><i class="fa fa-ban" style="color:red;"> </i> Ban Email</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <section class="content">
                                    <div class="col-md-12"> 
                                        <div class="callout callout-info">
                                            <div class="row">
                                                <div class="col-md-3"> 
                                                    <b>SLA Plan: </b> Default SLA Plan
                                                </div>
                                                <div class="col-md-3"> 
                                                    <b>Create Date: </b> 12/03/2015 1:40 Pm
                                                </div>
                                                <div class="col-md-3"> 
                                                    <b>Due Date: </b> 15/03/2015 1:05 pm
                                                </div>
                                                <div class="col-md-3"> 
                                                    <b>Last Response: </b> 15/03/2015 1:05 pm
                                                </div>
                                            </div>
                                        </div>
                                    </div>      
                                    <div class="col-md-6"> 
                                        <table class="table table-hover">
                                            <tr><td><b>Status:</b></td>       <td>Open</td></tr>
                                            <tr><td><b>Priority:</b></td>     <td>High</td></tr>
                                            <tr><td><b>Department:</b></td>   <td>Support</td></tr>
                                            <tr><td><b>Email:</b></td>          <td>Email@email.com</td></tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6"> 
                                        <table class="table table-hover">
                                            <tr><td><b>Phone:</b></td>          <td>9999999999</td></tr>
                                            <tr><td><b>Source:</b></td>         <td>Email (IP address)</td></tr>
                                            <tr><td><b>Help Topic:</b></td>     <td>— Unassigned —</td></tr>
                                            <tr><td><b>Last Message:</b></td>   <td>Default SLA</td></tr>
                                        </table>
                                    </div>
                                </section> 
                            </div>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-xs-12'>
                            <div class="nav-tabs-custom">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#General" data-toggle="tab" style="color:green;"><i class="fa fa-reply-all"> </i> Reply</a></li>
                                    <li><a href="#Reply" data-toggle="tab" style="color:orange;"><i class="fa fa-mail-forward" > </i> Forward</a></li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="General">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-default"><i class="fa fa-check-square-o" style="color:green;"> </i> Update</button>
                                            <button type="button" class="btn btn-default"><i class="fa fa-hand-o-right" style="color:orange;"> </i>  Assign</button>
                                            <button type="button" class="btn btn-default"><i class="fa fa-file-text" style="color:blue;"> </i>  Internal Notes</button>
                                            <button type="button" class="btn btn-default"><i class="fa fa-arrows-alt" style="color:red;"> </i>  Surrender</button>
                                        </div>
                                        <form>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label>From</label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" name="from" id="from" style="width:40%"/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label>To</label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" name="to" id="to" style="width:55%"/>
                                                    </div>
                                                </div>          
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label>Response</label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <select class="form-control" style="width:55%" >
                                                            <option>Select a canned response</option>
                                                            <option>Original Message</option>
                                                            <option>Last Message</option>
                                                        </select>
                                                    </div>
                                                </div>          
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label>Reply Content</label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <textarea id="txtEditor"></textarea>                                            
                                                    </div>
                                                </div>
                                            </div>
                                        </form>               
                                    </div>
                                    <div class="tab-pane" id="Reply" >
                                        <div class="form-group">
                                            <button type="button" class="btn btn-default"><i class="fa fa-mail-forward" style="color:green;"> </i> Send</button>
                                            <button type="button" class="btn btn-default"><i class="fa fa-th-large" style="color:teal;"> </i> Option</button>
                                            <button type="button" class="btn btn-default"><i class="fa fa-file-text" style="color:blue;"> </i> Internal Notes</button>
                                        </div>
                                        <form>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label>From</label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" name="from" id="from" style="width:40%" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label>To</label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" name="to" id="to" style="width:55%" />
                                                    </div>
                                                </div>      
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label>Subject</label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" name="from" id="from" style="width:100%" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label>Response</label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <select class="form-control" style="width:55%" >
                                                            <option>Select a canned response</option>
                                                            <option>Original Message</option>
                                                            <option>Last Message</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label>Reply Content</label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <textarea id="editor2" name="editor2" rows="5" cols="80">
                                                        </textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>               
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <ul class="timeline">
                                        <li class="time-label">
                                            <span class="bg-red">
                                                10 Feb. 2014
                                            </span>
                                            <ul class="pagination pagination-sm no-margin pull-right">
                                                <li><a href="#"><i class="fa fa-chevron-left"></i></a></li>
                                                <li><a href="#">1</a></li>
                                                <li><a href="#">2</a></li>
                                                <li><a href="#">3</a></li>
                                                <li><a href="#"><i class="fa fa-chevron-right"></i></a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <i class="fa fa-user bg-aqua" title="Posted by Customer"></i>
                                            <div class="timeline-item">
                                                <span class="time"><i class="fa fa-clock-o"></i> 5 mins ago </span>
                                                <h3 class="timeline-header"><a href="#">Customer Reply</a></h3>
                                                <div class="timeline-body">
                                                    Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles,
                                                    weebly ning heekya handango imeem plugg dopplr jibjab, movity
                                                    jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo kaboodle
                                                    quora plaxo ideeli hulu weebly balihoo...
                                                </div>
                                                <div class='timeline-footer'>
                                                    <a class="btn btn-primary btn-xs"> <i class="fa fa-file-o" style="color:#fff;"> </i> Read more</a>
                                                    <button type="button" class="btn btn-success btn-xs"><i class="fa fa-edit" style="color:#fff;"> </i> Edit</button>
                                                    <a class="btn btn-danger btn-xs"><i class="fa fa-trash" style="color:#fff;"> </i> Delete</a>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <i class="fa fa-envelope bg-blue" title="Posted by Customer"></i>
                                            <div class="timeline-item">
                                                <span class="time"><i class="fa fa-clock-o"></i> 12:05</span>
                                                <h3 class="timeline-header"><a href="#">Reseller Club</a></h3>
                                                <div class="timeline-body">
                                                    Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles,
                                                    weebly ning heekya handango imeem plugg dopplr jibjab, movity
                                                    jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo kaboodle
                                                    quora plaxo ideeli hulu weebly balihoo...
                                                </div>
                                                <div class='timeline-footer'>
                                                    <a class="btn btn-primary btn-xs"> <i class="fa fa-file-o" style="color:#fff;"> </i> Read more</a>
                                                    <button type="button" class="btn btn-success btn-xs"><i class="fa fa-edit" style="color:#fff;"> </i> Edit</button>
                                                    <a class="btn btn-danger btn-xs"><i class="fa fa-trash" style="color:#fff;"> </i> Delete</a>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <i class="fa fa-group bg-yellow" title="Posted by Support Team"></i>
                                            <div class="timeline-item">
                                                <span class="time"><i class="fa fa-clock-o"></i> 27 mins ago</span>
                                                <h3 class="timeline-header"><a href="#">Team Reply</a></h3>
                                                <div class="timeline-body">
                                                    Take me to your leader!
                                                    Switzerland is small and neutral!
                                                    We are more like Germany, ambitious and misunderstood!
                                                </div>
                                                <div class='timeline-footer'>
                                                    <a class="btn btn-primary btn-xs"> <i class="fa fa-file-o" style="color:#fff;"> </i> Read more</a>
                                                    <button type="button" class="btn btn-success btn-xs"><i class="fa fa-edit" style="color:#fff;"> </i> Edit</button>
                                                    <a class="btn btn-danger btn-xs"><i class="fa fa-trash" style="color:#fff;"> </i> Delete</a>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="time-label">
                                            <span class="bg-green">
                                                3 Jan. 2014
                                            </span>
                                        </li>
                                        <li>
                                            <i class="fa fa-mail-reply-all bg-purple" title="Posted by System"></i>
                                            <div class="timeline-item">
                                                <span class="time"><i class="fa fa-clock-o"></i> 2 days ago</span>
                                                <h3 class="timeline-header"><a href="#">System Reply</a></h3>
                                                <div class="timeline-body">
                                                    this is a system reply
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <i class="fa fa-clock-o bg-gray"></i>
                                            <ul class="pagination pagination-sm no-margin pull-right">
                                                <li><a href="#"><i class="fa fa-chevron-left"></i></a></li>
                                                <li><a href="#">1</a></li>
                                                <li><a href="#">2</a></li>
                                                <li><a href="#">3</a></li>
                                                <li><a href="#"><i class="fa fa-chevron-right"></i></a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    <b>Version</b> 2.0
                </div>
                <strong>Copyright &copy; 2014-2015 <a href="http://www.ladybirdweb.com">Ladybird Web Solution</a>.</strong> All rights reserved.
            </footer>
        </div>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>
        <script src='plugins/fastclick/fastclick.min.js'></script>
        <script src="dist/js/app.min.js" type="text/javascript"></script>
        <script src="dist/js/demo.js" type="text/javascript"></script>
        <script src="dist/js/tabby.js"></script>
        <script src="../dist/js/editor.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $("#txtEditor").Editor();
            });
        </script>
    </body>
</html>
