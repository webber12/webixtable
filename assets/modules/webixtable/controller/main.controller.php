<?php namespace WebixTable;

include_once ("base.controller.php");

class MainController extends \WebixTable\BaseController
{

    protected $modal_edit_btn_text = '{ view:"button", type:"icon", icon:"wxi-pencil", css:"webix_primary", label:"Правка", width:110, click:"edit_row" },';

    protected $inline_fields_width_default = 150;

    protected $inline_fields_width = ['id' => 80, 'title' => 250];

    protected $inline_fields_adjust = false;
    
    protected $ckeditor_height = 250;

    public function renderModulePage()
    {
        $output = '';

        //some default inputs
        if (count($this->getCfg('fields_modalform')) == 0) {
            $this->cfg['fields_modalform'] = $this->getCfg('fields');
            $this->cfg['fields_modalform_names'] = $this->getCfg('fields_names');
        }
        $tpl = $this->getCfg('tpl');
        $tpl = file_exists($this->module_folder . '/tpl/' . $tpl . '.tpl') ? $tpl : 'main';
        $inline_edit = $this->getCfg('inline_edit') == '1' ? 'true' : 'false';
        $modal_edit_btn = $this->getCfg('modal_edit') == '1' ? $this->modal_edit_btn_text : '';
        $context_edit_btn = $this->getCfg('modal_edit') == '1' ? '"Правка", ' : '';

        //render fields info
        $columns = $this->renderColumnsInfo();
        $form_fields = $this->renderModalFormInfo();
        $search_form_fields = $this->renderSearchFields();

        //transform data for tpl
        $cols = json_encode($columns);
        $module_id = (int)$_GET['id'];
        $formfields = json_encode($form_fields);
        $search_formfields = json_encode($search_form_fields);

        //tpl placeholders array
        $plh = array(
            'module_id' => $module_id,
            'module_url' => $this->module_url,
            'manager_theme_mode' => $this->modx->getConfig('manager_theme_mode') == 4 ? 'darkness' : '',
            'name' => $this->getCfg('name'),
            'table' => $this->getCfg('table'),
            'inline_edit' => $inline_edit,
            'modal_edit_btn' => $modal_edit_btn,
            'context_edit_btn' => $context_edit_btn,
            'idField' => $this->getCfg('idField'),
            'display' => $this->getCfg('display'),
            'cols' => $cols,
            'formfields' => substr($formfields, 1, -1),
            'search_formfields' => $search_formfields,
            'add_search_form' => !empty($this->getCfg('field_for_date_filter')) ? ($this->getCfg('field_for_date_filter') ? 'search_form,' : '') : ''
        );

        //render tpl with placeholders
        $output .= $this->renderTpl($tpl, $plh);
        return $output;
    }


/** some specific methods **/

    protected function setCfg()
    {
        if (!empty($this->params) && is_array($this->params)) {
            foreach ($this->params as $k => $v) {
                $this->cfg[$k] = $this->getParam($k, isset($this->cfg_defaults[$k]) ? $this->cfg_defaults[$k] : '', in_array($k, $this->cfg_arrays));
            }
        }
        return $this;
    }
    
    protected function getCfg($key)
    {
        $value = !empty($this->cfg[$key]) ? $this->cfg[$key] : (!empty($this->cfg_defaults[$key]) ? $this->cfg_defaults[$key] : (in_array($key, $this->cfg_arrays) ? array() : ''));
        $data = $this->prepare(array($key => $value), 'OnGetCfg');
        return $data[$key];
    }
    
    protected function getSelectValues($field, $table)
    {
        $out = array();
        $i = 0;
        $out[$i] = array('id' => '', 'value' => '');
        if (!empty($field) && !empty($table)) {
            $q = $this->modx->db->query("SELECT DISTINCT(" . $field . ") as field FROM " . $this->modx->getFullTableName($table) . " ORDER BY field ASC");
            while ($row = $this->modx->db->getRow($q)) {
                $i++;
                $out[$i] = array('id' => $row['field'], 'value' => $row['field']);
            }
        }
        return $out;
    }
    
