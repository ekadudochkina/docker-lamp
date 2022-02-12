<?php
/* @var $this BaseController */
?>
<style>
    .less-panel {
        font-size: 40px;
        position: fixed;
        left: 3px;
        z-index: 300;
        color: #ea5547;
        top: 50%;
        background: white;
        border: 1px solid #dde3e8;
        border-radius: 5px;
        padding: 5px;
        line-height: 40px;
        width: 40px;
        box-sizing: content-box;
        text-align: center;
        cursor: pointer;
        height: 40px;
        opacity: 0;
    }
    .less-panel:hover{
        transition-property: "opacity";
        transition-duration: 0.5s;
        opacity:1;
    }
    .less-panel .colors {
        display:none;
        width: 150px;
        background: white;
        position: absolute;
        border: 1px solid #dde3e8;
        left: 55px;
        border-radius: 5px;
        top: 0px;
    }
    .less-panel .colors .color {
        width: 20px;
        height: 20px;
        display: inline-block;
        border: 1px solid #dde3e8;
        margin: 5px;
        border-radius: 5px;
    }
</style>
<div class="less-panel">
    <div class="icon">
        <i class="fa fa-gear"></i>
    </div>
    <div class="colors">

    </div>
</div>

<script>


    $(document).on("ready", function () {
//        $("body > .outer *").each(function(i,el){
//            $(el).attr("contenteditable","true");
//        });
        var interval = window.setInterval(function () {
            less.refresh(true);
            //Сначала один раз даем обновить, а то вдруг ошибка будет?
            less.options.logLevel = 2;
        }, 3000);

        $(".less-panel .icon").on("click", function () {
            $(".less-panel .colors").toggle();
        });

        getLessVars = function (lessStr) {

            lines = lessStr.split('\n')
            lessVars = {}
            for (var i = 0; i < lines.length; i++)
            {
                var line = lines[i];
                if (line.indexOf('@') == 0)
                {
                    keyVar = line.split(';')[0].split(':')
                    lessVars[keyVar[0]] = keyVar[1].trim()
                }

            }
            return lessVars;
        }

        //Получаем текст скрипта
        var href = null;
        $("link").each(function (i, el) {
            if ($(el).attr("href").indexOf(".less") > 0)
                href = $(el).attr("href");
        });
        if (href)
            $.ajax(href).done(function (css) {
                
                //Превращаем цвета
                var vars = getLessVars(css);

                for (var name in vars)
                {
                   // console.log(name, name.indexOf("color"));
                    if (name.indexOf("color") === -1)
                        continue;
                    var html = "<div class='color' ></div>";
                    html = $(html);
                    var input = $("<input type='hidden' id='value" + name + "' />");
                    //picker.fromHSV(360 / 100 * i, 100, 100)
                    $(".less-panel .colors").append(html);
                    $(".less-panel .colors").append(input);

                    var picker = new jscolor(html.get(0), {valueElement: input.get(0), value: vars[name]});
                    input.on("change", function (input, name, value) {
                        clearInterval(interval);
                        var obj = {};
                        obj[name] = "#" + input.val();
                       // console.log(arguments, obj);
                        less.modifyVars(obj);
                    }.bind(this, input, name, vars[name]));
                    //console.log(picker);
                }
            })
    })
</script>