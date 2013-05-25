<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css">
            body{font-size:15px;padding:30px;}
            fieldset{width:820px;margin:0 auto;}
            fieldset div{margin-top:5px;}
            .hr1{border-bottom:2px solid #A6C61C;margin:0;padding:0;}
            table{
                border-collapse:collapse;
            }
            td{padding:5px;}
            input[type=button],input[type=submit]{
                cursor:pointer;
                padding:2xp 5px;
            }
        </style>
        <script src="res/js/jq/j.js"></script>
        <script src="res/js/kd/kindeditor-min.js"></script>
        <script src="res/js/jq/form.js"></script>
        <script src="res/js/jq/ajaxqueue.js"></script>
        <link rel="stylesheet" type="text/css" href="res/js/kd/plugins/code/prettify.css" />
        <script src="res/js/kd/plugins/code/prettify.js"></script>
        <script>
            var found = false;
            var _editor = [];
            var actionName = 'hiTask';
            KindEditor.ready(function(K) {
                $('textarea.textareahtml').each(function() {
                    var id = $(this).attr('id');
                    _editor.push(
                            K.create('#' + id, {//指定textarea
                        uploadJson: '?' + actionName + '.uploadJson',
                        fileManagerJson: '?' + actionName + '.fileManagerJson',
                        cssPath: 'res/js/kd/plugins/code/prettify.css',
                        emoticonsPath: 'resimage/emoticons/images/',
                        allowFileManager: true
                    })
                            );
                });
                prettyPrint();//执行代码高亮
            });
            $(function() {
                $('#cform').submit(function() {
                    $('textarea.textareahtml').each(function(i) {
                        try {
                            $(this).val(_editor[i].html())
                        } catch (e) {
                        }
                        ;
                    });
                    $(this).ajaxSubmit({
                        dataType: 'json'
                                , success: function(data, status_t, xhr) {
                            showInfoWindow(data.tip);
                            if (data.code == 200) {
                                $('#cform').clearForm();
                                for (i = 0; i < _editor.length; i++) {
                                    _editor[i].html('');
                                }
                            }
                        }
                        , error: function(xhr) {
                            showInfoWindow('通信出错,代码[' + xhr.status + ']');
                        }
                        , beforeSubmit: function() {
                             if(found){
                                 showInfoWindow('正在处理...');
                             }
                        }
                    });
                    return false;
                });
                $('#cboxSaveRomote').click(function() {
                    // alert();return;
                    if ($(this).attr('checked')) {
                        for (i = 0; i < _editor.length; i++) {
                            saveRomoteImgs(_editor[i], '?' + actionName + '.saveRemoteImage');
                        }
                    }
                });
            });
            //###########自动保存相关函数#######################
            function saveRomoteImgs(KE, scriptUrl) {
                var CheckboxId = "cboxSaveRomote";
                var content = KE.html();//得到html代码
                var matchArray1, matchArray2 = [], matchArray3 = [], tmp_matchArray1 = [];
                errorCounter = 0;
                var hostname = location.hostname;
                var reg = /<img[^>]+src=['"](http:\/\/[^'"]*)['"]/ig;
                var subreg = /src=['"]([^'"]*)['"]/ig;
                matchArray1 = content.match(reg);
                //没有远程图片直接提交表单
                if (!matchArray1) {
                    $("#" + CheckboxId)[0].checked = false;
                    showInfoWindow("<p>没有远程图片,请手动提交...</p>");
                    return;
                }
                //只保留非本域名的图片地址
                for (j = 0; j < matchArray1.length; j++) {
                    if (matchArray1[j].indexOf("http://" + hostname) == -1) {
                        tmp_matchArray1.push(matchArray1[j]);
                    }
                }
                matchArray1 = tmp_matchArray1;
                for (j = 0; j < matchArray1.length; j++) {
                    submatch = matchArray1[j].match(subreg);
                    if (submatch && submatch.length > 0) {
                        var url = submatch[0].replace(/('|"|src=)/g, '');
                        matchArray2.push(url);
                        matchArray3.push(url);
                        console.log(url);
                    }
                }
                //没有子匹配直接提交表单
                if (!matchArray2 || !matchArray2.length) {
                    $("#" + CheckboxId)[0].checked = false;
                    showInfoWindow("<p>没有远程图片,请手动提交...</p>");
                    return;
                }

                //队列开始
                var newQueue = $.AM.createQueue('queue');
                showInfoWindow("开始获取远程图片...");
                for (h = matchArray2.length - 1; h >= 0; h--) {
                    newQueue.offer({
                        url: scriptUrl
                                , type: "POST"
                                , data: {imgurl: matchArray2[h]}
                        , complete: function(x) {
                            var result = x.responseText;
                            try {
                                result = eval("(" + result + ")");
                            } catch (e) {
                                result = {};
                            }
                            var rurl = matchArray2.pop();//原始url
                            var qpr = matchArray3.length - matchArray2.length;
                            //内容替换处理
                            if (!result.error) {
                                var surl = result.url;//保存后的url
                                var content = KE.html();
                                content = content.replace('"' + rurl + '"', '"' + surl + '"');
                                content = content.replace("'" + rurl + "'", '"' + surl + '"');
                                KE.html(content);
                            } else {
                                showInfoWindow(result.message);
                                errorCounter++;
                                if (result.error == 2) {
                                    $.AM.destroyQueue('queue');
                                    return;
                                }
                            }
                            //进度提示
                            if (!matchArray2.length) {
                                $("#" + CheckboxId)[0].checked = false;
                                showInfoWindow("远程图片保存进度[" + qpr + "/" + matchArray3.length + "],失败[" + errorCounter + "]个,<b>保存完毕!<p> 请手动提交...</p>");
                            } else {
                                showInfoWindow("远程图片保存进度[" + qpr + "/" + matchArray3.length + "],失败[" + errorCounter + "]个.");
                            }
                        }
                    });
                }
            }
            var dialog;
            function showInfoWindow(msg) {
                if (!found) {
                    alert(msg);
                }
                return;
                try {
                    dialog.remove();
                } catch (e) {
                }
                dialog = KindEditor.dialog({
                    width: 400,
                    height: 200,
                    title: '提示信息',
                    body: '<div style="margin:10px;text-align:center;">' + msg + '</div>',
                    closeBtn: {
                        name: '关闭',
                        click: function(e) {
                            dialog.remove();
                        }
                    }
                });
            }
            //###########自动保存相关函数结束#######################
        </script>
    </head>
    <body><form method="post" action="?hiTask.create" id="cform">
            <fieldset><legend><h3>添加界面</h3></legend>
                <hr class="hr1"/>
                <table>
                                                                        <tr><td>文章标题</td><td><input type="text" name="title"/></td></tr>
                                                                                                <tr><td>文章内容</td><td><textarea style="width:400px;height:120px;" type="text" name="content"></textarea></td></tr>
                                                                                                <tr><td>添加时间</td><td><input type="text" name="time"/></td></tr>
                                                                                                <tr><td>状态</td><td><input type="text" name="status"/></td></tr>
                                                                                                <tr><td>添加者</td><td><input type="text" name="user"/></td></tr>
                                                                                                <tr><td>类型</td><td><input type="text" name="type"/></td></tr>
                                                                                    <tr><td>&nbsp;</td><td><input type="submit" value="提交"/></td></tr>
                </table>
            </fieldset>
        </form>
    </body>
</html>