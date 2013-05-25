<?php

class ccc extends WoniuController {

    private  //保存图片和文件的根目录绝对路径，可以指定绝对路径，比如 /var/www/attached/
            $save_path
            //保存图片和文件的根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
            , $save_url
            , $view_folder_name = 'ttt'

    ;

    public function __construct() {
        parent::__construct(); #一定不能忘记调用父类构造方法
        $this->save_path = 'res/attached/';
        $this->save_url = 'res/attached/';
        $this->model('mmm');
    }

    #显示添加数据界面

    public function doAdd() {
        $this->view('ttt/ttt_add');
    }

    #显示修改数据界面

    public function doModify() {
        $pk = 'ppk';
        $pid = $this->input->get_post($pk);
        $row = $this->model->mmm->selectRowByWhere(array($pk => $pid));
        if (is_numeric($pid) && !empty($row)) {
            $data['row'] = $row;
            $this->view('ttt/ttt_modify', $data);
        } else {
            trigger404();
        }
    }

    #显示列表

    public function doPage() {
        $page = intval($this->input->get('p')) ? $this->input->get('p') : 1;
        $data = $this->model->mmm->getPage($page, 10, '?ccc.page&p={page}', '*');
        $data['pk'] = 'ppk';
        $this->view('ttt/ttt_list', $data);
    }

    #显示列表

    public function doSearch() {
        
    }

    #执行添加数据

    public function doCreate() {
        $msg = $this->model->mmm->insert();
        if ($msg === TRUE) {
            $this->ajax_echo(200, '添加成功');
        } elseif ($msg === FALSE) {
            $this->ajax_echo(400, '添加失败');
        } else {
            $this->ajax_echo(400, $msg);
        }
    }

    #执行更新数据

    public function doUpdate() {
        $msg = $this->model->mmm->update();
        if ($msg === TRUE) {
            $this->ajax_echo(200, '修改成功');
        } elseif ($msg === FALSE) {
            $this->ajax_echo(400, '修改失败');
        } else {
            $this->ajax_echo(400, $msg);
        }
    }

    #删除多个记录

    public function doDels() {
        $pks = $this->input->post('pks');
        if (is_array($pks)) {
            echo $this->model->mmm->deleteByWhereIn($this->model->mmm->pk, $pks) ? $this->ajax_echo(200, '删除成功') : $this->ajax_echo(400, '删除失败');
        } else {
            ajaxEcho(400, 'error data.');
        }
    }

    #富文本编辑器上传文件接口

