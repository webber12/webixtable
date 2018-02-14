<?php
if(!isset($_SESSION['mgrValidated']) || !$modx->hasPermission('exec_module')){
    die();
}
$module_folder = 'webixtable';
$module_url = MODX_SITE_URL . 'assets/modules/' . $module_folder . '/';
$idField = trim($idField);
$display = (int)trim($display) > 0 ? (int)trim($display) : 10;
$fields = explode(',', str_replace(', ', ',', trim($fields)));
$fields_names = explode(',', str_replace(', ', ',', trim($fields_names)));
$fields_for_popup_editor = explode(',', str_replace(', ', ',', trim($fields_for_popup_editor)));
$fields_for_selector_filter = explode(',', str_replace(', ', ',', trim($fields_for_selector_filter)));
$fields_readonly = explode(',', str_replace(', ', ',', trim($fields_readonly)));
$fields_readonly[] = $idField;
$tpl = isset($tpl) && file_exists(MODX_BASE_PATH . '/assets/modules/' . $module_folder . '/tpl/' . trim($tpl) . '.tpl') ? trim($tpl) : 'main';
$inline_edit = isset($inline_edit) && $inline_edit == '1' ? 'true' : 'false';
$modal_edit_btn = isset($modal_edit) && $modal_edit == '1' ? '{ view:"button", type:"iconButton", icon:"pencil",  label:"Правка", width:110, click:"edit_row" },' : '';
$table = isset($table) ? trim($table) : false;
$field_for_date_filter = isset($field_for_date_filter) && trim($field_for_date_filter) != '' ? trim($field_for_date_filter) : false;

if (!function_exists(getSelectValues)) {
    function getSelectValues($modx, $field, $table) {
        $out = array();
        $i = 0;
        $out[$i] = array('id' => '', 'value' => '');
        if ($field && $table) {
            $q = $modx->db->query("SELECT DISTINCT(" . $field . ") as field FROM " . $modx->getFullTableName($table) . " ORDER BY field ASC");
            while ($row = $modx->db->getRow($q)) {
                $i++;
                $out[$i] = array('id' => $row['field'], 'value' => $row['field']);
            }
        }
        return $out;
    }
}

$columns = array();
foreach ($fields as $k => $field) {
    switch (true) {
        case in_array($field, $fields_for_popup_editor):
            $editor = 'popup';
            $formview = array('view' => 'textarea', 'label' => $fields_names[$k], 'name' => $field, 'height' => 100);
            break;
        case ($field == 'date' || preg_match('/^date_/', $field)):
            $editor = 'date';
            $formview = array('view' => 'datepicker', 'label' => $fields_names[$k], 'name' => $field, 'timepicker' => true);
            break;
        default:
            $editor = 'text';
            $formview = array('view' => 'text', 'label' => $fields_names[$k], 'name' => $field);
            break;
    }
    $tmp = array('id' => $field, header => array($fields_names[$k], array("content" => "serverFilter")), 'sort' => 'server', 'editor' => $editor, 'adjust' => true);
    if (in_array($field, $fields_for_selector_filter)) {
        $tmp['header'] = array($fields_names[$k], array("content" => "serverSelectFilter", "options" => getSelectValues($modx, $field, $table)));
    }
    if (in_array($field, $fields_readonly)) {
        unset($tmp['editor']);
        $formview['readonly'] = true;
    }
    $columns[] = $tmp;
    $form_fields[] = $formview;
}


$search_form_fields = array();
if ($field_for_date_filter) {
    $search_fields = array($field_for_date_filter => 'period');
    foreach ($search_fields as $key => $type) {
        $k = array_search($key, $fields);
        switch($type) {
            case 'period':
                $search_form_fields[] = array('view' => 'datepicker', 'label' => $fields_names[$k] . ' c ', 'name' => $key . '_from', 'labelWidth' => 110, 'stringResult' => true, 'format' => "%Y-%m-%d");
                $search_form_fields[] = array('view' => 'datepicker', 'label' => $fields_names[$k] . ' по ', 'name' => $key . '_to', 'labelWidth' => 110, 'stringResult' => true, 'format' => "%Y-%m-%d");
                break;
            default:
                break;
        }
    }
    $search_form_fields[] = array('view' => 'button', 'type' => 'iconButton', 'icon' => 'search', 'label' => 'Найти', 'click' => 'add_search');
}
$cols = json_encode($columns);
$module_id = (int)$_GET['id'];
$formfields = json_encode($form_fields);
$search_formfields = json_encode($search_form_fields);

$plh = array(
    'module_id' => $module_id,
    'module_url' => $module_url,
    'idField' => $idField,
    'display' => $display,
    'cols' => $cols,
    'name' => $name,
    'formfields' => substr($formfields, 1, -1),
    'inline_edit' => $inline_edit,
    'modal_edit_btn' => $modal_edit_btn,
    'table' => $table,
    'search_formfields' => $search_formfields,
    'add_search_form' => $field_for_date_filter ? 'search_form,' : ''
);

$tpl = file_get_contents($module_url . 'tpl/' . $tpl . '.tpl');
$output .= $modx->parseText($tpl, $plh);
echo $output;
