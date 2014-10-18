<?php

/**
 * MicroPHP
 *
 * An open source application development framework for PHP 5.2.0 or newer
 *
 * @package                MicroPHP
 * @author                狂奔的蜗牛
 * @email                672308444@163.com
 * @copyright          Copyright (c) 2013 - 2013, 狂奔的蜗牛, Inc.
 * @link                http://git.oschina.net/snail/microphp
 * @since                Version 1.0
 * @createdtime       {createdtime}
 * @property CI_DB_active_record $db
 * @property phpFastCache        $cache
 * @property MpInput          $input
 */
class WoniuModel extends MpLoaderPlus {

    private static $instance;

    /**
     * 实例化一个模型
     * @param type $classname_path
     * @param type $hmvc_module_floder
     * @return type WoniuModel
     */
    public static function instance($classname_path = null, $hmvc_module_floder = NULL) {
        if (!empty($hmvc_module_floder)) {
            MpRouter::switchHmvcConfig($hmvc_module_floder);
        }
        //这里调用控制器instance是为了触发自动加载，从而避免了插件模式下，直接instance模型，自动加载失效的问题
        WoniuController::instance();
        if (empty($classname_path)) {
            $renew = is_bool($classname_path) && $classname_path === true;
            MpLoader::classAutoloadRegister();
            return empty(self::$instance) || $renew ? self::$instance = new self() : self::$instance;
        }
        $system = systemInfo();
        $classname_path = str_replace('.', DIRECTORY_SEPARATOR, $classname_path);
        $classname = basename($classname_path);

        $model_folders = $system['model_folder'];

        if (!is_array($model_folders)) {
            $model_folders = array($model_folders);
        }
        $count = count($model_folders);
        //在plugin模式下，路由器不再使用，那么自动注册不会被执行，自动加载功能会失效，所以在这里再尝试加载一次，
        //如此一来就能满足两种模式
        MpLoader::classAutoloadRegister();
        foreach ($model_folders as $key => $model_folder) {
            $filepath = $model_folder . DIRECTORY_SEPARATOR . $classname_path . $system['model_file_subfix'];
            $alias_name = $classname;
            if (in_array($alias_name, array_keys(WoniuModelLoader::$model_files))) {
                return WoniuModelLoader::$model_files[$alias_name];
            }
            if (file_exists($filepath)) {
                if (!class_exists($classname, FALSE)) {
                    MpLoader::includeOnce($filepath);
                }
                if (class_exists($classname, FALSE)) {
                    return WoniuModelLoader::$model_files[$alias_name] = new $classname();
                } else {
                    if ($key == $count - 1) {
                        trigger404('Model Class:' . $classname . ' not found.');
                    }
                }
            } else {
                if ($key == $count - 1) {
                    trigger404($filepath . ' not  found.');
                }
            }
        }
    }

}

/**
 * Description of WoniuTableModel
 *
 * @author pm
 */
class MpTableModel extends MpModel {

    /**
     * 表主键名称
     * @var string 
     */
    public $pk;

    /**
     * 表的字段名称数组
     * @var array 
     */
    public $keys = array();

    /**
     * 不含表前缀的表名称
     * @var string 
     */
    public $table;

    /**
     * 含表前缀的表名称
     * @var string 
     */
    public $full_table;

    /**
     * 字段映射，$key是表单name名称，$val是字段名
     * @var array 
     */
    public $map = array();

    /**
     * 当前$this->db使用的表前缀
     * @var string 
     */
    public $prefix;

    /**
     * 完整的表字段信息
     * @var array 
     */
    public $fields = array();
    private static $models = array(), $table_cache = array();

    public function __construct() {
        parent::__construct();
        $this->database();
    }

    /**
     * 初始化一个表模型，返回模型实例
     * @param type $table         名称
     * @param CI_DB_active_record $db 数据库连接对象
     * @return MpTableModel
     */
    public function init($table, $db = null) {
        if (!is_null($db)) {
            $this->db = $db;
        }
        $this->prefix = $this->db->dbprefix;
        $this->table = $table;
        $this->full_table = $this->prefix . $table;
        $this->fields = $fields = $this->getTableFieldsInfo($table, $this->db);
        foreach ($fields as $col => $info) {
            if ($info['primary']) {
                $this->pk = $col;
            }
            $this->keys[] = $col;
            $this->map[$col] = $col;
        }
        return $this;
    }

    /**
     * 实例化一个默认表模型
     * @param type $table
     * @return MpTableModel
     */
    public static function M($table, $db = null) {
        if (!isset(self::$models[$table])) {
            self::$models[$table] = new MpTableModel();
            self::$models[$table]->init($table, $db);
        }
        return self::$models[$table];
    }

    /**
     * 表所有字段数组
     * @return array
     */
    public function columns() {
        return $this->keys;
    }

