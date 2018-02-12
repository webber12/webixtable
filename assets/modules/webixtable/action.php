<?php

define('MODX_API_MODE', true);
define('IN_MANAGER_MODE', true);

include_once(__DIR__ . "/../../../index.php");
$modx->db->connect();
if (empty ($modx->config)) {
    $modx->getSettings();
}

if (!isset($_SESSION['mgrValidated']) || !$modx->hasPermission('exec_module')) {
    die();
}

//парсим свойства модуля на предмет нужных настроек
if (isset($_REQUEST['module_id']) && (int)$_REQUEST['module_id'] > 0) {
    $prop = $modx->db->getValue("SELECT properties FROM " . $modx->getFullTableName("site_modules") . " WHERE id=" . (int)$_REQUEST['module_id'] . " LIMIT 0,1");
    if ($prop) {
        $properties = $modx->parseProperties($prop);
        if (is_array($properties)) {
            extract($properties, EXTR_SKIP);
        }
    }
}
$idField = isset($idField) ? trim($idField) : false;
$fields = isset($fields) ? explode(',', str_replace(', ', ',', trim($fields))) : false;
$fields_names = isset($fields_names) ? explode(',', str_replace(', ', ',', trim($fields_names))) : false;
$table = isset($table) ? trim($table) : false;
$display = isset($display) && (int)$display > 0 ? (int)$display : 10;
$filterEq = isset($filterEq) ? explode(',', str_replace(', ', ',', trim($filterEq))) : false;

//$modx->logEvent(1,1,json_encode($_REQUEST),'REQUEST');

//начинаем...
$out = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
switch($action) {
    case 'update':
        $arr = array();
        foreach ($fields as $field) {
            if (isset($_REQUEST[$field])) {
                $arr[$field] = $modx->db->escape($_REQUEST[$field]);
            }
        }
        $opetarion = isset($_REQUEST['webix_operation']) ? $_REQUEST['webix_operation'] : '';
        switch ($opetarion) {
            case 'update':
                if (!empty($arr) && isset($arr[$idField]) && $arr[$idField] != '') {
                    foreach ($arr as $k => $v) {
                        if (preg_match('/^href_/', $k)) {//удаляем преобразованные в ссылки адреса
                            unset($arr[$k]);
                        }
                    }
                    $modx->db->update($arr, $modx->getFullTableName($table), "`" . $idField . "`='" . $arr[$idField] . "'");
                }
                break;
            case 'insert':
                if (!empty($arr) && isset($arr[$idField]) && $arr[$idField] != '') {
                    $modx->db->insert($arr, $modx->getFullTableName($table));
                } else if ($idField == 'id') {
                    $max = $modx->db->getValue("SELECT MAX(`" . $idField . "`) FROM " . $modx->getFullTableName($table));
                    $max = $max ? ($max + 1) : 1;
                    $modx->db->insert(array('id' => $max), $modx->getFullTableName($table));
                }
                break;
            case 'delete':
                if (!empty($arr) && isset($arr[$idField]) && $arr[$idField] != '') {
                    $modx->db->delete($modx->getFullTableName($table), "`" . $idField . "`='" . $arr[$idField] . "'");
                }
                break;
        }
        break;
        
    case 'list':
        $DLparams = array(
            'controller' => 'onetable',
            'table' => $table,
            'api' => implode(',', $fields),
            'JSONformat' => 'new',
            'idType' => 'documents',
            'idField' => $idField,
            'idType' => 'documents',
            'ignoreEmpty' => '1',
            'display' => $display,
            'prepare' => function($data, $modx, $_DL, $_extDocLister) {
                foreach ($data as $k => $v) {
                    if (preg_match('/^href_/', $k)) {
                        $v = MODX_SITE_URL . ltrim($v, '/');
                        $data[$k] = '<a href="' . $v . '" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>';
                    }
                }
                return $data;
            }
        );
        //имеем запрос с сервера
        if (isset($_REQUEST['continue']) && $_REQUEST['continue'] == 'true') {
            if (isset($_REQUEST['sort'])) {
                $sortBy = implode('', array_keys($_REQUEST['sort']));
                $sortDir = strtoupper(implode('', array_values($_REQUEST['sort'])));
                $orderBy = $sortBy . ' ' . $sortDir;
                $DLparams['orderBy'] = $orderBy;
            }
            if (isset($_REQUEST['start'])) {
                $DLparams['offset'] = (int)$_REQUEST['start'];
            }
            if (isset($_REQUEST['filter'])) {
                $tmp = array();
                foreach ($fields as $field) {
                    if (isset($_REQUEST['filter'][$field]) && $_REQUEST['filter'][$field] != "") {
                     if (in_array($field,$filterEq))
                      $tmp[] = "`" . $field . "` = '" . $modx->db->escape($_REQUEST['filter'][$field]) . "'";
                     else 
                      $tmp[] = "`" . $field . "` LIKE '%" . $modx->db->escape($_REQUEST['filter'][$field]) . "%'";
                    }
                }
                if (!empty($tmp)) {
                    $DLparams['addWhereList'] = implode(" AND ", $tmp);
                }
            }
        }
        $tmp = $modx->runSnippet("DocLister", $DLparams);
        
        $tmp2 = json_decode($tmp, TRUE);
        $rows = $tmp2['rows'];
        $total_count = $tmp2['total'];
        $itogo = array("data" => $rows, "pos" => (int)$_REQUEST['start'], "total_count" => $total_count);
        $out .= json_encode($itogo);
        break;
        
    case 'get_next':
        $max = $modx->db->getValue("SELECT MAX(`" . $idField . "`) FROM " . $modx->getFullTableName($table));
        $out .= $max ? ($max + 1) : 1;
        break;

    case 'get_row'://получаем данные для формы в модальное окно
        if (isset($_REQUEST['key']) && $_REQUEST['key'] != '') {
            $key = $modx->db->escape($_REQUEST['key']);
            $q = $modx->db->query("SELECT * FROM " . $modx->getFullTableName($table) . " WHERE `" . $idField . "`='" . $key . "' LIMIT 0,1");
            if ($modx->db->getRecordCount($q) == 1) {
                $row = $modx->db->getRow($q);
                foreach ($row as $k => $v) {
                    if (!in_array($k, $fields)) {
                        unset($row[$k]);
                    }
                }
                $out .= json_encode($row);
            }
        }
        break;

    case 'update_row'://обновляем данные из формы в модальном окне
        $arr = array();
        $resp = 'error';
        foreach ($fields as $field) {
            if (isset($_REQUEST[$field])) {
                $arr[$field] = $modx->db->escape($_REQUEST[$field]);
            }
        }
        if (!empty($arr) && isset($arr[$idField]) && $arr[$idField] != '') {
            $up = $modx->db->update($arr, $modx->getFullTableName($table), "`" . $idField . "`='" . $arr[$idField] . "'");
            if ($up) {
                $resp = 'ok';
            }
        }
        $out = $resp;
        break;

    default:
        break;
}

echo $out;
