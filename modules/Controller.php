<?php



class Controller extends Loader {

    private static $woniu;

    public function __construct() {
        parent::__construct();
        self::$woniu = &$this;
    }

    public static function &getInstance() {
        return self::$woniu;
    }
    public  function view_path($view_name){
        global $system;
        $view_path = $system['view_folder'] . DIRECTORY_SEPARATOR . $view_name . $system['view_file_subfix'];
        return $view_path;
    }
}
