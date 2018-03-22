define([
    'underscore',
    'ko',
    'uiComponent',
    'Magento_Ui/js/lib/collapsible',
    'jquery',
    'Mirasvit_Helpdesk/js/lib/jquery.MultiFile'
], function (_, ko, Component, Collapsible, $) {
    'use strict';

    return Component.extend({
        replyType: ko.observable('public'),
        defaults: {
            template: 'Mirasvit_Helpdesk/reply-area'
        },

        initialize: function () {
            this._super();
            this._bind();

            return this;
        },
        _bind: function () {
            var defaultClasses = '';
            $('body').on('helpdesk-switch-reply-type', function (e, v) {
                var textarea = $('[data-field=helpdesk-reply-field] textarea');
                if (!defaultClasses) {
                    defaultClasses = textarea.attr('class');
                }
                textarea.attr('class', defaultClasses + ' ' + v);
            });
            $('body').on('helpdesk-insert-quick-response', function (e, body) {
                if(typeof tinyMCE != 'undefined' && tinyMCE.activeEditor != null &&
                    document.getElementsByClassName('mceEditor').length) {
                    tinyMCE.activeEditor.setContent(tinyMCE.activeEditor.getContent() + body);

                } else {
                    var textarea = $('[data-field=helpdesk-reply-field] textarea');
                    var val = textarea.val();
                    if (val != '') {
                        val = val + "\n"
                    }
                    textarea.val(val + body);
                }
            });
        },
        afterFileInputRender: function () {
            $('.multi').MultiFile();

            var $replyArea = $('[data-field=helpdesk-reply-field] textarea');
            setInterval(function() {
                updateSaveBtn();
            }, 500);

            var updateSaveBtn = function () {
                var saveButton = $('#save-split-button-save-button,#save-split-button-button,#save-split-button-close-button');
                var editButton = $('#save-split-button-save-continue-button,#save-split-button-edit-button');

                if ($replyArea.val() == '') {
                    saveButton.html('Save');
                    editButton.html('Save & Continue Edit');
                } else {
                    saveButton.html('Save & Send Message');
                    editButton.html('Save, Send & Continue Edit');
                }
            };

            setTimeout(function() {
                updateTextarea();
                updateWysiwyg(); // wysiwyg does not show from first time
            }, 500);

            var updateTextarea = function () {
                $('body').trigger('helpdesk-switch-reply-type', $('[data-field="reply_type"]').val());
            };

            var updateWysiwyg = function () {
                if (!$('#reply_parent').length && $('#togglereply').length) {
                    $('#togglereply').click();
                }
            };
        }
    });
});
