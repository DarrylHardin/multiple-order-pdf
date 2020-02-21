<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace importantcoding\multipleorderpdf\elements\actions;
use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Json;

/**
 * Delete represents a Delete element action.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.0.0
 */
class ShipAction extends ElementAction
{
    // Properties
    // =========================================================================

    /**
     * @var string|null The confirmation message that should be shown before the elements get deleted
     */
    public $confirmationMessage = 'Generate PDF of these orders and change status to Shipped to Business?';

    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $successMessage = 'List Generated Successfully';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('app', 'Mark Orders Shipped and Generate PDF');
    }

    /**
     * @inheritdoc
     */
    public static function isDestructive(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        return $this->confirmationMessage;
    }


    /**
     * @inheritdoc
     */
    public function getTriggerHtml()
    {
        $type     = Json::encode(static::class);
        $template = "special/_pdf/truck-log";
        $message = "Changed through Mark Orders Shipped and Generate PDF";
        $newStatus = 13;
        $deliveryMethod = "Shipped to Business";
        $js       = <<<EOD
(function()
{
    var trigger = new SelectActionTrigger({
        type: {$type},
        batch: true,
        
        activate: function(\$selectedItems)
        {
            var idInputs = \$selectedItems
                .map(function(index, element) {
                    return '<input type="hidden" name="ids[]" value="' + \$(element).data('id') + '" />';
                })
                .get()
                .join('');
                
            var form = $('<form method="post" target="_blank" action="">' +
            '<input type="hidden" name="action" value="multiple-order-pdf/default/checked-in-pdf" />' +
            idInputs +
            '<input type="hidden" name="{csrfName}" value="{csrfValue}" />' +
            '<input type="hidden" name="template" value="{$template}" />' +
            '<input type="hidden" name="message" value="{$message}" />' +
            '<input type="hidden" name="newStatus" value="{$newStatus}" />' +
            '<input type="hidden" name="deliveryMethod" value="{$deliveryMethod}" />' +
            '<input type="submit" value="Submit" />' +
            '</form>');
            
            form.appendTo('body');
            form.submit();
            form.remove();
        }
    });
})();
EOD;

        $js = \str_replace([
            '{csrfName}',
            '{csrfValue}',
        ], [
            Craft::$app->getConfig()->getGeneral()->csrfTokenName,
            Craft::$app->getRequest()->getCsrfToken(),
        ], $js);

        Craft::$app->getView()->registerJs($js);
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        return true;
    }
}