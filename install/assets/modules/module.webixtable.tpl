//<?
/**
 * webixtable
 * 
 * Simple edit bd custom tables with webix.com - UI
 * 
 * @author      webber (web-ber12@yandex.ru)
 * @category    module
 * @version     0.1
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @guid webixtable
 * @internal    @properties &name=Заголовок модуля;text;&fields=Поля в таблице БД (comma separated);text;&fields_for_popup_editor=Поля, для которых нужно popup-редактирование;text;&fields_names=Названия колонок полей БД в модуле (comma separated);text;&idField=Уник.поле в БД;text;id&table=Имя таблицы в БД;text;&display=Показывать по;text;10
 * @internal    @modx_category Manager and Admin
 * @internal    @installset base, sample
 */
 
$module_url = MODX_SITE_URL . 'assets/modules/webixtable/';
$idField = trim($idField);
$display = (int)trim($display) > 0 ? (int)trim($display) : 10;
$fields = explode(',', str_replace(', ', ',', trim($fields)));
$fields_names = explode(',', str_replace(', ', ',', trim($fields_names)));
$fields_for_popup_editor = explode(',', str_replace(', ', ',', trim($fields_for_popup_editor)));

$columns = array();
foreach ($fields as $k => $field) {
    $tmp = array('id' => $field, header => array($fields_names[$k], array("content" => "serverFilter")), 'sort' => 'server', 'editor' => in_array($field, $fields_for_popup_editor) ? 'popup' : 'text', 'adjust' => true);
    if ($idField == $field) {
        unset($tmp['editor']);
    }
    $columns[] = $tmp;
}
$cols = json_encode($columns);
$module_id = (int)$_GET['id'];

$plh = array(
		'module_id' => $module_id,
		'module_url' => $module_url,
		'idField' => $idField,
		'display' => $display,
		'cols' => $cols,
		'name' => $name
);

$tpl = file_get_contents($module_url . 'tpl/main.tpl');
$output .= $modx->parseText($tpl, $plh);
echo $output;