    /**
     * 缓存表字段信息，并返回
     * @staticvar array $info  字段信息数组
     * @param type $tableName  不含前缀的表名称
     * @return array
     */
    public static function getTableFieldsInfo($tableName, $db) {
        if (!empty(self::$table_cache[$tableName])) {
            return self::$table_cache[$tableName];
        }
        if (!file_exists($cache_file = systemInfo('table_cache_folder') . DIRECTORY_SEPARATOR . $tableName . '.php')) {
            $info = array();
            $result = $db->query('SHOW FULL COLUMNS FROM ' . $db->dbprefix . $tableName)->result_array();
            if ($result) {
                foreach ($result as $val) {
                    $info[$val['Field']] = array(
                        'name' => $val['Field'],
                        'type' => $val['Type'],
                        'comment' => $val['Comment'] ? $val['Comment'] : $val['Field'],
                        'notnull' => $val['Null'] == 'NO' ? 1 : 0,
                        'default' => $val['Default'],
                        'primary' => (strtolower($val['Key']) == 'pri'),
                        'autoinc' => (strtolower($val['Extra']) == 'auto_increment'),
                    );
                }
            }
            $content = 'return ' . var_export($info, true) . ";\n";
            $content = '<?' . 'php' . "\n" . $content;
            file_put_contents($cache_file, $content);
            $ret_info[$tableName] = $info;
        } else {
            $ret_info[$tableName] = include ($cache_file);
        }
        return $ret_info[$tableName];
    }

    /**
     * 数据验证
     * @param type $source_data 数据源，要检查的数据
     * @param type $ret_data    数据验证通过$ret_data是验证规则处理后的数据用户插入或者更新到数据库,数据验证失败$ret_data是空数组
     * @param type $rule 验证规则<br/>
     *                   格式：array(<br/>
     *                               '字段名称'=>array(<br/>
     *                                               '表单验证规则'=>'验证失败提示信息'<br/>
     *                                               ,...   <br/>
     *                                               )<br/>
     *                               ,...<br/>
     *                             )<br/>
     * @param type $map  字段映射信息数组。格式：array('表单name名称'=>'表字段名称',...)
     * @return string 返回null:验证通过。非空字符串:验证失败提示信息。 
     */
    public function check($source_data, &$ret_data, $rule = null, $map = null) {
        $rule = !is_array($rule) ? array() : $rule;
        $map = is_null($map) ? $this->map : $map;
        $data = $this->readData($map, $source_data);
        return $this->checkData($rule, $data, $ret_data);
    }

    /**
     * 添加数据
     * @param array $ret_data  需要添加的数据
     * @return boolean
     */
    public function insert($ret_data) {
        return $this->db->insert($this->table, $ret_data);
    }

    /**
     * 更新数据
     * @param type $ret_data  需要更新的数据
     * @param type $where     可以是where条件关联数组，还可以是主键值。
     * @return boolean
     */
    public function update($ret_data, $where) {
        $where = is_array($where) ? $where : array($this->pk => $where);
        return $this->db->where($where)->update($this->table, $ret_data);
    }

    /**
     * 获取一条或者多条数据
     * @param type $values      可以是一个主键的值或者主键的值数组，还可以是where条件
     * @param boolean $is_rows  返回多行记录还是单行记录，true：多行，false：单行
     * @param type $order_by    当返回多行记录时，可以指定排序，比如：'time desc'
     * @return int
     */
    public function find($values, $is_rows = false, $order_by = null) {
        if (empty($values)) {
            return 0;
        }
        if (is_array($values)) {
            $is_asso = array_diff_assoc(array_keys($values), range(0, sizeof($values))) ? TRUE : FALSE;
            if ($is_asso) {
                $this->db->where($values);
            } else {
                $is_rows = true;
                $this->db->where_in($this->pk, array_values($values));
            }
        } else {
            $this->db->where(array($this->pk => $values));
        }
        if ($order_by) {
            $this->db->order_by($order_by);
        }
        if (!$is_rows) {
            $this->db->limit(1);
        }
        $rs = $this->db->get($this->table);
        if ($is_rows) {
            return $rs->result_array();
        } else {
            return $rs->row_array();
        }
    }

    /**
     * 获取所有数据
     * @param type $where   where条件数组
     * @param type $orderby 排序，比如: id desc
     * @param type $limit   limit数量，比如：10
     * @param type $fileds  要搜索的字段，比如：id,name。留空默认*
     * @return type
     */
    public function findAll($where = null, $orderby = NULL, $limit = null, $fileds = null) {
        if (!is_null($fileds)) {
            $this->db->select($fileds);
        }
        if (!is_null($where)) {
            $this->db->where($where);
        }
        if (!is_null($orderby)) {
            $this->db->order_by($orderby);
        }
        if (!is_null($limit)) {
            $this->db->limit($limit);
        }
        return $this->db->get($this->table)->result_array();
    }

