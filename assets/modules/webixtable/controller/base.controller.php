<?php namespace WebixTable;

class BaseController
{
    protected $modx = null;
    
    protected $params;

    protected $cfg;
    
    public $controller;
    
    protected $module_url;

    protected $cfg_arrays = ['fields', 'fields_names', 'fields_modalform', 'fields_modalform_names', 'fields_for_popup_editor', 'fields_for_selector_filter', 'fields_readonly'];

    protected $cfg_defaults = ['idField' => 'id', 'display' => '20', 'tpl' => 'main', 'controller_name' => 'main'];
    
    public function __construct($params = array())
    {
        $this->modx = EvolutionCMS();
        $this->setParams($params);
        $this->setCfg();
        $this->module_url = MODX_SITE_URL . 'assets/modules/webixtable/';
        $this->module_folder = MODX_BASE_PATH . 'assets/modules/webixtable/';
    }
    
    protected function setParams($params)
    {
        if (!empty($params) && is_array($params)) {
            $this->params = $params;
        } else {
            $this->params = $this->parseParams();
        }
        return $this;
    }
    
    protected function parseParams()
    {
        $params = array();
        if (isset($_REQUEST['module_id']) && (int)$_REQUEST['module_id'] > 0) {
            $prop = $this->modx->db->getValue("SELECT properties FROM " . $this->modx->getFullTableName("site_modules") . " WHERE id=" . (int)$_REQUEST['module_id'] . " LIMIT 0,1");
            if (!empty($prop)) {
                $params = $this->modx->parseProperties($prop);
            }
        }
        return $params;
    }
    
    protected function getParams()
    {
        
    }
    
    protected function getParam($key, $default = '', $makeArrayFromString = false)
    {
       $param = !empty($this->params[$key]) ? trim($this->params[$key]) : $default;
       if ($makeArrayFromString) {
            $param = $this->makeArrayFromStr($param);
       }
       return $param;
    }

    protected function makeArrayFromStr($str, $sep = ',') {
        return !empty($str) ? array_map('trim', explode($sep, $str)) : array();
    }

    protected function setCfg() 
    {
        return;
    }

    public function getController()
    {
        $controller_name = $this->modx->db->escape($this->getParam('controller_name'));
        $tpl = $this->modx->db->escape($this->getParam('tpl'));
        switch (true) {
            case (!empty($controller_name) && file_exists(MODX_BASE_PATH . 'assets/modules/webixtable/controller/' . strtolower($controller_name) . '.controller.php')) :
                $controller = 'WebixTable\\' . strtoupper($controller_name) . 'Controller';
                $controllerFile = MODX_BASE_PATH . 'assets/modules/webixtable/controller/' . strtolower($controller_name) . '.controller.php';
                break;
            case (!empty($tpl) && file_exists(MODX_BASE_PATH . 'assets/modules/webixtable/controller/' . strtolower($tpl) . '.controller.php')) :
                $controller = 'WebixTable\\' . strtoupper($tpl) . 'Controller';
                $controllerFile = MODX_BASE_PATH . 'assets/modules/webixtable/controller/' . strtolower($tpl) . '.controller.php';
                break;
            default:
                $controller = 'WebixTable\\MainController';
                $controllerFile = MODX_BASE_PATH . 'assets/modules/webixtable/controller/main.controller.php';
        }
        return $this->loadClass($controller, $controllerFile, $this->params);
    }
    
    protected function loadClass($className, $classFile, $params = array())
    {
        if (!class_exists($className)) {
            include_once($classFile);
        }
        return new $className($params);
    }
}