    public function doUploadJson() {
        //根目录路径，可以指定绝对路径，比如 /var/www/attached/
        $save_path = $this->save_path;
        //根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        $save_url = $this->save_url;
        //定义允许上传的文件扩展名
        $ext_arr = array(
            'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
            'flash' => array('swf', 'flv'),
            'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
            'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
        );
        //最大文件大小
        $max_size = 1000000;
        //有上传文件时
        if (empty($_FILES) === false) {
            //原文件名
            $file_name = $_FILES['imgFile']['name'];
            //服务器上临时文件名
            $tmp_name = $_FILES['imgFile']['tmp_name'];
            //文件大小
            $file_size = $_FILES['imgFile']['size'];
            //检查文件名
            if (!$file_name) {
                $this->alert("请选择文件。");
            }
            //检查目录
            if (@is_dir($save_path) === false) {
                $this->alert("上传目录不存在。");
            }
            //检查目录写权限
            if (@is_writable($save_path) === false) {
                $this->alert("上传目录没有写权限。");
            }
            //检查是否已上传
            if (@is_uploaded_file($tmp_name) === false) {
                $this->alert("临时文件可能不是上传文件。");
            }
            //检查文件大小
            if ($file_size > $max_size) {
                $this->alert("上传文件大小超过限制。");
            }
            //检查目录名
            $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
            if (empty($ext_arr[$dir_name])) {
                $this->alert("目录名不正确。");
            }
            //获得文件扩展名
            $temp_arr = explode(".", $file_name);
            $file_ext = array_pop($temp_arr);
            $file_ext = trim($file_ext);
            $file_ext = strtolower($file_ext);
            //检查扩展名
            if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
                $this->alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
            }
            //创建文件夹
            if ($dir_name !== '') {
                $save_path .= $dir_name . "/";
                $save_url .= $dir_name . "/";
                if (!file_exists($save_path)) {
                    mkdir($save_path);
                }
            }
            $ymd = date('Y') . '/' . date('m') . '/' . date('d');
            $save_path .= $ymd . "/";
            $save_url .= $ymd . "/";
            if (!file_exists($save_path)) {
                mkdir($save_path);
            }
            //新文件名
            $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
            //移动文件
            $file_path = $save_path . $new_file_name;
            if (move_uploaded_file($tmp_name, $file_path) === false) {
                $this->alert("上传文件失败。");
            }
            @chmod($file_path, 0644);
            $file_url = $save_url . $new_file_name;
            header('Content-type: text/html; charset=UTF-8');
            echo json_encode(array('error' => 0, 'url' => $file_url));
            exit;
        }
    }

    #富文本编辑器浏览图片接口

    public function doFileManagerJson() {
        //根目录路径，可以指定绝对路径，比如 /var/www/attached/
        $root_path = $this->save_path;
        //根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        $root_url = $this->save_url;
        //图片扩展名
        $ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
        //目录名
        $dir_name = empty($_GET['dir']) ? '' : trim($_GET['dir']);
        if (!in_array($dir_name, array('', 'image', 'flash', 'media', 'file'))) {
            echo "Invalid Directory name.";
            exit;
        }
        if ($dir_name !== '') {
            $root_path .= $dir_name . "/";
            $root_url .= $dir_name . "/";
            if (!file_exists($root_path)) {
                mkdir($root_path);
            }
        }
        //根据path参数，设置各路径和URL
        if (empty($_GET['path'])) {
            $current_path = realpath($root_path) . '/';
            $current_url = $root_url;
            $current_dir_path = '';
            $moveup_dir_path = '';
        } else {
            $current_path = realpath($root_path) . '/' . $_GET['path'];
            $current_url = $root_url . $_GET['path'];
            $current_dir_path = $_GET['path'];
            $moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
        }
        //echo realpath($root_path);
        //排序形式，name or size or type
        $this->order = empty($_GET['order']) ? 'name' : strtolower($_GET['order']);
        //不允许使用..移动到上一级目录
        if (preg_match('/\.\./', $current_path)) {
            echo 'Access is not allowed.';
            exit;
        }
        //最后一个字符不是/
        if (!preg_match('/\/$/', $current_path)) {
            echo 'Parameter is not valid.';
            exit;
        }
        //目录不存在或不是目录
        if (!file_exists($current_path) || !is_dir($current_path)) {
            echo 'Directory does not exist.';
            exit;
        }
        //遍历目录取得文件信息
        $file_list = array();
        if ($handle = opendir($current_path)) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
                if ($filename{0} == '.')
                    continue;
                $file = $current_path . $filename;
                if (is_dir($file)) {
                    $file_list[$i]['is_dir'] = true; //是否文件夹
                    $file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
                    $file_list[$i]['filesize'] = 0; //文件大小
                    $file_list[$i]['is_photo'] = false; //是否图片
                    $file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
                } else {
                    $file_list[$i]['is_dir'] = false;
                    $file_list[$i]['has_file'] = false;
                    $file_list[$i]['filesize'] = filesize($file);
                    $file_list[$i]['dir_path'] = '';
                    $file_ext = strtolower(array_pop(explode('.', trim($file))));
                    $file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
                    $file_list[$i]['filetype'] = $file_ext;
                }
                $file_list[$i]['filename'] = $filename; //文件名，包含扩展名
                $file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
                $i++;
            }
            closedir($handle);
        }
        usort($file_list, 'cmp_func');
        $result = array();
        //相对于根目录的上一级目录
        $result['moveup_dir_path'] = $moveup_dir_path;
        //相对于根目录的当前目录
        $result['current_dir_path'] = $current_dir_path;
        //当前目录的URL
        $result['current_url'] = $current_url;
        //文件数
        $result['total_count'] = count($file_list);
        //文件列表数组
        $result['file_list'] = $file_list;
        //输出JSON字符串
        header('Content-type: application/json; charset=UTF-8');
        echo json_encode($result);
    }

    #富文本编辑框保存远程图片接口

    public function doSaveRemoteImage() {
        if (!function_exists("file_get_contents")) {
            $this->alert("服务器PHP没有启用函数[file_get_contents],不能保存远程图片.", 2);
            exit();
        }
        if (!ini_get("allow_url_fopen")) {
            $this->alert("服务器PHP没有启用[allow_url_fopen],不能保存远程图片.", 2);
            exit();
        }
        if (empty($_POST['imgurl'])) {
            $this->alert("imgurl为空！");
        }
        //根目录路径，可以指定绝对路径，比如 /var/www/attached/
        $root_path = $this->save_path;
        //根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        $root_url = $this->save_url;

        $phpbb_root_path = $root_path;
        //图片显示路径
        $imgShowPath = $root_url . 'image/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
        //图片保存路径
        $imgStorePath = $phpbb_root_path . 'image/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
        if (!is_dir($imgStorePath)) {
            mkdir($imgStorePath, 0777, true);
        }
        $imgurl = $_POST['imgurl'];
        if (false !== stripos($imgurl, "//" . $_SERVER['HTTP_HOST'] . "")) {
            header('Content-type: text/html; charset=utf-8');
            echo json_encode(array('error' => 0, 'url' => $imgurl));
        }
        $newimgname = time() . '_' . rand(1000, 9999);
        $newimgpath = $imgStorePath . $newimgname;
        set_time_limit(0);
        @$get_file = file_get_contents($imgurl);
        if ($get_file) {
            $fp = fopen($newimgpath, 'w');
            fwrite($fp, $get_file);
            fclose($fp);
            //读取文件获取类型
            if (!($filetype = $this->getFiletype($newimgpath))) {
                $this->alert("图片类型未知！");
                @unlink($newimgpath);
            } else {
                rename($newimgpath, $newimgpath . "." . $filetype);
                $newimgname = $newimgname . "." . $filetype;
            }
            header('Content-type: text/html; charset=utf-8');
            echo json_encode(array('error' => 0, 'url' => $imgShowPath . $newimgname));
            exit;
        } else {
            $this->alert("获取图片{$imgurl}失败！");
        }
    }

    #安全读取图片文件扩展名

    public function getFiletype($filename) {
        $file = fopen($filename, "rb");
        $bin = fread($file, 2); //只读2字节  
        fclose($file);
        $strInfo = @unpack("C2chars", $bin);
        $typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);
        $ftype = '';
        switch ($typeCode) {
            case 255216:
                $ftype = 'jpg';
                break;
            case 7173:
                $ftype = 'gif';
                break;
            case 6677:
                $ftype = 'bmp';
                break;
            case 13780:
                $ftype = 'png';
                break;
        }
        return $ftype;
    }

    public function alert($msg) {
        header('Content-type: text/html; charset=UTF-8');
        echo json_encode(array('error' => 1, 'message' => $msg));
        exit;
    }

}

#end class
#外部函数，浏览图片依赖函数

function cmp_func($a, $b, $order = 'type') {
    $order = strtolower($_POST['order']);
    if ($a['is_dir'] && !$b['is_dir']) {
        return -1;
    } else if (!$a['is_dir'] && $b['is_dir']) {
        return 1;
    } else {
        if ($order == 'size') {
            if ($a['filesize'] > $b['filesize']) {
                return 1;
            } else if ($a['filesize'] < $b['filesize']) {
                return -1;
            } else {
                return 0;
            }
        } else if ($order == 'type') {
            return strcmp($a['filetype'], $b['filetype']);
        } else {
            return strcmp($a['filename'], $b['filename']);
        }
    }
}