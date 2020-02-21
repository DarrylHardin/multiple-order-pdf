<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace importantcoding\multipleorderpdf\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\commerce\elements\Order;
use craft\commerce\Plugin;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Json;

/**
 * Class Update Order Status
 *
 * @property null|string $triggerHtml the action’s trigger HTML
 * @property string $triggerLabel the action’s trigger label
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class OrderAction extends ElementAction
{
    /**
     * @var int
     */
    public $orderStatusId;

    /**
     * @var string
     */
    public $orderConfirmation;


    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Plugin::t('Mark Special Orders Ordered');
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
    var trigger = new OrderActionTrigger({
        type: {$type},
        batch: true,
        activate: function(\$selectedItems)
        {
            Craft.elementIndex.setIndexBusy();
            var modal = new ImportantCoding.MultipleOrderPdf.UpdateOrderStatusModal(currentOrderStatus,orderStatuses, {
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
            $order->orderStatusId = 18;
            $order->setFieldValue('orderConfirmation', $this->orderConfirmation);
            Craft::$app->getElements()->saveElement($order);
        }

        return true;
    }
}