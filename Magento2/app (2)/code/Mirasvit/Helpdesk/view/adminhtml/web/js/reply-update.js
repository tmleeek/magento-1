require([
    'jquery',
    'tinymce'
], function ($) {
    'use strict';

    $('#reply').val(draftText);
    var origText = $('#reply').val();

    function updateActivity() {
        if (!isAllowDraft) {
            return;
        }

        var text = -1;

        var currentText = '';
        if(tinyMCE.activeEditor) {
            currentText = tinyMCE.activeEditor.getContent();
        } else {
            currentText = $('#reply').val();
        }

        if (currentText != origText) {
            origText = currentText;
            text = origText;
        }
        new Ajax.Request(draftUpdateUrl, {
            method : "get",
            loaderArea: false,
            parameters : {ticket_id: draftTicketId, text: text},
            onSuccess : function(response) {
                draftText = text;
                if(response.responseText.indexOf('<head>') == -1) {
                    $('.helpdesk-notice').html(response.responseText);
                    if (response.responseText == '') {
                        $('.helpdesk-notice').hide();
                    } else {
                        $('.helpdesk-notice').show();
                    }
                }
            }
        });
    }

    var updateTimer = window.setInterval(updateActivity, draftDelayPeriod);

    tinyMCE.onAddEditor.add(function(obj, editor) {
        editor.onPostRender.add(function(ed, cm) {
            ed.setContent(draftText);
        });
        //editor.onRemove.add(function(ed) {
        //    alert('close');
        //});
        //editor.onEvent.add(function(ed, e) {
        //    console.debug('Editor event occured: ' + e.target.nodeName);
        //});
    });
});