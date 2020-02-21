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
use craft\commerce\elements\Order;
/**
 * Delete represents a Delete element action.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.0.0
 */
class CheckinAction extends ElementAction
{
    // Properties
    // =========================================================================

    /**
     * @var string|null The confirmation message that should be shown before the elements get deleted
     */
    public $confirmationMessage = 'Check in these Special Orders?';

    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $successMessage = 'Orders Checked in';

    /**
     * @var int
     */
    public $orderStatusId;

    /**
     * @var string
     */
    public $message;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('app', 'Check in these orders?');
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
        $orderStatuses = Json::encode(array_values(Plugin::getInstance()->getOrderStatuses()->getAllOrderStatuses()));
        $type = Json::encode(static::class);

        $js = <<<EOT
(function()
{
    var trigger = new Craft.ElementActionTrigger({
        type: {$type},
        batch: true,
        activate: function(\$selectedItems)
        {
            Craft.elementIndex.setIndexBusy();
            var modal = new Craft.Commerce.UpdateOrderStatusModal(currentOrderStatus,orderStatuses, {
                onSubmit: function(data){
                   Craft.elementIndex.submitAction({$type}, data);
                   modal.hide();
                   return false;
                }
            });
        }
    });
})();
EOT;

        Craft::$app->getView()->registerJs($js);

        return null;
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $orders = $query->all();

        foreach ($orders as $order) {
            /** @var Order $order */
            $order->orderStatusId = $this->orderStatusId;
            $order->message = $this->message;
            Craft::$app->getElements()->saveElement($order);
        }

        return true;
    }
}