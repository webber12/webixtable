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
    </head>
    <body style="background-color: #fafafa;">
        <div id="wbx_table" style="padding-bottom:20px;"></div>
    
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
            webix.ui({
                container:"wbx_table",
                rows:[
                    { view:"template", type:"header", template:"[+name+]"},
                    { view:"toolbar", id:"mybar", elements:[
                        { view:"button", type:"iconButton", icon:"plus", label:"Добавить", width:110, click:"add_row"}, 
                        { view:"button", type:"iconButton", icon:"trash",  label:"Удалить", width:110, click:"del_row" }/*,
                        { view:"button", value:"Обновить", width:100, click:"refresh" }*/]
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
                            template : "{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}"
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
        function del_row(){
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
            $$("mydatatable").refresh();
        }
        function show_alert(text, level) {
            webix.alert(text, level, function(result){});
        }
        </script>
    </body>
</html>