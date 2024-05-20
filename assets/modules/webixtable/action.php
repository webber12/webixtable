<?php
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    define('MODX_API_MODE', true);
    define('IN_MANAGER_MODE', true);
    define('NO_TRACY', true);

    include_once(__DIR__ . "/../../../index.php");
    $modx->db->connect();
    if (empty ($modx->config)) {
        $modx->getSettings();
    }
    if (!isset($_SESSION['mgrValidated']) || !$modx->hasPermission('exec_module')) {
        die();
    }
    
    $out = '';
    $action = !empty($_REQUEST['action']) ? $modx->db->escape($_REQUEST['action']) : '';
    if (!empty($action)) {
        include_once 'controller/base.controller.php';
        $base = new \WebixTable\BaseController();
        $controller = $base->getController();
        if (is_callable(array($controller, 'ajax' . $action))) {
            $out = call_user_func(array($controller, 'ajax' . $action));
        }
    }
    header('Content-Type: application/json; charset=utf-8');
    echo $out;
    exit();
}
exit();

