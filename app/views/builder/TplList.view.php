<?php
if (!defined('IN_WONIU_APP')) {
    exit();
}
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>列表展示</title>
        <script src="res/js/jq/j.js"></script>
        <style type="text/css">
            body{font-size:15px;padding:30px;}
            fieldset{width:820px;margin:0 auto;}
            .hr1{border-bottom:2px solid #A6C61C;margin:0;padding:0;}
            legend{color:green;font-size:16px;font-weight:bold;}
            table{
                border-collapse:collapse;
                margin-top:10px;
            }
            th{
                background-color:#DDD;
            }
            .trhover{background-color:#F5F5F5}
            td,th{padding:5px;text-align:center;}
            input[type=button],input[type=submit]{
                cursor:pointer;
            }
            input[type=text],select{padding:5px;}
            label,input[type=checkbox]{
                cursor:pointer;
            }
            .vals{width:250px;}
            .searchbtn{padding:8px;}
        </style>
        <script>
            var actionName = '<?php echo $_POST['action_name']; ?>';
            $.ajaxSetup({
                error: function(x) {
                    alert('通信出错,代码[' + x.status + ']');
                }
            });
            $(function() {
                $('tr:odd').css({'background-color': '#F5F5F5'});
                $('#choose').click(function() {
                    $('.pk').attr('checked', $(this)[0].checked);
                });
                $('#del_choose').click(function() {
                    var pks = getChoose();
                    if (pks.length) {
                        if (!confirm('确定删除吗?')) {
                            return;
                        }
                        $.ajax({
                            url: '?' + actionName + '.dels'
                                    , type: 'post'
                                    , data: {'pks': pks}
                            , success: function(data) {
                                alert(data.tip);
                                if (data.code == 200) {
                                    location = location;
                                }
                            }
                            , dataType: 'json'
                        });
                    }
                });
                $('.searchbtn').click(function() {
                    var where = getWhere();
                    if (where) {
                        where = encodeURIComponent(where);
                    }
                    var url;
                    if (location.href.indexOf('?') === -1) {
                        url = '?query=' + where;
                    } else {
                        url = location.search.substr(1);
                        var urla = url.split('&');
                        for (var i = 0; i < urla.length; i++) {
                            if (urla[i].indexOf('query=') === 0) {
                                urla.splice(i, 1);
                                break;
                            }
                        }
                        for (var i = 0; i < urla.length; i++) {
                            if (urla[i].indexOf('&{ echo $pageKey}&=') === 0) {
                                urla.splice(i, 1);
                                break;
                            }
                        }
                        url = location.href.substring(0, location.href.indexOf('?')) + '?' + urla.join('&') + '&query=' + where;
                    }

                    location = url;
                });
            });
            function getWhere() {
                var conds = [];
                $('.where').each(function() {
                    var $tr = $(this);
                    var andor = $tr.find('.andor');
                    var col = '`' + $tr.find('.col').val() + '`';
                    var comp = $tr.find('.comp').val();
                    var val = $tr.find('.vals').val();
                    var where = '';
                    if (andor[0] && conds.length >= 1) {
                        where += ' ' + andor.val() + ' ';
                    }
                    var cond = parse(col, comp, val);
                    if (cond) {
                        where += cond;
                        conds.push(where);
                    }
                });
                if (conds.length && $('.by').val()) {
                    conds.push(' order by ' + $('.by').val() + ' ' + $('.order').val());
                }
                conds = conds.join('');
                return conds;
            }
            function getChoose() {
                var pks = [];
                $('.pk:checked').each(function() {
                    pks.push($(this).val());
                });
                return pks;
            }
            function parse(col, comp, val) {
                if (!val) {
                    return '';
                }
                switch (comp) {
                    case '>=,<':
                    case '>,<=':
                    case '>=,<=':
                        val = val.split(',');
                        comp = comp.split(',');
                        return '(' + col + comp[0] + val[0] + ' and ' + col + comp[1] + val[1] + ')';
                        break;
                    case '%like%':
                        return col + " like '%" + val + "%'";
                        break;
                    case 'like%':
                        return col + " like '" + val + "%'";
                        break;
                    case '%like':
                        return col + " like '%" + val + "'";
                        break;
                    case 'in':
                        val = val.split(',');
                        if (isNaN(val[0])) {
                            for (var i = 0; i < val.length; i++) {
                                val[i] = "'" + val[i] + "'";
                            }
                        }
                        return col + ' in(' + val.join(',') + ')';
                        break;
                    default:
                        if (isNaN(val)) {
                            return col + comp + "'" + val + "'";
                        } else {
                            return col + comp + val;
                        }
                        break;
                }
            }
        </script></head>
    <body>
        <fieldset>
            <legend>列表页&nbsp;&nbsp;[<a href="?<?php echo $_POST['action_name']; ?>.add">添加</a>]</legend>
            <hr class="hr1"/>
            &{ if($openSearch){ }&
            <p>搜索选项：
                <?php
                $total = count($search);
                $i = 0;
                $orderby = '';
                foreach ($search as $key => $value) {
                    $orderby.='<option value="' . $key . '">' . $value . '</option>';
                }
                $orderby = '<select class="by"><option value=""></option>' . $orderby . '</select><select class="order"><option value="desc">降序</option><option value="asc">升序</option></select>';
                foreach ($search as $key => $value) {
                    $orderby.='<option value="' . $key . '">' . $value . '</option>';
                    echo ($i == 0 ? '<table><tr class="where"><td><input class="searchbtn" type="button" value="　搜　索　"/>' : '<tr class="where"><td><select class="andor">
            <option value="and">且</option>
            <option value="or">或</option>
            </select>') . '</td><td><input class="col" type="hidden" value="' . $key . '">' . $value . ':</td><td>
            <select class="comp">
            <option value="=">等于</option>
            <option value="%like%">(中间)包含</option>
            <option value="like%">(开头)包含</option>
            <option value="%like">(结尾)包含</option>
            <option value=">">大于</option>
            <option value="<">小于</option>
            <option value=">=">大于等于</option>
            <option value="<=">小于等于</option>
            <option value=">=,<">=&lt;区间&lt;</option>
            <option value=">,<=">&lt;区间&lt;=</option>
            <option value=">=,<=">=&lt;区间&lt;=</option>
            <option value="in">IN(枚举)</option>
            </select></td><td>
            <input class="vals" type="text" /></td></tr>
            ';
                    if ($i == $total - 1) {
                        echo '<tr><td>排序：</td><td style="text-align:left;" colspan="3">' . $orderby . '<input class="searchbtn" type="button" value="　搜　索　"/></td></tr></table>';
                    }
                    $i++;
                }
                ?>
            </p>
             &{}}&
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr><?php foreach ($rows as $row) { ?><th><?php echo $row['th']; ?></th><?php } ?><th>操作</th></tr>
                &{foreach($items as $row){}&
                <tr><?php foreach ($rows as $key => $row) { ?><?php if ($key == 0) { ?><td><label><input type="checkbox" value="&{ echo $row['<?php echo $row['col']; ?>']}&" class="pk"/>&{ echo $row['<?php echo $row['col']; ?>']}&</label></td><?php } else { ?><td>&{ echo $row['<?php echo $row['col']; ?>']}&</td><?php } ?><?php } ?>
                    <td>[<a href="?<?php echo $_POST['action_name']; ?>.modify&<?php echo $_POST['pk']; ?>=&{ echo $row[$pk]}&">修改</a>]</td>
                </tr>
                &{}//end foreach}&
            </table>
            <p><label><input type="checkbox" id="choose"/>全选/全不选</label><input type="button" id="del_choose" value="删除所选"></p>
            <p>&{ echo $page;}&</p>
        </fieldset>
    </body>
</html>