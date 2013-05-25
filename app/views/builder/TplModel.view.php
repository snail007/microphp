<?php

/**
 * MicroPHP模型类,表ttt模型
 * @author 狂奔的蜗牛
 * @email  672308444@163.com
 * @version alpha
 */
class mmm extends WoniuModel {

    public $pk;
    public $keys;
    public $table;
    public $map;
    public $msg;

    public function __construct() {
        parent::__construct();
        $this->table = "ttt";
        $this->pk = "ppk";
        $this->keys = '#keys#';
        #字段映射，$key是表单name名称，$val是字段名
        $this->map = '#map#';
        $this->database();
    }

    /**
     * 添加数据
     */
    public function insert() {
        $rule = '#rule#';
        $data = $this->readData($this->map);
        #数据预处理，比如加入附加数据，$data['time']=time(); time是表里面的字段
        #表单验证
        $msg = $this->checkData($rule, $data);
        if (is_null($msg)) {
            return $this->db->insert($this->table, $data);
        } else {
            return $msg;
        }
    }

    /**
     * 更新数据
     */
    public function update() {
        $rule = '#rule2#';
        $data = $this->readData($this->map);
        #数据预处理，比如加入附加数据，$data['time']=time(); time是表里面的字段
        #表单验证.
        $msg = $this->checkData($rule, $data);
        if (is_null($msg)) {
            $where = array($this->pk => @$data[$this->pk]);
            unset($data[$this->pk]);
            return $this->db->where($where)->update($this->table, $data);
        } else {
            return $msg;
        }
    }

    /**
     * 根据条件获取一条记录
     */
    public function selectRowByWhere(Array $where) {
        return $this->db->where($where)->limit(1)->get($this->table)->row_array();
    }

    /**
     * 根据条件获取一条记录
     */
    public function selectRowsByWhere(Array $where) {
        return $this->db->where($where)->get($this->table)->result_array();
    }

    /**
     * 根据条件更新一条记录
     */
    public function updateByWhere(Array $where, Array $data) {
        return $this->db->where($where)->update($this->table, $data);
    }

    /**
     * 根据条件删除记录
     */
    public function deleteByWhere(Array $where) {
        return $this->db->where($where)->delete($this->table);
    }

    /**
     * 根据条件删除记录
     */
    public function deleteByWhereIn($key, Array $in) {
        return $this->db->where_in($key,$in)->delete($this->table);
    }

    public function getPage($page, $pagesize, $url, $fields, Array $where, Array $like = null, $orderby = null) {
        $data = array();
        $total = $this->db->from($this->table)->where($where)->count_all_results();
        if (is_array($like)) {
            $this->db->like($like);
        }
        if (!is_null($orderby)) {
            $this->db->order_by($orderby);
        }
        $data['items'] = $this->db->select($fields)->where($where)->limit($pagesize, ($page - 1) * $pagesize)->get($this->table)->result_array();
        $data['page'] = $this->page($total, $page, $pagesize, $url);
        return $data;
    }

    /*     *
     * 示例：两个参数第一个是当前验证字段的值，第二个读取的整个数据数组(根据$map读取的),键是表字段,
     * 
     */

    public function checkPass($val, $data) {
        if ($val == $this->input->post('pass2')) {
            return null;
        } else {
            return '两次密码不一致';
        }
    }

}