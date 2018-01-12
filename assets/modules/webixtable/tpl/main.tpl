<!DOCTYPE HTML>
<html>
    <head>
    <link rel="stylesheet" href="media/style/common/font-awesome/css/font-awesome.min.css?v=4.7.0">
    <link rel="stylesheet" href="[+module_url+]skin/webix.css" type="text/css">
    <script src="[+module_url+]skin/webix.js" type="text/javascript"></script>
    <script src="[+module_url+]skin/skin.js" type="text/javascript"></script>
    </head>
    <body style="background-color: #fafafa;">
        <div id="wbx_table" style="padding-bottom:20px;"></div>
    
        <script type="text/javascript" charset="utf-8">

        webix.ready(function(){
            webix.ui({
                container:"wbx_table",
                rows:[
                    { view:"template", type:"header", template:"[+name+]"},
                    { view:"toolbar", id:"mybar", elements:[
                        { view:"button", value:"Добавить", width:100, click:"add_row"}, 
                        { view:"button", value:"Удалить", width:100, click:"del_row" },
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