    /**
     * 根据条件获取一个字段的值或者数组
     * @param type $col         字段名称
     * @param type $where       可以是一个主键的值或者主键的值数组，还可以是where条件
     * @param boolean $is_rows  返回多行记录还是单行记录，true：多行，false：单行
     * @param type $order_by    当返回多行记录时，可以指定排序，比如：'time desc'
     * @return type
     */
    public function findCol($col, $where, $is_rows = false, $order_by = null) {
        $row = $this->find($where, $is_rows, $order_by);
        if (!$is_rows) {
            return isset($row[$col]) ? $row[$col] : null;
        } else {
            $vals = array();
            foreach ($row as $v) {
                $vals[] = $v[$col];
            }
            return $vals;
        }
    }

    /**
     * 
     * 根据条件删除记录
     * @param type $values 可以是一个主键的值或者主键主键的值数组
     * @param type $cond   附加的where条件，关联数组
     * 成功则返回影响的行数，失败返回false
     */
    public function delete($values, Array $cond = NULL) {
        return $this->deleteIn($this->pk, $values, $cond);
    }

    /**
     * 
     * 根据条件删除记录
     * @param type $key    where in的字段名称
     * @param type $values 可以是一个主键的值或者主键主键的值数组
     * @param type $cond   附加的where条件，关联数组
     * 成功则返回影响的行数，失败返回false
     * @return int|boolean
     */
    public function deleteIn($key, $values, Array $cond = NULL) {
        if (empty($values)) {
            return 0;
        }
        if (is_array($values)) {
            $this->db->where_in($key, array_values($values));
        } else {
            $this->db->where(array($key => $values));
        }
        if (!empty($cond)) {
            $this->db->where($cond);
        }
        if ($this->db->delete($this->table)) {
            return $this->db->affected_rows();
        } else {
            return false;
        }
    }

    /**
     * 分页方法
     * @param int $page       第几页
     * @param int $pagesize   每页多少条
     * @param string $url     基础url，里面的{page}会被替换为实际的页码
     * @param string $fields  select的字段，全部用*，多个字段用逗号分隔
     * @param array $where    where条件，关联数组
     * @param array $like     搜素的字段，比如array('title'=>'java');搜索title包含java
     * @param string $orderby 排序字段，比如: 'id desc'
     * @param array $page_bar_order   分页条组成，可以参考手册分页条部分
     * @param int   $page_bar_a_count 分页条a的数量，可以参考手册分页条部分
     * @return type
     */
    public function getPage($page, $pagesize, $url, $fields = '*', Array $where = null, Array $like = null, $orderby = null, $page_bar_order = array(1, 2, 3, 4, 5, 6), $page_bar_a_count = 10) {
        $data = array();

        if (is_array($where)) {
            $this->db->where($where);
        }
        if (is_array($like)) {
            $this->db->like($like);
        }
        $total = $this->db->from($this->table)->count_all_results();
        //这里必须重新附加条件，上面的count会重置条件
        if (is_array($where)) {
            $this->db->where($where);
        }
        if (is_array($like)) {
            $this->db->like($like);
        }
        if (!is_null($orderby)) {
            $this->db->order_by($orderby);
        }
        $data['items'] = $this->db->select($fields)->limit($pagesize, ($page - 1) * $pagesize)->get($this->table)->result_array();
        $data['page'] = $this->page($total, $page, $pagesize, $url, $page_bar_order, $page_bar_a_count);
        return $data;
    }

    /**
     * SQL搜索
     * @param type $page      第几页
     * @param type $pagesize  每页多少条
     * @param type $url       基础url，里面的{page}会被替换为实际的页码
     * @param type $fields    select的字段，全部用*，多个字段用逗号分隔
     * @param type $cond      SQL语句where后面的部分，不要带limit
     * @param array $page_bar_order   分页条组成，可以参考手册分页条部分
     * @param int   $page_bar_a_count 分页条a的数量，可以参考手册分页条部分
     * @return type
     */
    public function search($page, $pagesize, $url, $fields, $cond, $page_bar_order = array(1, 2, 3, 4, 5, 6), $page_bar_a_count = 10) {
        $data = array();
        $table = $this->full_table;
        $query = $this->db->query('select count(*) as total from ' . $table . (strpos(trim($cond), 'order') === 0 ? '' : ' where') . $cond)->row_array();
        $total = $query['total'];
        $data['items'] = $this->db->query('select ' . $fields . ' from ' . $table . (strpos(trim($cond), 'order') === 0 ? '' : ' where') . $cond . ' limit ' . (($page - 1) * $pagesize) . ',' . $pagesize)->result_array();
        $data['page'] = $this->page($total, $page, $pagesize, $url, $page_bar_order, $page_bar_a_count);
        return $data;
    }

}
class MpModel extends WoniuModel{}
/* End of file Model.php */
