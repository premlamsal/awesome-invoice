<?php
    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    // will get http://localhost:8000/install 
?>
<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Welcome to Application</title>

        <!-- CSS -->
        <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
        <link rel="stylesheet" href="<?php echo $actual_link; ?>/assets/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo $actual_link; ?>/assets/font-awesome/css/font-awesome.min.css">
        <link rel="stylesheet" href="<?php echo $actual_link; ?>/assets/css/form-elements.css">
        <link rel="stylesheet" href="<?php echo $actual_link; ?>/assets/css/style.css">
        <!-- custom css by prem -->
        <link rel="stylesheet" type="text/css" href="<?php echo $actual_link; ?>/assets/css/main.css">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->

        <!-- Favicon and touch icons -->
        <link rel="shortcut icon" href="<?php echo $actual_link; ?>/assets/ico/favicon.png">
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo $actual_link; ?>/assets/ico/apple-touch-icon-144-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo $actual_link; ?>/assets/ico/apple-touch-icon-114-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo $actual_link; ?>/assets/ico/apple-touch-icon-72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" href="<?php echo $actual_link; ?>/assets/ico/apple-touch-icon-57-precomposed.png">



    </head>

    <body>

        <!-- Top menu -->
        <nav class="navbar navbar-inverse navbar-no-bg" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#top-navbar-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <!-- <a class="navbar-brand" href="index.php">BootZard - Bootstrap Wizard Template</a> -->
                </div>
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="top-navbar-1">
                    <!-- <ul class="nav navbar-nav navbar-right">
                        <li>
                            <span class="li-text">
                                Put some text or
                            </span>
                            <a href="#"><strong>links</strong></a>
                            <span class="li-text">
                                here, or some icons:
                            </span>
                            <span class="li-social">
                                <a href="https://www.facebook.com/pages/Azmindcom/196582707093191" target="_blank"><i class="fa fa-facebook"></i></a>
                                <a href="https://twitter.com/anli_zaimi" target="_blank"><i class="fa fa-twitter"></i></a>
                                <a href="https://plus.google.com/+AnliZaimi_azmind" target="_blank"><i class="fa fa-google-plus"></i></a>
                                <a href="https://github.com/AZMIND" target="_blank"><i class="fa fa-github"></i></a>
                            </span>
                        </li>
                    </ul> -->
                </div>
            </div>
        </nav>

        <!-- Top content -->
        <div class="top-content">
            <div class="container">

                <div class="row">
                    <div class="col-sm-8 col-sm-offset-2 text">
                        <h1>Welcome to <strong>Awesome</strong> Invoice</h1>
                        <div class="description">
                            <p>
                                You are one step behind to run applicaiton.
                                Learn more for <a href="http://premlamsal.com.np/awesome-invoice/"><strong>Awesome</strong></a> Invoice
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3 form-box">
                        <form role="form" action="" method="post" class="f1">

                            <h3>Install Application</h3>
                            <p>Fill in the form to get instant access</p>
                            <div class="f1-steps">
                                <div class="f1-progress">
                                    <div class="f1-progress-line" data-now-value="16.66" data-number-of-steps="3" style="width: 16.66%;"></div>
                                </div>

                                <div class="f1-step active">
                                    <div class="f1-step-icon"><i class="fa fa-cog"></i></div>
                                    <p>Application</p>
                                </div>

                                <div class="f1-step">
                                    <div class="f1-step-icon"><i class="fa fa-database"></i></div>
                                    <p>Databse</p>
                                </div>

                                <div class="f1-step">
                                    <div class="f1-step-icon"><i class="fa fa-key"></i></div>
                                    <p>Account</p>
                                </div>


                            </div>
                            <div id="dynamicBtn">


                            </div>
                              <div id="responseMsg" style="text-align: center;">

                             </div>

                            <fieldset chunk="app">
                               <!--  <div class="form-group">
                                    <label class="sr-only" for="f1-about-yourself">About yourself</label>
                                    <textarea name="f1-about-yourself" placeholder="..."
                                                         class="f1-about-yourself form-control" id="f1-about-yourself"></textarea>
                                </div> -->
                                  <div class="form-group">
                                     <label>Application Name</label>
                                    <input type="text" id="APP_NAME" placeholder="My Application" class="form-control">
                                     <div id="error_app_name" class="text-danger">

                                    </div>
                                </div>
                                 <div class="form-group">
                                     <label>Application Environment</label>
                                    <input type="text" id="APP_ENV" placeholder="local" class="form-control">
                                     <div id="error_app_env" class="text-danger">

                                    </div>
                                </div>
                                 <div class="form-group">
                                     <label>Application URL</label>
                                    <input type="text" id="APP_URL" placeholder="http://localhost" class="form-control">
                                     <div id="error_app_url" class="text-danger">

                                    </div>
                                </div>

                                <div class="f1-buttons">
                                    <button type="button" class="btn btn-next">Next</button>
                                </div>
                            </fieldset>


                            <fieldset chunk="database">
                                <!--  <div class="form-group">
                                     <label>Database Type</label>
                                    <input type="text" id="DB_CONNECTION" placeholder="mysql" class="form-control" disabled="" value="mysql">
                                </div> -->

                                <div class="form-group">
                                     <label>Database Host</label>
                                    <input type="text" id="DB_HOST" placeholder="127.0.0.1" class="form-control">
                                     <div id="error_db_host" class="text-danger">

                                    </div>
                                </div>
                                 <div class="form-group">
                                     <label>Database Port</label>
                                    <input type="text" id="DB_PORT" placeholder="3306" class="form-control">
                                     <div id="error_db_port" class="text-danger">

                                    </div>
                                </div>
                                <div class="form-group">
                                     <label>Database Name</label>
                                    <input type="text" id="DB_DATABASE" placeholder="invoice" class="form-control">
                                     <div id="error_db_name" class="text-danger">

                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Database Username</label>
                                    <input type="text" id="DB_USERNAME" placeholder="root" class="form-control">
                                     <div id="error_db_user" class="text-danger">

                                    </div>
                                </div>
                                <div class="form-group">
                                     <label>Database Password</label>
                                    <input type="" id="DB_PASSWORD" placeholder="admin" class="form-control">
                                     <div id="error_db_password" class="text-danger">

                                    </div>
                                </div>


                                <div class="f1-buttons">
                                    <button type="button" class="btn btn-previous">Previous</button>
                                    <button type="button" class="btn btn-next">Next</button>
                                </div>
                            </fieldset>


                            <fieldset chunk="account">
                                 <h4>Set up your admin account</h4>
                                 <div class="form-group">
                                    <label class="sr-only" for="f1-username">username</label>
                                    <input type="text" name="" placeholder="Username..." class="form-control" id="USER_NAME">
                                     <div id="error_user_name" class="text-danger">

                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="sr-only" for="f1-email">Email</label>
                                    <input type="text" name="USER_EMAIL" placeholder="Email..." class="f1-email form-control" id="USER_EMAIL">
                                     <div id="error_user_email" class="text-danger">

                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="sr-only" for="f1-password">Password</label>
                                    <input type="password" name="USER_PASSWORD" placeholder="Password..." class="f1-password form-control" id="USER_PASSWORD">
                                     <div id="error_user_password" class="text-danger">

                                    </div>
                                </div>

                                <div class="f1-buttons">
                                    <button type="button" class="btn btn-previous">Previous</button>
                                    <button type="button" class="btn btn-next">Next</button>
                                </div>

                            </fieldset>

                        </form>

                    </div>
                </div>

            </div>
        </div>

        <!-- axios -->
        <script src="<?php echo $actual_link; ?>/assets/js/axios.min.js"></script>

        <!-- Javascript -->
        <script src="<?php echo $actual_link; ?>/assets/js/jquery-1.11.1.min.js"></script>
        <script src="<?php echo $actual_link; ?>/assets/bootstrap/js/bootstrap.min.js"></script>
        <script src="<?php echo $actual_link; ?>/assets/js/jquery.backstretch.min.js"></script>
        <script src="<?php echo $actual_link; ?>/assets/js/retina-1.1.0.min.js"></script>
        <script src="<?php echo $actual_link; ?>/assets/js/scripts.js"></script>

        <!--[if lt IE 10]>
            <script src="<?php echo $actual_link; ?>/assets/js/placeholder.js"></script>
        <![endif]-->

    </body>

</html>
