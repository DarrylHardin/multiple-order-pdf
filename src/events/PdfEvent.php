<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace importantcoding\multipleorderpdf\events;

use craft\commerce\elements\Order;
use yii\base\Event;

/**
 * Class AddressEvent
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class PdfEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var Order
     */
    public $order;

    /**
     * @var array
     */
    public $orders;

    /**
     * @var string
     */
    public $option;

    /**
     * @var string
     */
    public $template;

    /**
     * @var array
     */
    public $variables;

    /**
     * @var string|null The rendered PDF
     */
    public $pdf;
}
