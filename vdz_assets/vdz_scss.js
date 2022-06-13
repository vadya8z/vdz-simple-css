(function ($) {
    $(document).on('ready', function () {
        var $vdz_simple_css_code_textarea = $("#vdz_simple_css_code_id");
        var vdz_simple_css_code_textarea_value = $vdz_simple_css_code_textarea.val();
        var vdz_codemirror_editor = CodeMirror.fromTextArea(document.getElementById("vdz_simple_css_code_id"), {
            // lineNumbers: true, //Траблы со стилями
            // styleActiveLine: true,
            // lineWrapping: true,
            value: vdz_simple_css_code_textarea_value,
            extraKeys: {"Ctrl-Space": "autocomplete"},
            theme: "twilight",
            mode: {name: "css"}
        });
        //При добавлении новых строк - вносим изменения в поле и запускаем триггер события для смены вида на экране
        vdz_codemirror_editor.on("viewportChange", function(vdz_codemirror_editor, change) {
            $vdz_simple_css_code_textarea.val(vdz_codemirror_editor.getValue());
            $vdz_simple_css_code_textarea.trigger('change');
        });

    });
})(jQuery);

