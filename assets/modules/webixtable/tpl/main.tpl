<!DOCTYPE HTML>
<html>
    <head>
    <link rel="stylesheet" href="media/style/common/font-awesome/css/font-awesome.min.css?v=4.7.0">
    <link href="https://fonts.googleapis.com/css?family=PT+Sans:400,400i,700&amp;subset=cyrillic" rel="stylesheet">
    <link rel="stylesheet" href="[+module_url+]skin/webix.css" type="text/css">
    <style>
        body.webix_full_screen{overflow:auto !important;}
        .webix_view.webix_pager{margin-bottom:30px;}
        .webix_cell{-webkit-transition: all .3s,-moz-transition: all .3s,-o-transition: all .3s,transition: all .3s}
        .webix_cell:nth-child(odd){background-color:#f6f8f8;}
        .webix_cell:hover{background-color: rgba(93, 109, 202, 0.16);}
    </style>
    <script src="[+module_url+]skin/webix.js" type="text/javascript"></script>
    <script src="//cdn.webix.com/site/i18n/ru.js" type="text/javascript" charset="utf-8"></script>
    </head>
    <body style="background-color: #fafafa;">
        <div id="wbx_table" style="padding-bottom:20px;"></div>
        <div id="wbx_pp" style="padding-bottom:20px;width:90%;"></div>
    
        <script type="text/javascript" charset="utf-8">

        webix.ready(function() {
            webix.editors.$popup = {
                date:{
                    view:"popup",
                    body:{ 
                        view:"calendar", 
                        timepicker:true, 
                        timepickerHeight:50,
                        width: 320, 
                        height:300
                    }
                },
                text:{
                    view:"popup", 
                    body:{view:"textarea", width:350, height:150}
                }
            };
            var form = {
                view:"form",
                id:"myform",
                borderless:true,
                elements: [
                    [+formfields+] ,
                    { margin:5, cols:[
                        { view:"button", value: "Submit", type:"form", click:submit_form},
                        { view:"button", value: "Cancel", click: function (elementId, event) {
                            this.getTopParentView().hide();
                        }}
                    ]},
                    {rows : [
                        {template:"The End:)", type:"section"}
                    ]}
                ],
                rules:{},
                elementsConfig:{
                    labelPosition:"top",
                },
                height:500,
                scroll:"y"
            };
            webix.ui({
                view:"window",
                id:"win2",
                width:500,
                height:500,
                position:"center",
                modal:true,
                head:{
                    view:"toolbar", margin:-4, cols:[
                        {view:"label", label: "Редактирование данных" },
                        {view:"icon", icon:"times-circle",
                            click:"$$('win2').hide();"}
                        ]
                },
                body:form
            });

            webix.ui({
                container:"wbx_table",
                rows:[
                    { view:"template", type:"header", template:"[+name+]"},
                    { view:"toolbar", id:"mybar", elements:[
                        { view:"button", type:"iconButton", icon:"plus", label:"Добавить", width:110, click:"add_row"}, 
                        { view:"button", type:"iconButton", icon:"trash",  label:"Удалить", width:110, click:"del_row" },
                        [+modal_edit_btn+]
                        { view:"button", value:"Обновить", width:100, click:"refresh" }]
                    },
                    { view:"datatable",
                        autoheight:true,select:"row",resizeColumn:true,
                        id:"mydatatable",
                        editable:true,
                        editaction: "dblclick",
                        datafetch:[+display+],
                        navigation:true,
                        columns : [+cols+] ,
                        pager:{   
                            size : [+display+],
                            group : 5,
                            template : "{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
                            container:"wbx_pp"
                        },
                        url: "[+module_url+]action.php?action=list&module_id=[+module_id+]",
                        save: "[+module_url+]action.php?action=update&module_id=[+module_id+]",
                        delete: "[+module_url+]action.php?action=delete&module_id=[+module_id+]"
                    }
                ]
            });
        });

        function add_row() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '[+module_url+]action.php?action=get_next&module_id=[+module_id+]', false);
            xhr.send();
            if (xhr.status != 200) {
                  show_alert(xhr.status + ': ' + xhr.statusText, "alert-warning");
            } else {
                var ins = {'[+idField+]' : xhr.responseText};
                $$("mydatatable").add(ins, 0);
            }
        }
        function del_row() {
            var selected = $$("mydatatable").getSelectedId();
            if (typeof(selected) !== "undefined") {
                webix.confirm("Вы уверены, что хотите удалить выбранную строку?", "confirm-warning", function(result){
                    if (result === true) {
                        $$("mydatatable").remove(selected);
                    }
                });
            } else {
                show_alert("Вы не выбрали строку для удаления", "alert-warning");
            }
        }
        function refresh() {
            $$("mydatatable").clearAll();
            $$("mydatatable").load($$("mydatatable").config.url);
        }
        function edit_row(){
            var selected = $$("mydatatable").getSelectedId();
            if (typeof(selected) !== "undefined") {
                $$("win2").getBody().clear();
                $$("win2").show();
                $$("myform").load("[+module_url+]action.php?action=get_row&module_id=[+module_id+]&key=" + selected);
            } else {
                show_alert("Вы не выбрали строку для редактирования", "alert-warning");
            }
        }
        function submit_form() {
            webix.ajax().post("[+module_url+]action.php?action=update_row&module_id=[+module_id+]", $$("myform").getValues(), function(text, data, xhr){ 
                if (text == 'ok') {
                    refresh();
                    show_alert('Изменения успешно сохранены', "alert-success");
                } else {
                    show_alert('Ошибка на сервере, попробуйте позднее', "alert-warning");
                }
            });
        }
        function show_alert(text, level) {
            webix.alert(text, level, function(result){});
        }
        </script>
    </body>
</html>