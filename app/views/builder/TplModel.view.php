<?php if(!defined('IN_WONIU_APP')){exit();}
/**
 * MrPmvc模型类,表'#table#'模型，该类继承BaseModel完成复杂数据操作
 * @author 狂奔的蜗牛
 * @email  672308444@163.com
 * @version alpha
 */
class mmmModel extends BaseModel{
         protected $pk;
         protected $keys;
         protected $table;
         protected $map;
         public  $msg;
         public function __construct(){
             parent::__construct();
             $this->table="'#table#'";
             $this->pk="ppk";
             $this->keys='#keys#';
             #字段映射，$key是表单name名称，$val是字段名
             $this->map='#map#';
             #手动加载数据库,用于数据库自动连接设为false时使用
             //$this->initDB();
             #针对msyql编码设置,之前确保数据库已经连接
             //if($this->db){$this->db->exec('set names utf8');}
         }
    /**
     * 添加数据
     *    验证失败返回FALSE并会保存验证提示到$this->msg中
     *    添加成功返回最后添加的主键id
     *    返回FALSE表示添加失败
     */
    public function insert(){
             /**
              * 添加数据验证规则($rule[$key],$key都是表字段名)
              *$key=>['rule'] 可以是function比如checkpass就是调用当前类的checkpass方法,
                            函数接受两个参数第一个是当前验证字段的值,
                            第二个是读取的整个数据数组(根据$map读取的),键是表字段.
              *$key=>['rule'] 还可以是正则表达式，目前仅支持函数和正则表达式这两种
              *
              *验证失败后,提示信息（$key=>['msg']） 保存在$this->msg中
              *
              *注意：如果不想验证某一字段，可以把rule留空，或者删除字段对应的键值对
              *      总之就是有规则不管有没有获取到对应的数据都进行强制验证。
              *      没有规则不管有没有获取到对应的数据都不验证。
              *
              */
             $rule='#rule#';
             $data=parent::readData($this->map);
             #数据预处理，比如加入附加数据，$data['time']=time(); time是表里面的字段
             
             #表单验证
             $isOk=parent::check($rule,$data);
             if($isOk){
                  return parent::_insert($data);
             }else{
                  return FALSE;
             }
    }
    /**
     * 更新数据
     *    验证失败返回FALSE并会保存验证提示到$this->msg中
     *    返回TRUE 表示更新
     *    返回FALSE表示更新失败
     */
    public function update(){
             /**
              * 更新数据验证规则($rule[$key],$key都是表字段名)
              *$key=>['rule'] 可以是function比如checkpass就是调用当前类的checkpass方法,
                                                                                函数接受两个参数第一个是当前验证字段的值,
                                                                                第二个是读取的整个数据数组(根据$map读取的),键是表字段.
              *$key=>['rule'] 还可以是正则表达式，目前仅支持函数和正则表达式这两种
              *
              *验证失败后,提示信息（$key=>['msg']） 保存在$this->msg中
              *
              *注意：如果不想验证某一字段，可以把rule留空，或者删除字段对应的键值对
              *      总之就是有规则不管有没有获取到对应的数据都进行强制验证。
              *      没有规则不管有没有获取到对应的数据都不验证。
              *
              */
             $rule='#rule2#';
             $data=parent::readData($this->map);
             #数据预处理，比如加入附加数据，$data['time']=time(); time是表里面的字段
             
             
             #表单验证.
             $isOk=parent::check($rule,$data);
             if($isOk){
                  return parent::_update($data);
             }else{
                  return FALSE;
             }
    }
    
