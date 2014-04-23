<?php

/**
 * 如果要在NetBeans里面实现，$this->代码提示，就把该文件放到项目目录任意文件夹即可。
 * @property CI_DB_active_record $db
 * @property phpFastCache        $cache
 * @property WoniuInput          $input
 */
class WoniuLoaderPlus extends WoniuLoader {
    
}

/**
 * @property CI_DB_active_record $db
 * @property phpFastCache        $cache
 * @property WoniuInput          $input
 * @property WoniuModelTip       $model
 */
class WoniuLoader {

    /**
     * 
     * @return WoniuModelTip
     */
    public function model() {
        return null;
    }

}

/**
 * 模型代码自动提示
 * 如果新建了模型，按着下面格式添加，即可实现$this->model->自动提示
 * 
 * @property DemoModel              $DemoModel
 */
class WoniuModelTip {
    
}