    protected function getTable()
    {
        return $this->modx->getFullTableName($table = $this->getCfg('table'));
    }

/** end some specific methods **/


/** render module tpl methods **/

    protected function renderColumnsInfo()
    {
        $columns = array();
        foreach ($this->getCfg('fields') as $k => $field) {
            $editor = $this->getFieldEditor($field);
            $tmp = array('id' => $field, 'header' => array($this->getCfg('fields_names')[$k], array("content" => "serverFilter")), 'sort' => 'server', 'editor' => $editor,  'width' => $this->inline_fields_width_default, 'adjust' => $this->inline_fields_adjust);
            if (in_array($field, $this->getCfg('fields_for_selector_filter'))) {
                $tmp['header'] = array($this->getCfg('fields_names')[$k], array("content" => "serverSelectFilter", "options" => $this->getSelectValues($field, $this->getCfg('table'))));
            }
            if (in_array($field, $this->getCfg('fields_readonly'))) {
                unset($tmp['editor']);
            }
            if (!empty($this->inline_fields_width[$field])) {
                $tmp['width'] = $this->inline_fields_width[$field];
            }
            $columns[] = $tmp;
        }
        $columns = $this->prepare($columns, 'OnAfterRenderColumns');
        return $columns;
    }
    
    protected function renderSearchFields()
    {
        $search_form_fields = array();
        if (!empty($this->getCfg('field_for_date_filter'))) {
            $search_fields = array($this->getCfg('field_for_date_filter') => 'period');
            foreach ($search_fields as $key => $type) {
                $k = array_search($key, $this->getCfg('fields'));
                switch($type) {
                    case 'period':
                        $search_form_fields[] = array('view' => 'datepicker', 'label' => $this->getCfg('fields_names')[$k] . ' c ', 'name' => $key . '_from', 'labelWidth' => 110, 'stringResult' => true, 'format' => "%Y-%m-%d");
                        $search_form_fields[] = array('view' => 'datepicker', 'label' => $this->getCfg('fields_names')[$k] . ' по ', 'name' => $key . '_to', 'labelWidth' => 110, 'stringResult' => true, 'format' => "%Y-%m-%d");
                        break;
                    default:
                        break;
                }
            }
            $search_form_fields[] = array('view' => 'button', 'type' => 'iconButton', 'icon' => 'search', 'label' => 'Найти', 'click' => 'add_search');
        }
        $search_form_fields = $this->prepare($search_form_fields, 'OnAfterRenderSearchFields');
        return $search_form_fields;
    }
    
    protected function renderModalFormInfo()
    {
        $form_fields = array();
        foreach ($this->getCfg('fields_modalform') as $k => $field) {
            $formview = $this->getFormViewInModal($field, $k);
            if (in_array($field, $this->getCfg('fields_readonly'))) {
                $formview['readonly'] = true;
            }
            $form_fields[] = $formview;
        }
        $form_fields = $this->prepare($form_fields, 'OnAfterRenderModalForm');
        return $form_fields;
    }
    
    protected function renderTpl($tpl, $plh)
    {
        $output = '';
        $tpl = file_get_contents($this->module_folder . 'tpl/' . $tpl . '.tpl');
        $output .= $this->modx->parseText($tpl, $plh);
        return $output;
    }
    
    protected function getFieldEditor($field)
    {
        switch (true) {
            case in_array($field, $this->getCfg('fields_for_popup_editor')):
                $editor = 'popup';
                break;
            case ($field == 'date' || preg_match('/^date_/', $field) || preg_match('/(.*)_date$/', $field)):
                $editor = 'date';
                break;
            default:
                $editor = 'text';
                break;
            }
        return $editor;
    }
    