    /**
     * 按主键获取一条记录
     * 成功返回一个数组，失败返回false
     */
    public function getBypk2($pid){
           $pid=intval($pid);
           return parent::getByCol($this->pk,$pid);
    }
    /**
     * 按主键更新一条记录的指定字段
     * 成功返回影响的条数，失败返回-1
     */
    public function setColBypk2($col,$val,$pid){
           return parent::setColByPk($col,$val,$pid);
    }
   /**
     * 按主键数组更新多条记录的指定字段
     * 成功返回影响的条数，失败返回-1
     */
    public function setColBypk2s($col,$val,Array $pids){
           return parent::setColByPks($col,$val,$pids);
    }
    /**
     * 根据主键删除一条记录
     * 成功返回影响的条数，失败返回-1
     */
    public function deleteBypk2($pid){
           return $this->db->delete("{$this->table} where {$this->pk}=?",array($pid));
    }
     /**
     * 根据主键数组删除多条记录
     * 成功返回影响的条数，失败返回-1
     */
    public function deleteBypk2s(Array $pids){
           $ina=array();
           foreach ($pids as $val){
              $ina[]='?';
           }
           $instr=implode(',', $ina);
           return $this->db->delete("{$this->table} where {$this->pk} in({$instr})",$pids);
    }
    /**
     * @param int $pagesize    每页多少条
     * @param string $orderby  排序字段 如：time desc
     * @param string $fields   搜索的字段 如：id,title 默认：*
     * @param string $where    搜索的条件 如：kind='news' 默认：1
     * @return Array  $data
       <br/> 返回如： $data['rows'];
                     $data['navhtml'];
       <br/>其中：$data['rows']是分页结果集，$data['navhtml']是分页导航html
     */
    public function page($pagesize=10,$orderby=null,$fields='*',$where='1'){
           if(!$orderby)$orderby="{$this->pk} desc";
           $curpage=httpInt('p',false,1);
           $start=($curpage-1)*$pagesize;
           $total = $this->db->getVar("select count(*) from {$this->table} where {$where}");
           $px = page($total,$pagesize);
           $list = $this->db->rows("select {$fields} from {$this->table} where {$where} order by {$orderby} LIMIT {$start},{$pagesize}");
           $data=array();
           $list=is_array($list)?$list:array();
           $data['rows']=$list;
           $data['navhtml']=$px;
           return $data;
    }
    /**
     * @param Array $search    说明看下面的示例
     * @param Boolean $is_and  有多个条件时where采用and连接还是or连接 true：and false：or 默认false
     * @param int $pagesize    每页多少条
     * @param string $orderby  排序字段 如：time desc
     * @return Array  $data
       <br/> 返回如：$data['rows'];
                     $data['navhtml'];
                <br/>其中：$data['rows']是分页结果集，$data['navhtml']是分页导航html
     * <br/>$search 示例： 
     * $search=array(
                     'keyword'=>'name'
                     ,'k2'=>'id'
                     );
                  其中keyword，k2是url中的参数名称，name和id分别是参数对应表字段名称
     */
    public function  search(Array $search,$is_and=false,$pagesize=10,$orderby=null){
           $linker=$is_and?' and ':' or ';
           $url=request_uri();
           $pkey='p';
           $where=array();$attach_link=array();
           foreach ($search as $key=>$col) {
               $where[]=" {$col} like '%".httpString($key,false)."%' ";
               $attach_link[]="{$key}=".urlencode(httpString($key,false));
               $url=preg_replace("/&?{$key}=[^&]{0,}/", '',$url);
           }
           $url=preg_replace("/&?{$pkey}=[^&]{0,}/", '',$url);
           $where_str=implode($linker, $where);
           $where_str=$where_str?$where_str:1;
           $attach_link_str=implode('&', $attach_link);
           if(strpos($url, '?')===FALSE){
               $url=$url.'?'.$pkey.'={page}&'.$attach_link_str;
           }else{
               $url=$url.'&'.$pkey.'={page}&'.$attach_link_str;
           }
           if(!$orderby)$orderby="{$this->pk} desc";
           $total = $this->db->getVar("select count(*) from {$this->table} where {$where_str}");
           $px = page($total,$pagesize,$pkey,$url);
           $curpage=httpInt($pkey,false,1);
           $curpage=$curpage>ceil($total/$pagesize)||$curpage<=0?1:$curpage;
           $start=($curpage-1)*$pagesize;
           $list = $this->db->rows("select * from {$this->table} where {$where_str} order by {$orderby} LIMIT {$start},{$pagesize}");
           $data=array();
           $list=is_array($list)?$list:array();
           $data['rows']=$list;
           $data['navhtml']=$px;
           $this->db->debug();
           return $data;
    }
    #自定义表单验证函数
    /**示例：两个参数第一个是当前验证字段的值，第二个读取的整个数据数组(根据$map读取的),键是表字段,
     public function checkPass($val,$data){
                 if($val==P('pass2')){
                       return  true;
                 }else{
                       return false;
                 }
     }
     */
}