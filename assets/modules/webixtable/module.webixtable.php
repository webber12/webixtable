<?php
if(!isset($_SESSION['mgrValidated']) || !$modx->hasPermission('exec_module')){
    die();
}

include_once 'controller/base.controller.php';
$base = new \WebixTable\BaseController($params);
$controller = $base->getController();
echo $controller->makeForm();