    protected function getFormViewInModal($field, $k)
    {
        switch (true) {
            case in_array($field, $this->getCfg('fields_for_popup_editor')):
                $formview = array('view' => 'ckeditor5', 'label' => $this->getCfg('fields_modalform_names')[$k], 'name' => $field, 'config' => array(), 'height' => $this->ckeditor_height);
                break;
            case ($field == 'date' || preg_match('/^date_/', $field) || preg_match('/(.*)_date$/', $field)):
                $formview = array('view' => 'datepicker', 'label' => $this->getCfg('fields_modalform_names')[$k], 'name' => $field, 'timepicker' => true);
                break;
            default:
                $formview = array('view' => 'text', 'label' => $this->getCfg('fields_modalform_names')[$k], 'name' => $field);
                break;
        }
        return $formview;
    }

/** end render module tpl methods **/



/** ajax methods **/

    protected function makeListFilter($field)
    {
        switch (true) {
            case (in_array($field, $this->getCfg('fields_for_selector_filter'))):
                $filter = "`" . $field . "`='" . $this->modx->db->escape($_REQUEST['filter'][$field]) . "'";
                break;
            case (in_array($field, array('id', 'rid'))):
                $filter = "`" . $field . "`='" . $this->modx->db->escape($_REQUEST['filter'][$field]) . "'";
                break;
            default:
                $filter = "`" . $field . "` LIKE '%" . $this->modx->db->escape($_REQUEST['filter'][$field]) . "%'";
                break;
        }
        return $filter;
    }

    protected function makeListDateFilter($addwhere)
    {
        $field_for_date_filter = $this->getCfg('field_for_date_filter');
        if (isset($_REQUEST[$field_for_date_filter . '_from']) && $_REQUEST[$field_for_date_filter . '_from'] != '') {
            $_from = date("Y-m-d", strtotime($_REQUEST[$field_for_date_filter . '_from'])) . " 00:00:00";
            $addwhere[] = "`" . $field_for_date_filter . "`>='" . $_from . "' ";
        }
        if (isset($_REQUEST[$field_for_date_filter . '_to']) && $_REQUEST[$field_for_date_filter . '_to'] != '') {
            $_to = date("Y-m-d", strtotime($_REQUEST[$field_for_date_filter . '_to'])) . " 23:59:59";
            $addwhere[] = "`" . $field_for_date_filter . "`<= '" . $_to . "' ";
        }
        return $addwhere;
    }

