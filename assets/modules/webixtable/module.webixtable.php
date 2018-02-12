<?php
if(!isset($_SESSION['mgrValidated']) || !$modx->hasPermission('exec_module')){
    die();
}
$module_url = MODX_SITE_URL . 'assets/modules/webixtable/';
$idField = trim($idField);
$display = (int)trim($display) > 0 ? (int)trim($display) : 10;
$fields = explode(',', str_replace(', ', ',', trim($fields)));
$fields_names = explode(',', str_replace(', ', ',', trim($fields_names)));
$fields_for_popup_editor = explode(',', str_replace(', ', ',', trim($fields_for_popup_editor)));
$tpl = isset($tpl) && file_exists(MODX_BASE_PATH . '/assets/modules/webixtable/tpl/' . trim($tpl) . '.tpl') ? trim($tpl) : 'main';
$inline_edit = isset($inline_edit) && $inline_edit == '1' ? 'true' : 'false';
$modal_edit_btn = isset($modal_edit) && $modal_edit == '1' ? '{ view:"button", type:"iconButton", icon:"pencil",  label:"Правка", width:110, click:"edit_row" },' : '';
$table = isset($table) ? trim($table) : false;

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
    if ($idField == $field) {
        unset($tmp['editor']);
        $formview['readonly'] = true;
    }
    $columns[] = $tmp;
    $form_fields[] = $formview;
}
$cols = json_encode($columns);
$module_id = (int)$_GET['id'];
$formfields = json_encode($form_fields);

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
    'table' => $table
);

$tpl = file_get_contents($module_url . 'tpl/' . $tpl . '.tpl');
$output .= $modx->parseText($tpl, $plh);
echo $output;
