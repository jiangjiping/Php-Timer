<?php
defined("APP_PATH") || exit("Access Deny!");
?>
<html>
<head>
    <title>ptimer管理中心</title>
    <link href="resource/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="resource/dist/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
    <style type="text/css">
        body {
            padding-top: 38px;
            font-size: 14px;
            font-family: Microsoft YaHei, '宋体', Tahoma, Helvetica, Arial;
        }

        .tab-content > .tab-pane {
            padding: 20px;
            background: #F5F5F5;
        }

        .logo h1 {
            margin-top: 0px;
            color: #fff;
            font-size: 45px;
            font-family: "Helvetica Neue", Helvetica, Arial, "Hiragino Sans GB", "Hiragino Sans GB W3", "Microsoft YaHei UI", "Microsoft YaHei", "WenQuanYi Micro Hei", sans-serif;
        }

        .container {
            width: 100%;
        }

        #crontab-list td {
            vertical-align: middle;
        }

        h1, h2, h3 {
            margin: 0px;
        }

        .mynav {
            padding-bottom: 8px;
            color: #fff;
            text-shadow: 0 1px 3px rgba(0, 0, 0, .4), 0 0 30px rgba(0, 0, 0, .075);
            background: -moz-linear-gradient(45deg, #320031 0, #6d3353 100%);
            background: -webkit-gradient(linear, left bottom, right top, color-stop(0%, #020031), color-stop(100%, #6d3353));
            background: -webkit-linear-gradient(45deg, #020031 0, #6d3353 100%);
            background: -o-linear-gradient(45deg, #020031 0, #6d3353 100%);
            background: -ms-linear-gradient(45deg, #020031 0, #6d3353 100%);
            background: linear-gradient(45deg, #020031 0, #6d3353 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#320031', endColorstr='#6d3353', GradientType=1);
            -webkit-box-shadow: inset 0 3px 7px rgba(0, 0, 0, .2), inset 0 -3px 7px rgba(0, 0, 0, .2);
            -moz-box-shadow: inset 0 3px 7px rgba(0, 0, 0, .2), inset 0 -3px 7px rgba(0, 0, 0, .2);
            box-shadow: inset 0 3px 7px rgba(0, 0, 0, .2), inset 0 -3px 7px rgba(0, 0, 0, .2);
        }

        th {
            background-color: #f5f5f5;
        }

        th, td, td div {
            text-align: center;
            font-size: 14px;
            vertical-align: middle;
            font-family: Monaco, "Helvetica Neue", Helvetica, Arial, "Hiragino Sans GB", "Hiragino Sans GB W3", "Microsoft YaHei UI", "Microsoft YaHei", "WenQuanYi Micro Hei", sans-serif;
        }

        td div {
            margin-left: auto;
            margin-right: auto;
        }

        .center {
            margin: 0 auto;
        }

        .p10 {
            padding: 10px;
        }

        .mt50 {
            margin-top: 50px;
        }

        .mt110 {
            margin-top: 110px;
        }

        .mt10 {
            margin-top: 10px;
        }

        .mt20 {
            margin-top: 20px;
        }

        .mt30 {
            margin-top: 30px;
        }

        .h34 {
            height: 34px;
        }
    </style>
    <script type="text/javascript" src="resource/dist/js/jquery.min.js"></script>
    <script type="text/javascript" src="resource/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="resource/dist/js/bootstrap-button.js"></script>
    <script type="text/javascript" src="resource/dist/js/bootstrap-modal.js"></script>
    <script type="text/javascript" src="resource/dist/js/vue.min.js"></script>
    <script type="text/javascript" src="resource/dist/js/bootstrap-datetimepicker.min.js"></script>
</head>
<body>
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                            class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">新增任务</h4>
            </div>
            <div class="modal-body">
                <ul id="myTab" class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#everyday" id="everyday-tab" role="tab"
                                                              data-toggle="tab"
                                                              aria-controls="everyday" aria-expanded="true">每天</a></li>
                    <li role="presentation" class=""><a href="#everyweek" role="tab" id="everyweek-tab"
                                                        data-toggle="tab"
                                                        aria-controls="everyweek" aria-expanded="false">每周</a></li>
                    <li role="presentation" class=""><a href="#interval" role="tab" id="interval-tab" data-toggle="tab"
                                                        aria-controls="interval" aria-expanded="false">周期</a></li>
                    <li role="presentation" class=""><a href="#once" role="tab" id="once-tab" data-toggle="tab"
                                                        aria-controls="once" aria-expanded="false">一次</a></li>
                </ul>
                <div id="myTabContent" class="tab-content">
                    <div role="tabpanel" class="tab-pane fade active in" id="everyday" aria-labelledby="everyday-tab">
                        <form action="">
                            <input type="hidden" name="task_type" value="<?php echo \Ptimer\TaskType::EVERY_DAY ?>">
                            <div class="input-group">
                                <span class="input-group-addon">任务标题</span>
                                <input type="text" class="form-control" name="title"
                                       placeholder="请输入任务标题">
                            </div>
                            <div class="input-group mt10">
                                <span class="input-group-addon">执行命令</span>
                                <input type="text" class="form-control" name="command"
                                       placeholder="for example: /usr/local/php/bin/php boot.php">
                            </div>
                            <div class="input-group mt10">
                                <span class="input-group-addon">触发时间</span>
                                <input size="19" type="text" name="trigger_time" class="form-control form_datetime_1"
                                       placeholder="请选择执行时间">
                            </div>
                        </form>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="everyweek" aria-labelledby="everyweek-tab">
                        <form action="">
                            <input type="hidden" name="task_type" value="<?php echo \Ptimer\TaskType::EVERY_WEEK ?>">
                            <div class="input-group">
                                <span class="input-group-addon">任务标题</span>
                                <input type="text" class="form-control" name="title"
                                       placeholder="请输入任务标题">
                            </div>
                            <div class="input-group mt10">
                                <span class="input-group-addon">执行命令</span>
                                <input type="text" class="form-control" name="command"
                                       placeholder="for example: /usr/local/php/bin/php boot.php">
                            </div>
                            <div class="input-group mt10">
                                <span class="input-group-addon">选择周几</span>
                                <select class="form-control" name="weekday">
                                    <option value="1">周一</option>
                                    <option value="2">周二</option>
                                    <option value="3">周三</option>
                                    <option value="4">周四</option>
                                    <option value="5">周五</option>
                                    <option value="6">周六</option>
                                    <option value="0">周日</option>
                                </select>
                            </div>
                            <div class="input-group mt10">
                                <span class="input-group-addon">触发时间</span>
                                <input size="19" type="text" name="trigger_time" class="form-control form_datetime_2"
                                       placeholder="请选择执行时间">
                            </div>
                        </form>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="interval" aria-labelledby="interval-tab">
                        <form action="">
                            <input type="hidden" name="task_type" value="<?php echo \Ptimer\TaskType::INTERVAL ?>">
                            <div class="input-group">
                                <span class="input-group-addon">任务标题</span>
                                <input type="text" class="form-control" name="title"
                                       placeholder="请输入任务标题">
                            </div>
                            <div class="input-group mt10">
                                <span class="input-group-addon">执行命令</span>
                                <input type="text" class="form-control" name="command"
                                       placeholder="for example: /usr/local/php/bin/php boot.php">
                            </div>
                            <div class="input-group mt10">
                                <span class="input-group-addon">间隔秒数</span>
                                <input type="text" name="interval" class="form-control"
                                       placeholder="请输入周期执行间隔的秒数">
                            </div>
                        </form>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="once" aria-labelledby="once-tab">
                        <form action="">
                            <input type="hidden" name="task_type" value="<?php echo \Ptimer\TaskType::ONCE ?>">
                            <div class="input-group">
                                <span class="input-group-addon">任务标题</span>
                                <input type="text" class="form-control" name="title"
                                       placeholder="请输入任务标题">
                            </div>
                            <div class="input-group mt10">
                                <span class="input-group-addon">执行命令</span>
                                <input type="text" class="form-control" name="command"
                                       placeholder="for example: /usr/local/php/bin/php boot.php">
                            </div>
                            <div class="input-group mt10">
                                <span class="input-group-addon">触发时间</span>
                                <input size="19" type="text" name="trigger_time" class="form-control form_datetime_3"
                                       placeholder="请选择执行一次的触发时间">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="save" type="button" class="btn btn-primary" data-loading-text="处理中...">保存</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->

<div class="navbar-default navbar-fixed-top mynav">
    <div style="background: #000;">
        <div class="container center row">
            <div class="col-md-4" style="padding-left: 0px; padding-top: 5px;">
                <h5 style="color: white; padding-left: 20px;">
                    <?php if ($ptimer_status) { ?>
                    欢迎使用ptimer! <span style="color: #5cb85c;font-weight: bold;padding-left: 5px;">[Ptimer正常运行]
                        <?php } else { ?>
                            <span style="color: indianred;font-weight: bold;padding-left: 5px;">【 警告! 无法连接Ptimer, 请检查服务是否启动! 】</span>
                        <?php } ?>
                </h5>
            </div>
            <div class="col-md-8 p10" style="text-align: right">
                <button type="button" class="btn btn-danger" id="logout" style="padding: 3px 10px;"><span
                            class="glyphicon glyphicon-off"
                            aria-hidden="true"></span> 注销
                </button>
            </div>
        </div>
    </div>
    <div class="container row center">
        <div class="logo navbar-brand col-md-2">
            <h1>Ptimer</h1>
        </div>
        <div class="col-md-4" style="padding-top: 6px;">
            <button id="add" class="btn btn-success mt20 p10" data-toggle="modal" type="button">
                <span class="glyphicon glyphicon-plus-sign"></span> 新增任务
            </button>
            <button id="flush" data-loading-text="加载中,请耐心等待..." style="margin-left: 7px;"
                    class="btn btn-primary mt20 p10" type="button">
                <span class="glyphicon glyphicon-refresh"></span> 刷新任务列表
            </button>
        </div>
        <div class="col-md-4 col-md-offset-2 mt30">
            <form method="post" action="">
                <div class="input-group ssearch">
                    <input type="text" name="keywords" class="form-control" placeholder="请输入关键词"
                           style="font-size: 13px;">
                    <span class="input-group-btn">
                                    <button class="btn btn-default h34" type="submit">
                                        <span class="glyphicon glyphicon-search"></span></button>
                                </span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="container mt110 center" style="min-height: 600px;">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><span class="glyphicon glyphicon-time"></span> 任务列表</h3>
        </div>
        <div class="panel-body">
            <div id="crontab-list">
                <table class="table table-hover table-bordered">
                    <thead>
                    <tr>
                        <th>标题</th>
                        <th>命令</th>
                        <th>执行类型 <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip"
                                       data-placement="right"
                                       title="执行类型为[ 一次 ]时, 当任务执行完成会从任务列表移除, 可在日志查看执行历史" aria-hidden="true">
                            </span>
                        </th>
                        <th>上一次执行时间</th>
                        <th>触发时间 <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip"
                                       data-placement="right"
                                       title="只有一次性任务才显示" aria-hidden="true">
                            </span></th>
                        <th>执行次数</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody id="clist">
                    <template v-for="item in items">
                        <tr>
                            <td>
                                <div style="word-break: break-all;word-wrap: break-word; width: 159px;">
                                    <input type="hidden" name="get_task_id" value="{{item.id}}">
                                    {{item.title}}
                                </div>
                            </td>
                            <td>
                                <div style="word-break: break-all;word-wrap: break-word; width: 750px;text-decoration: underline;color: green;">
                                    {{item.command}}
                                </div>
                            </td>
                            <td>
                                <div v-if="item.task_type==<?php echo \Ptimer\TaskType::ONCE ?>">一次</div>
                                <div v-else>
                                    <div v-if="item.task_type==<?php echo \Ptimer\TaskType::INTERVAL ?>">
                                        每隔 <span class="badge">{{item.interval}}</span> 秒执行
                                    </div>
                                    <div v-else>
                                        <div v-if="item.task_type==<?php echo \Ptimer\TaskType::EVERY_DAY ?>">
                                            每天 <span class="badge">{{item.HisTime}}</span> 执行
                                        </div>
                                        <div v-else>
                                            每周{{item.weekday}} <span class="badge">{{item.HisTime}}</span> 执行
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div v-if="item.last_exec_time">{{item.last_exec_time}}</div>
                                <div v-else class="text-muted">——</div>
                            </td>
                            <td>
                                <div v-if="item.task_type==<?php echo \Ptimer\TaskType::ONCE ?>">{{item.triggerTime}}
                                </div>
                                <div v-else class="text-muted">——</div>
                            </td>
                            <td>
                                <div>{{item.exec_num}}</div>
                            </td>
                            <td>
                                <a href="javascript:;" onclick="return customerConfirm($(this))" class="del"
                                   rel="{{item.id}}">
                                    <span class="glyphicon glyphicon-minus-sign text-danger"></span>
                                </a>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<div class="panel-footer text-center navbar-fixed-bottom">
    <h6>
        <small class="p10">&copy;2016, Power By 江济平(randy)</small>
    </h6>
</div>
</body>
<script type="text/javascript">
    var bind;
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
    $(".form_datetime_1,.form_datetime_2").datetimepicker({
        language: 'zh-CN',
        format: 'hh:ii:ss'
    });
    $(".form_datetime_3").datetimepicker({
        language: 'zh-CN',
        format: 'yyyy-mm-dd hh:ii:ss'
    });
    function customerConfirm(a) {
        var result = confirm('确定删除该项吗?');
        var lock = false;
        if (result) {
            a.parents('tr').css("opacity", 0.5);
            //删除计划任务
            if (lock) {
                return;
            }
            lock = true;
            $.ajax({
                type: 'POST',
                url: '/index.php',
                data: {
                    key: 'remove_get',
                    timer_id: a.attr("rel")
                },
                dataType: 'json',
                success: function (data) {
                    if (bind) {
                        bind.items = data.items;
                    } else {
                        bind = new Vue({
                            el: '#clist',
                            data: {
                                items: data.items
                            }
                        });
                    }
                    lock = false;
                }
            });
        }
    }
    function form_reset() {
        $("input[type=text][name=command]").val('');
        $("input[type=text][name=interval]").val('');
        $("input[type=text][name=trigger_time]").val('');
        $('#myModal').modal('hide');
    }
    function flush() {
        var $btn = $("#flush").button('loading');
        $.ajax({
            type: 'POST',
            url: '/index.php',
            data: {
                key: 'save_get'
            },
            dataType: 'json',
            success: function (data) {
                if (bind) {
                    bind.items = data.items;
                } else {
                    bind = new Vue({
                        el: '#clist',
                        data: {
                            items: data.items
                        }
                    });
                }
                $btn.button('reset');

            }
        });
    }
    $(document).ready(function () {
        $('#myTab a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
        $("#logout").click(function (e) {
            e.preventDefault();
            $.post('/chk_login.php', {
                'logout': 1
            }, function (result) {
                window.location.href = '/';
            });
        });
        $("#flush").on('click', function () {
            flush();
        });
        $("#add").click(function () {
            $('#myModal').modal();
        });
        $("button#save").on('click', function () {
            var btn = $("button#save").button('loading');
            $.ajax({
                type: 'POST',
                url: '/index.php',
                data: {
                    key: 'add_get',
                    title: $("#myTabContent .active input[type=text][name=title]").val(),
                    command: $("#myTabContent .active input[type=text][name=command]").val(),
                    interval: $("#myTabContent .active input[type=text][name=interval]").val(),
                    trigger_time: $("#myTabContent .active input[type=text][name=trigger_time]").val(),
                    task_type: $("#myTabContent .active input[type=hidden][name=task_type]").val(),
                    weekday: $("#myTabContent .active select[name=weekday]").val()
                },
                dataType: 'json',
                success: function (data) {
                    if (data.code == 0) {
                        bind.items = data.items;
                        btn.button('reset');
                        form_reset();
                    } else if (data.code == 1) {
                        alert(data.msg);
                        btn.button('reset');
                    }

                },
                error: function (e) {
                    console.log(e);
                    form_reset();
                }
            });
            flush();
        });
        flush();
    });
</script>
</html>