    public function ajaxList()
    {
        $DLparams = array(
            'controller' => 'onetable',
            'table' => $this->getCfg('table'),
            'api' => implode(',', $this->getCfg('fields')),
            'JSONformat' => 'new',
            'idType' => 'documents',
            'idField' => $this->getCfg('idField'),
            'idType' => 'documents',
            'ignoreEmpty' => '1',
            'display' => $this->getCfg('display'),
            'prepare' => array($this, 'DLprepare')
        );
        $addwhere = array();
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
                foreach ($this->getCfg('fields') as $field) {
                    if (isset($_REQUEST['filter'][$field]) && !empty($_REQUEST['filter'][$field]) && $_REQUEST['filter'][$field] != "") {
                        $addwhere[] = $this->makeListFilter($field);
                    }
                }
            }
        }
        if (!empty($this->getCfg('field_for_date_filter'))) {
            $addwhere = $this->makeListDateFilter($addwhere);
        }
        if (!empty($addwhere)) {
            $DLparams['addWhereList'] = implode(" AND ", $addwhere);
        }
        $tmp = $this->modx->runSnippet("DocLister", $DLparams);
        $tmp2 = json_decode($tmp, TRUE);
        $rows = $tmp2['rows'];
        $total_count = $tmp2['total'];
        $itogo = array("data" => $rows, "pos" => (int)$_REQUEST['start'], "total_count" => $total_count);
        return json_encode($itogo);
    }
    
    public function ajaxUpdate()
    {
        $arr = array();
        $idField = $this->getCfg('idField');
        foreach ($this->getCfg('fields') as $field) {
            if (isset($_REQUEST[$field])) {
                $arr[$field] = $this->modx->db->escape($_REQUEST[$field]);
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
                    $arr = $this->prepare($arr, 'OnBeforeUpdateInline');
                    $this->modx->db->update($arr, $this->getTable(), "`" . $idField . "`='" . $arr[$idField] . "'");
                }
                break;
            case 'insert':
                if (!empty($arr) && isset($arr[$idField]) && $arr[$idField] != '') {
                    $this->modx->db->insert($arr, $this->getTable());
                } else if ($idField == 'id') {
                    $max = $this->modx->db->getValue("SELECT MAX(`" . $idField . "`) FROM " . $this->getTable());
                    $max = $max ? ($max + 1) : 1;
                    $this->modx->db->insert(array('id' => $max), $this->getTable());
                }
                break;
            case 'delete':
                if (!empty($arr) && isset($arr[$idField]) && $arr[$idField] != '') {
                    $this->modx->db->delete($this->getTable(), "`" . $idField . "`='" . $arr[$idField] . "'");
                }
                break;
        }
    }
    
    public function ajaxGetNext()
    {
        $max = $this->modx->db->getValue("SELECT MAX(`" . $this->getCfg('idField') . "`) FROM " . $this->getTable());
        return json_encode(array('max' => ($max ? ($max + 1) : 1)));
    }
    
    public function ajaxGetRow()
    {
        //получаем данные для формы в модальное окно.
        $out = '';
        if (!empty($_REQUEST['key'])) {
            $key = $this->modx->db->escape($_REQUEST['key']);
            $q = $this->modx->db->query("SELECT * FROM " . $this->getTable() . " WHERE `" . $this->getCfg('idField') . "`='" . $key . "' LIMIT 0,1");
            if ($this->modx->db->getRecordCount($q) == 1) {
                $row = $this->modx->db->getRow($q);
                foreach ($row as $k => $v) {
                    if (!in_array($k, $this->getCfg('fields_modalform'))) {
                        unset($row[$k]);
                    }
                }
                $row = $this->prepare($row, 'OnBeforeRenderModalData');
                $out .= json_encode($row);
            }
        }
        return $out;
    }
    
    public function ajaxUpdateRow()
    {
        //обновляем данные из формы в модальном окне
        $arr = array();
        $resp = 'error';
        $idField = $this->getCfg('idField');
        foreach ($this->getCfg('fields_modalform') as $field) {
            if (isset($_REQUEST[$field])) {
                $arr[$field] = $this->modx->db->escape($_REQUEST[$field]);
            }
        }
        $arr = $this->prepare($arr, 'OnBeforeUpdateModal');
        if (!empty($arr[$idField]) || (isset($arr[$idField]) && $arr[$idField] === 0)) {
            $up = $this->modx->db->update($arr, $this->getTable(), "`" . $idField . "`='" . $arr[$idField] . "'");
            if ($up) {
                $resp = 'ok';
            }
        }
        $out = $resp;
        return $out;
    }

/** end ajax methods **/



/** prepare methods **/

    protected function prepare($data, $mode = 'OnBeforeListingData')
    {
        //defaults
        /*
        /
        / OnGetCfg - получаем массив $key=>value из конфига
        / OnAfterRenderColumns - дескрипторы колонок в таблице
        / OnAfterRenderSearchFields - дексрипторы полей в модальной форме
        / OnAfterRenderSearchFields - дексрипторы полей для поиска
        / OnBeforeListingData - данные до вывода в таблицу
        / OnBeforeRenderModalData - данные до вывода в модальную форму
        / OnBeforeUpdateInline - данные перед сохранением из таблицы
        / OnBeforeUpdateModal - данные перед сохранением из модального окна
        / to invoke prepare, just call $this->prepare($data, $mode)
        / and suggest, that you`ve create method 'invoke' . $mode($data)
        / that returns $data after work
        /
        */
        if (is_callable(array($this, 'invoke' . $mode))) {
            call_user_func(array($this, 'invoke' . $mode), $data);
        }
        return $data;
    }

    public function DLprepare(array $data = array(), $modx, $_DL, $_extDocLister) {
        return $this->prepare($data);
    }

    public function invokeOnBeforeListingData($data)
    {
        foreach ($data as $k => $v) {
            if (preg_match('/^href_/', $k) && !empty($v)) {
                $v = $this->modx->getConfig('site_url') . ltrim($v, '/');
                $data[$k] = '<a href="' . $v . '" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>';
            }
        }
        return $data;
    }
/** end prepare methods **/

}

