<?php
session_start();
if (isset($_SESSION['uid']) && $_SESSION['uid'] > 0) {
    header("Location: /index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>ptimer登陆</title>
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="/resource/dist/css/bootstrap.min.css">
    <script type="text/javascript" src="/resource/dist/js/jquery.min.js"></script>
    <script type="text/javascript" src="/resource/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/resource/dist/js/bootstrapValidator.js"></script>
</head>

<body>

<div id="logindiv">
    <div class="container" id="login-block">

        <form class="form-signin pull-right" role="form" id="loginForm" method="post" action="/chk_login.php">
            <h4 class="form-signin-heading">账号密码登陆</h4>
            <br/>
            <div id="result"></div>
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-user"></span>
                    </span>
                    <input type="text" name="username" class="form-control" placeholder="手机号/会员名/邮箱" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-eye-open"></span>
                    </span>
                    <input type="password" name="password" class="form-control" placeholder="请输入密码" required>
                </div>
            </div>
            <button class="btn btn-lg btn-primary btn-block" type="submit" id="submit" name="submit">登陆</button>
        </form>

    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('#loginForm').bootstrapValidator({
            message: '这个值无效!',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                username: {
                    message: '该用户名无效',
                    validators: {
                        notEmpty: {
                            message: '请输入用户名'
                        }
                    }
                },
                password: {
                    validators: {
                        notEmpty: {
                            message: '请输入密码'
                        }
                    }
                },
            }
        }).on("success.form.bv", function (e) {
            e.preventDefault();
            $("#result").html("");
            var $form = $(e.target);
            var bv = $form.data('bootstrapValidator');
            $.post($form.attr('action'), $form.serialize(), function (result) {
                if (result.code != 0) {
                    var str = '<div class="alert alert-danger alert-dismissable">'
                        + '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                        '<strong>' + result.msg + '</strong></div>';
                    $("#result").append(str);
                } else {
                    window.location.href = "/index.php";
                }
            }, 'json');
        });
    });
</script>

</body>
