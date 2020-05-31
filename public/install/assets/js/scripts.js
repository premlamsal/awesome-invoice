function scroll_to_class(element_class, removed_height) {
    var scroll_to = $(element_class).offset().top - removed_height;
    if ($(window).scrollTop() != scroll_to) {
        $('html, body').stop().animate({
            scrollTop: scroll_to
        }, 0);
    }
}

function bar_progress(progress_line_object, direction) {
    var number_of_steps = progress_line_object.data('number-of-steps');
    var now_value = progress_line_object.data('now-value');
    var new_value = 0;
    if (direction == 'right') {
        new_value = now_value + (100 / number_of_steps);
    } else if (direction == 'left') {
        new_value = now_value - (100 / number_of_steps);
    }
    progress_line_object.attr('style', 'width: ' + new_value + '%;').data('now-value', new_value);
}

function moveToNext(parent_fieldset, current_active_step, progress_line) {
    parent_fieldset.fadeOut(400, function() {
        // change icons
        current_active_step.removeClass('active').addClass('activated').next().addClass('active');
        // progress bar
        bar_progress(progress_line, 'right');
        // show next step
        $(this).next().fadeIn();
        // scroll window to beginning of the form
        scroll_to_class($('.f1'), 20);
    });
}

function goPrevious(parent_fieldset, current_active_step, progress_line) {
    // navigation steps / progress steps
    parent_fieldset.fadeOut(400, function() {
        // change icons
        current_active_step.removeClass('active').prev().removeClass('activated').addClass('active');
        // progress bar
        bar_progress(progress_line, 'left');
        // show previous step
        $(this).prev().fadeIn();
        // scroll window to beginning of the form
        scroll_to_class($('.f1'), 20);
    });
}

