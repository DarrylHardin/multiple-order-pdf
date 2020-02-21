<?php
/**
 * Multiple Order PDF plugin for Craft CMS 3.x
 *
 * Put multiple selected orders on a single PDF page, the order will be pushed to next page if page break would split.
 *
 * @link      https://importantcoding.com
 * @copyright Copyright (c) 2019 importantcoding
 */

namespace importantcoding\multipleorderpdf\controllers;

use importantcoding\multipleorderpdf\MultipleOrderPdf;
use importantcoding\multipleorderpdf\services\MySms;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\commerce\controllers\BaseFrontEndController;
use craft\commerce\elements\Order;
use craft\web\Controller;
use craft\commerce\events\OrderStatusEvent;
use craft\commerce\services\OrderHistories;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\RangeNotSatisfiableHttpException;
use yii\web\Response;

/**
 * DefaultController Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    importantcoding
 * @package   MultipleOrderPdf
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index', 'do-something'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/multiple-order-pdf/default-controller
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $ids      = Craft::$app->getRequest()->getRequiredParam('ids');
        $template = "special/_pdf/print-form";

        if (empty($ids)) {
            throw new InvalidConfigException('No order ids provided');
        }

        $orders = Order::find()
                       ->limit(null)
                       ->id($ids)
                       ->all();

        // return $this->renderTemplate('special-orders/pdf/print-form', $orders);

        $output   = MultipleOrderPdf::$plugin->multipleOrderPdfService->generatePdfs($orders, $ids, $template);
        $filename = $output['filename'];

        Craft::$app->getResponse()->sendContentAsFile($output['output'], $filename, [
            'mimeType' => 'application/pdf',
        ]);

        Craft::$app->end();
        
    }

    public function actionCheckedInPdf(): Response
    {
        // $number = Craft::$app->getRequest()->getQueryParam('number');
        $option = Craft::$app->getRequest()->getQueryParam('option', '');
        $ids      = Craft::$app->getRequest()->getRequiredParam('ids');
        $template      = Craft::$app->getRequest()->getRequiredParam('template');
        $message      = Craft::$app->getRequest()->getRequiredParam('message');
        $newStatus      = Craft::$app->getRequest()->getRequiredParam('newStatus');
        $deliveryMethod      = Craft::$app->getRequest()->getRequiredParam('deliveryMethod');
       
        if (empty($ids)) {
            throw new InvalidConfigException('No order ids provided');
        }
        
        $orders = Order::find()
                       ->limit(null)
                       ->id($ids)
                       ->all();
        /**
         * Check to see if statusId is correct. Allow to continue through if not, but do not update order status Id
         */
        $variables = [];
        $variables['deliveryMethod'] = $deliveryMethod;
        foreach($orders as $order)
        {
            // if($order->orderStatusId == 18)
            // {  
                $variables['business'] = $order->getFieldValue('businessSpecialOrder');
                $order->orderStatusId = $newStatus;
                $order->message = $message;
                Craft::$app->getElements()->saveElement($order);
            // }
        }
        // if (!$number) {
        //     throw new HttpInvalidParamException('Order number required');
        // }

        // $order = Plugin::getInstance()->getOrders()->getOrderByNumber($number);

        // if (!$orders) {
        //     throw new HttpException('404','Order not found');
        // }
        $pdf = MultipleOrderPdf::$plugin->multipleOrderPdfService->renderPdfForOrder($orders, $option, $template, $variables);
        // $pdf = Plugin::getInstance()->getPdf()->renderPdfForOrder($orders, $option);
        // $filenameFormat = Plugin::getInstance()->getSettings()->orderPdfFilenameFormat;

        // $fileName = $this->getView()->renderObjectTemplate($filenameFormat, $orders);

        // if (!$fileName) {
        //     $fileName = 'Order-' . $order->number;
        // }

        // //API Key
        // $api_key = 'v92NHVfGd_hWTign_1O65A';

        // //initialize class with apiKey and AuthToken(if available)
        // $mysms = new mysms($api_key);

        // //lets login user to get AuthToken
        // // $login_data = array('msisdn' => '18503540421', 'password' => 'haha69XD');

        // $mysms->singleCall();  //providing REST type(json/xml), resource from http://api.mysms.com/index.html and POST data
        $fileName = "SpecialOrder" . " " . $deliveryMethod . " " . date("m-d-Y");
        if($template == "special/_pdf/truck-log")
        {
            $fileName = $variables['business'] . " " . $deliveryMethod . date("m-d-Y");
        }
        
        return Craft::$app->getResponse()->sendContentAsFile($pdf, $fileName . '.pdf', [
            'mimeType' => 'application/pdf'
        ]);   
    }

}
