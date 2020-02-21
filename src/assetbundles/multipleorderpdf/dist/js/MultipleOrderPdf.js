/**
 * Batch PDF Export plugin for Craft CMS
 *
 * Batch PDF Export JS
 *
 * @author    Superbig
 * @copyright Copyright (c) 2018 Superbig
 * @link      https://importantcoding.com
 * @package   MultipleOrderPdf
 * @since     1.0.0
 */
SelectActionTrigger = Craft.ElementActionTrigger.extend({
    init: function(settings) {
        this.setSettings(settings, Craft.ElementActionTrigger.defaults);
        var $triggers = $('[id^=' + settings.type.replace(/[\[\]\\]+/g, '-') + '-actiontrigger]');
        var $trigger = $triggers.first();

        if ($triggers.length > 1) {
            $triggers.each(function(index, element) {
                var $currentTrigger = $(element);
                if (settings.label === $(element).text()) {
                    $trigger = $currentTrigger
                }
            });
        }

        this.$trigger = $trigger;

        // Do we have a custom handler?
        if (this.settings.activate) {
            // Prevent the element index's click handler
            this.$trigger.data('custom-handler', true);

            // Is this a custom trigger?
            if (this.$trigger.prop('nodeName') === 'FORM') {
                this.addListener(this.$trigger, 'submit', 'handleTriggerActivation');
            }
            else {
                this.addListener(this.$trigger, 'click', 'handleTriggerActivation');
            }
        }

        this.updateTrigger();
        Craft.elementIndex.on('selectionChange', $.proxy(this, 'updateTrigger'));
    },
    defaults: {
        type: null,
        batch: true,
        validateSelection: null,
        activate: null,
        label: null,
    }
});

OrderActionTrigger = Craft.ElementActionTrigger.extend({
    init: function(settings) {
        ImportantCoding.MultipleOrderPdf.UpdateOrderStatusModal = Garnish.Modal.extend(
        {
            id: null,
            orderStatusId: null,
            originalStatus: null,
            currentStatus: null,
            originalStatusId: null,
            $statusSelect: null,
            $selectedStatus: null,
            $orderStatusIdInput: null,
            $message: null,
            $error: null,
            $updateBtn: null,
            $statusMenuBtn: null,
            $cancelBtn: null,
            init: function(currentStatus, orderStatuses, settings) {
                this.id = Math.floor(Math.random() * 1000000000);

                this.setSettings(settings, {
                    resizable: false
                });

                this.originalStatusId = currentStatus.id;
                this.currentStatus = currentStatus;

                var $form = $('<form class="modal fitted" method="post" accept-charset="UTF-8"/>').appendTo(Garnish.$bod);
                var $body = $('<div class="body"></div>').appendTo($form);
                var $inputs = $('<div class="content">' +
                    '<h2 class="first">' + Craft.t('commerce', "Update Order Status") + '</h2>' +
                    '</div>').appendTo($body);

                // // Build menu button
                // this.$statusSelect = $('<a class="btn menubtn" href="#"><span class="status ' + currentStatus.color + '"></span>' + currentStatus.name + '</a>').appendTo($inputs);
                // var $menu = $('<div class="menu"/>').appendTo($inputs);
                // var $list = $('<ul class="padded"/>').appendTo($menu);
                // var classes = "";
                // for (var i = 0; i < orderStatuses.length; i++) {
                //     if (this.currentStatus.id === orderStatuses[i].id) {
                //         classes = "sel";
                //     } else {
                //         classes = "";
                //     }
                //     $('<li><a data-id="' + orderStatuses[i].id + '" data-color="' + orderStatuses[i].color + '" data-name="' + orderStatuses[i].name + '" class="' + classes + '"><span class="status ' + orderStatuses[i].color + '"></span>' + orderStatuses[i].name + '</a></li>').appendTo($list);
                // }

                // this.$selectedStatus = $('.sel', $list);

                // Build message input
                this.$orderConfirmation = $('<div class="field">' +
                    '<div class="heading">' +
                    '<label>' + Craft.t('commerce', 'Confirmation Number') + '</label>' +
                    '</div>' +
                    '<div class="input ltr">' +
                    '<input type="text" class="text fullwidth" name="field["orderConfirmation"]">' +
                    '</div>' +
                    '</div>').appendTo($inputs);

                // Error notice area
                this.$error = $('<div class="error"/>').appendTo($inputs);

                // Footer and buttons
                var $footer = $('<div class="footer"/>').appendTo($form);
                var $mainBtnGroup = $('<div class="btngroup right"/>').appendTo($footer);
                this.$cancelBtn = $('<input type="button" class="btn" value="' + Craft.t('commerce', 'Cancel') + '"/>').appendTo($mainBtnGroup);
                this.$updateBtn = $('<input type="button" class="btn submit" value="' + Craft.t('commerce', 'Update') + '"/>').appendTo($mainBtnGroup);

                this.$updateBtn.addClass('disabled');

                // Listeners and
                this.$statusMenuBtn = new Garnish.MenuBtn(this.$statusSelect, {
                    onOptionSelect: $.proxy(this, 'onSelectStatus')
                });

                this.addListener(this.$cancelBtn, 'click', 'hide');
                this.addListener(this.$updateBtn, 'click', function(ev) {
                    ev.preventDefault();
                    if (!$(ev.target).hasClass('disabled')) {
                        this.updateStatus();
                    }
                });
                this.base($form, settings);
            },

            updateStatus: function() {
                var data = {
                    'confirmationNumber': this.$confirmationNumber.find('textarea[name="confirmationNumber"]').val(),
                };

                this.settings.onSubmit(data);
            },
            defaults: {
                onSubmit: $.noop
            }
        })
    }
});