function clearMsg() {
    $("#responseMsg").text('');
    $("#responseMsg").text('');
}
jQuery(document).ready(function() {
    var formData = new FormData;
    /*
        Fullscreen background
    */
    // $.backstretch("#101010");
    // $('#top-navbar-1').on('shown.bs.collapse', function() {
    //     $.backstretch("resize");
    // });
    // $('#top-navbar-1').on('hidden.bs.collapse', function() {
    //     $.backstretch("resize");
    // });
    /*
        Form
    */
    $('.f1 fieldset:first').fadeIn('slow');
    $('.f1 input[type="text"], .f1 input[type="password"], .f1 textarea').on('focus', function() {
        $(this).removeClass('input-error');
    });
    // next step
    $('.f1 .btn-next').on('click', function() {
        var parent_fieldset = $(this).parents('fieldset');
        var next_step = true;
        var verified = false;
        var need_to_verify = false;
        var chukedData = '';
        //custom code by prem
        // navigation steps / progress steps
        var current_active_step = $(this).parents('.f1').find('.f1-step.active');
        var progress_line = $(this).parents('.f1').find('.f1-progress-line');
        // fields validation
        parent_fieldset.find('input[type="text"], input[type="password"], textarea').each(function() {
            if ($(this).val() == "") {
                $(this).addClass('input-error');
                next_step = false;
            } else {
                $(this).removeClass('input-error');
            }
        });
        //check for env varible and make need_to_verify=true;
        chukedData = parent_fieldset.attr('chunk');
        // console.log(chukedData);//get
        if (chukedData === 'app') {
            need_to_verify = false;
            formData.delete("_METHOD");
            formData.delete("APP_NAME");
            formData.delete("APP_ENV");
            formData.delete("APP_URL");
            formData.append("APP_NAME", $("#APP_NAME").val()); //application name
            formData.append("APP_ENV", $("#APP_ENV").val()); //local
            formData.append("APP_URL", $("#APP_URL").val()); //http://localhost
            // console.log($("#APP_NAME").val());
            verified = true;
            if (next_step && verified) {
                moveToNext(parent_fieldset, current_active_step, progress_line)
            }
        } else if (chukedData === 'database') {
            if (next_step) {
                need_to_verify = true;
                // console.log(need_to_verify);
                // removes previous present data
                formData.delete("_METHOD");
                // formData.append("DB_CONNECTION", $("#DB_CONNECTION").val()); //should be mysql
                formData.delete("DB_HOST");
                formData.delete("DB_PORT");
                formData.delete("DB_DATABASE");
                formData.delete("DB_USERNAME");
                formData.delete("DB_PASSWORD");
                formData.append("_METHOD", "POST"); //127.0.0.1
                formData.append("DB_HOST", $("#DB_HOST").val()); //127.0.0.1
                formData.append("DB_PORT", $("#DB_PORT").val()); //port 3306
                formData.append("DB_DATABASE", $("#DB_DATABASE").val()); // myDB
                formData.append("DB_USERNAME", $("#DB_USERNAME").val()); //should be mysql username
                formData.append("DB_PASSWORD", $("#DB_PASSWORD").val()); //should be mysql password
                axios.post('./back/appDbCheck.php', formData).then(response => {
                    if (response.data.status === "success") {
                        // verified = true;
                        // fields validation
                        if (next_step) {
                            $("#responseMsg").html("<p class='text-success'>Migration started</p>");
                            axios.get('./back/migration.php').then(function(response) {
                                if (response.data.msg.results === 0) {
                                    $("#responseMsg").html("<p class='text-success'>" + response.data.msg.migrate[0] + "</p>");
                                    moveToNext(parent_fieldset, current_active_step, progress_line)
                                } else if (response.data.msg.results === 1) {
                                    $("#responseMsg").html("<p class='text-danger'>Internal error occured while creating migration.</p>");
                                }
                            }).catch(function(error) {
                                $("#responseMsg").html("<p class='text-danger'>Migration failed</p>");
                            });
                        }
                    }
                }).catch(error => {
                    if (error.response.status == 500) {
                        if (error.response.data.error && error.response.data.error.APP_URL && error.response.data.error.APP_ENV && error.response.data.error.APP_NAME) {
                            goPrevious(parent_fieldset, current_active_step, progress_line);
                            $("#error_app_name").text('');
                            $("#error_app_url").text('');
                            $("#error_app_env").text('');
                            $("#error_app_name").text(error.response.data.error.APP_NAME);
                            $("#error_app_url").text(error.response.data.error.APP_URL);
                            $("#error_app_env").text(error.response.data.error.APP_ENV);
                        }
                        if (error.response.data.error) {
                            $("#error_db_host").text('');
                            $("#error_db_port").text('');
                            $("#error_db_name").text('');
                            $("#error_db_user").text('');
                            $("#error_db_password").text('');
                            $("#error_db_host").text(error.response.data.error.DB_HOST);
                            $("#error_db_port").text(error.response.data.error.DB_PORT);
                            $("#error_db_name").text(error.response.data.error.DB_DATABASE);
                            $("#error_db_user").text(error.response.data.error.DB_USERNAME);
                            $("#error_db_password").text(error.response.data.error.DB_PASSWORD);
                        }
                        $("#responseMsg").html("<p class='text-danger'>" + error.response.data.msg + "</p>");
                    } else {
                        $("#responseMsg").html("<p class='text-danger'>Unknown eror occured while creating appDbCheck</p>");
                    }
                });
            }
        } else if (chukedData === 'account') {
            if (next_step) {
                formData.delete("_METHOD");
                formData.delete("USER_NAME");
                formData.delete("USER_NAME");
                formData.delete("USER_PASSWORD");
                let newForm = new FormData();
                newForm.append("_METHOD", "POST");
                newForm.append("USER_NAME", $("#USER_NAME").val());
                newForm.append("USER_EMAIL", $("#USER_EMAIL").val());
                newForm.append("USER_PASSWORD", $("#USER_PASSWORD").val());
                //creating account for the admin
                axios.post('./back/register.php', newForm).then(response => {
                    if (response.data.status === "success") {
                        getUrl = response.data.url;
                        $("#responseMsg").html("<p class='text-success'>Application Installed sucessfully.</p>");
                        $("#dynamicBtn").html("<a href=" + getUrl + " class='btn btn-success'>Go to App</a>");
                        moveToNext(parent_fieldset, current_active_step, progress_line)
                    }
                }).catch(error => {
                    let getError = error.response.data
                    // console.log(error.response.data.msg.USER_NAME);//get error text of username
                    if (error.response.status == 500) {
                        if (getError.error) {
                            let errorData = getError.error;
                            $("#error_user_name").text('');
                            $("#error_user_email").text('');
                            $("#error_user_password").text('');
                            $("#error_user_name").text(errorData.USER_NAME);
                            $("#error_user_email").text(errorData.USER_EMAIL);
                            $("#error_user_password").text(errorData.USER_PASSWORD);
                        }
                        $("#responseMsg").html("<p class='text-danger'>" + getError.msg + "</p>");
                        console.log(getError)
                    } else {
                        $("#responseMsg").html("<p class='text-danger'>Unknown error occured..</p>");

                    }
                });
            }
        }
        //check for env variable
        //custome code by prem
    });
    // previous step
    $('.f1 .btn-previous').on('click', function() {
        var parent_fieldset = $(this).parents('fieldset');
        //custom code by prem
        // navigation steps / progress steps
        var current_active_step = $(this).parents('.f1').find('.f1-step.active');
        var progress_line = $(this).parents('.f1').find('.f1-progress-line');
        // fields validation
        goPrevious(parent_fieldset, current_active_step, progress_line);
    });
    // submit
    $('.f1').on('submit', function(e) {
        // fields validation
        $(this).find('input[type="text"], input[type="password"], textarea').each(function() {
            if ($(this).val() == "") {
                e.preventDefault();
                $(this).addClass('input-error');
            } else {
                $(this).removeClass('input-error');
            }
        });
        // fields validation
    });
});