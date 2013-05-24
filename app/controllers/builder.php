<?php
class Builder extends WoniuController {

    public function __construct() {
        parent::__construct();
        $this->database();
        define('APP_ROOT', $this->config('system','application_folder').'/');
    }

    public function doIndex() {
        $this->view('builder/index');
    }

    public function doReadInfo() {

        if ($this->db->simple_query('select count(*) from '.$this->input->post('table'))===FALSE) {
            exit('表  ' . $this->input->post('table') . ' 不存在.');
        } else {
            if ($row = $this->db->limit(1)->get($this->input->post('table'))->row_array()) {
                if (!isset($row[$this->input->post('pk')])) {
                    exit('主键' . $this->input->post('pk') . '不存在。');
                }
                $rule = array();
                foreach ($row as $col => $value) {
                    $rule[] = $col;
                    if ($this->input->post('pk') == $col)
                        continue;
                    $rule2[] = $col;
                }
                $data['rule_add'] = $rule2;
                $data['rule_modify'] = $rule;
                $data['attach'] = $this->input->post();
                $this->view('builder/show_create_model', $data);
            }else {
                exit('请保证表' . $this->input->post('table') . '至少存在一条记录.');
            }
        }
    }

    public function doShowCreateAction() {
        $row = $this->db->limit(1)->get($this->input->post('table'))->row_array();
        foreach ($row as $col => $value) {
            $rule[] = $col;
        }
        $data['rule'] = $rule;
        $data['attach'] = array('table' => $this->input->post('table'), 'pk' => $this->input->post('pk'), 'model' => $this->input->post('model'));
        $this->view('builder/show_create_action', $data);
    }

    public function doShowCreateView() {
        $data['attach'] = $this->input->post();
        $this->view('builder/show_create_view', $data);
    }

    public function doCreateModel() {
        $keys = array();
        $rule = array();
        $rule2 = array();
        $map = array();
        $row = $this->db->limit(1)->get($this->input->post('table'))->row_array();
        foreach (array_keys($row) as $col) {
            if (!(trim($this->input->post($col . '_modify_reg'), '  '))) {
                $rule2[$col] = array('rule' => $this->input->post($col . '_add_reg'), 'msg' => $this->input->post($col . '_add_hint'));
            } else {
                $rule2[$col] = array('rule' => $this->input->post($col . '_modify_reg'), 'msg' => $this->input->post($col . '_modify_hint'));
            }
            $map[$col] = $col;
            if ($col === $this->input->post('pk'))
                continue;
            $keys[] = $col;
            $rule[$col] = array('rule' => $this->input->post($col . '_add_reg'), 'msg' => $this->input->post($col . '_add_hint'));
        }
        $keys = $this->formatArray(var_export($keys, true));

        $rule = var_export($rule, true);
        $rule = str_replace("\n", "\n" . str_repeat(' ', 26), $rule);

        $rule2 = var_export($rule2, true);
        $rule2 = str_replace("\n", "\n" . str_repeat(' ', 26), $rule2);

        $map = var_export($map, true);
        $map = str_replace("\n", "\n" . str_repeat(' ', 26), $map);

        $tpl = file_get_contents(APP_ROOT . 'views/builder/TplModel.view.php');
        $data = str_replace("'#keys#'", $keys, $tpl);
        $data = str_replace("ttt", $this->input->post('table'), $data);
        $data = str_replace("mmm", $this->input->post('model'), $data);
        $data = str_replace("ppk", $this->input->post('pk'), $data);
        $data = str_replace("pk2", ucfirst($this->input->post('pk')), $data);
        $data = str_replace("'#rule#'", $rule, $data);
        $data = str_replace("'#rule2#'", $rule2, $data);
        $data = str_replace("'#map#'", $map, $data);
//        echo ($data);
//        exit();
        force_download($this->input->post('model') . '.Model.php', $data);
    }

    public function doCreateAction() {
        $map = array();
        $name = $this->input->post('name');
        $type = $this->input->post('type');
        $col = $this->input->post('col');
        foreach ($col as $key => $val) {
            if ($type[$key]) {
                $map[] = array('col' => $val, 'name' => ($name[$key] ? $name[$key] : $val), 'type' => $type[$key]);
            }
        }
        $map = $this->formatArray(var_export($map, true));
        $tpl = file_get_contents(APP_ROOT . 'views/builder/TplAction.php');
        $data = str_replace("ccc", $this->input->post('action_name'), $tpl);
        $data = str_replace("'#map#'", $map, $data);
        $data = str_replace("ttt", $this->input->post('table'), $data);
        $data = str_replace("'#pk#'", $this->input->post('pk'), $data);
        $data = str_replace("pageCols", implode(',', $col), $data);
        force_download($this->input->post('action_name') . '.php', $data);
    }

    public function doCreateAddView() {
        $data = array();
        $data["rows"] = array();
        $data['table'] = $this->input->post('table');
        $data['pk'] = $this->input->post('pk');
        $name = $this->input->post('name');
        $type = $this->input->post('type');
        $col = $this->input->post('col');
        foreach ($col as $key => $val) {
            if ($type[$key]) {
                $data["rows"][] = array('col' => $val, 'name' => ($name[$key] ? $name[$key] : $val), 'type' => $type[$key]);
            }
        }
        $ret = $this->view('builder/TplAdd', $data, true);
        $ret = str_replace("&{", '[{', $ret);
        $ret = str_replace("}&", '}]', $ret);
        //echo $ret;
        force_download($this->input->post('table') . '_add.view.php', $ret);
    }

    public function doCreateModifyView() {
        $data = array();
        $data["rows"] = array();
        $data['table'] = $this->input->post('table');
        $data['pk'] = $this->input->post('pk');
        $name = $this->input->post('name');
        $type = $this->input->post('type');
        $col = $this->input->post('col');
        foreach ($col as $key => $val) {
            if ($type[$key]) {
                $data["rows"][] = array('col' => $val, 'name' => ($name[$key] ? $name[$key] : $val), 'type' => $type[$key]);
            }
        }
        $tpl = $this->view('builder/TplModify', $data, true);
        $data = str_replace("&{", '<?php echo ', $tpl);
        $data = str_replace("}&", ';?>', $data);
        force_download($this->input->post('table') . '_modify.view.php', $data);
    }

    public function doCreateListView() {
        $data = array();
        $data["rows"] = array();
        $data['table'] = $this->input->post('table');
        $data['pk'] = $this->input->post('pk');
        $name = $this->input->post('name');
        $type = $this->input->post('type');
        $col = $this->input->post('col');
        foreach ($col as $key => $val) {
            if ($type[$key]) {
                $data["rows"][] = array('col' => $val, 'th' => ($name[$key] ? $name[$key] : $val), 'type' => $type[$key]);
            }
        }
        $tpl = $this->view('builder/TplList', $data, true);
        $tpl = str_replace("#{", '<!--<', $tpl);
        $tpl = str_replace("}#", '>-->', $tpl);
        $tpl = str_replace("[&{", '[{', $tpl);
        $tpl = str_replace("}&]", '}]', $tpl);
        force_download($this->input->post('table') . '_list.view.php', $tpl);
    }

    private function formatArray($str) {
        $patterns = array(
            "/\n/"
            , "/ /"
            , "/\\d+=>/"
        );
        $replace = array(
            ""
            , ""
            , ""
        );
        return preg_replace($patterns, $replace, $str);
    }

}