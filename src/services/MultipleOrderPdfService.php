<?php
/**
 * Multiple Order PDF plugin for Craft CMS 3.x
 *
 * Put multiple selected orders on a single PDF page, the order will be pushed to next page if page break would split.
 *
 * @link      https://importantcoding.com
 * @copyright Copyright (c) 2019 importantcoding
 */

namespace importantcoding\multipleorderpdf\services;
use importantcoding\multipleorderpdf\events\PdfEvent;
use importantcoding\multipleorderpdf\MultipleOrderPdf;
use craft\commerce\elements\Order;
use craft\events\RegisterElementActionsEvent;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use iio\libmergepdf\Merger as Merger;
use craft\commerce\Plugin as Commerce;
use importantcoding\multipleorderpdf\elementactions\SelectAction;
use craft\commerce\Plugin;
use craft\helpers\ArrayHelper;
use craft\web\View;
use Dompdf\Dompdf;
use Dompdf\Options;
use yii\base\Exception;

use Craft;
use craft\base\Component;

use yii\base\InvalidArgumentException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;



/**
 * MultipleOrderPdfService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    importantcoding
 * @package   MultipleOrderPdf
 * @since     1.0.0
 */
class MultipleOrderPdfService extends Component
{
    // Constants
    // =========================================================================

    /**
     * @event PdfEvent The event that is triggered before a PDF is rendered
     * Event handlers can override Commerce's PDF generation by setting [[PdfEvent::pdf]] to a custom-rendered PDF.
     */
    const EVENT_BEFORE_RENDER_PDF = 'beforeRenderPdf';

    /**
     * @event PdfEvent The event that is triggered after a PDF is rendered
     */
    const EVENT_AFTER_RENDER_PDF = 'afterRenderPdf';

    // Public Methods
    // =========================================================================

    /**
     * Returns a rendered PDF object for the order.
     *
     * @param Order $order
     * @param array $orders
     * @param string $option
     * @param string $templatePath
     * @param array $variables variables available to the pdf html template. Available to template by the array keys.
     * @return string
     * @throws Exception if no template or order found.
     */
    public function renderPdfForOrder(array $orders, $option = '', $templatePath = null, $variables = []): string
    {
        if (null === $templatePath) {
            // $templatePath = Plugin::getInstance()->getSettings()->orderPdfPath;
            $templatePath = 'special/_pdf/print-form';
        }

        // Trigger a 'beforeRenderPdf' event
        $event = new PdfEvent([
            'orders' => $orders,
            'option' => $option,
            'template' => $templatePath,
            'variables' => $variables
        ]);
        $this->trigger(self::EVENT_BEFORE_RENDER_PDF, $event);

        if ($event->pdf !== null) {
            return $event->pdf;
        }

        $variables['orders'] = $event->orders;
        $variables['option'] = $event->option;

        // Set Craft to the site template mode
        $view = Craft::$app->getView();
        $oldTemplateMode = $view->getTemplateMode();
        $view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        if (!$templatePath || !$view->doesTemplateExist($templatePath)) {
            // Restore the original template mode
            $view->setTemplateMode($oldTemplateMode);

            throw new Exception('PDF template file does not exist.');
        }

        try {
            $html = $view->renderTemplate($templatePath, $variables);
        } catch (\Exception $e) {
            // Set the pdf html to the render error.
            Craft::error('PDF render error ' . $e->getMessage());
            Craft::$app->getErrorHandler()->logException($e);
            $html = Plugin::t('An error occurred while generating this PDF.');
        }

        // Restore the original template mode
        $view->setTemplateMode($oldTemplateMode);

        $dompdf = new Dompdf();

        // Set the config options
        $pathService = Craft::$app->getPath();
        $dompdfTempDir = $pathService->getTempPath() . DIRECTORY_SEPARATOR . 'commerce_dompdf';
        $dompdfFontCache = $pathService->getCachePath() . DIRECTORY_SEPARATOR . 'commerce_dompdf';
        $dompdfLogFile = $pathService->getLogPath() . DIRECTORY_SEPARATOR . 'commerce_dompdf.htm';

        // Should throw an error if not writable
        FileHelper::isWritable($dompdfTempDir);
        FileHelper::isWritable($dompdfLogFile);

        $isRemoteEnabled = Plugin::getInstance()->getSettings()->pdfAllowRemoteImages;

        $options = new Options();
        $options->setTempDir($dompdfTempDir);
        $options->setFontCache($dompdfFontCache);
        $options->setLogOutputFile($dompdfLogFile);
        $options->setIsRemoteEnabled($isRemoteEnabled);

        // Set the options
        $dompdf->setOptions($options);

        // Paper size and orientation
        $pdfPaperSize = Plugin::getInstance()->getSettings()->pdfPaperSize;
        $pdfPaperOrientation = Plugin::getInstance()->getSettings()->pdfPaperOrientation;
        $dompdf->setPaper($pdfPaperSize, $pdfPaperOrientation);

        $dompdf->loadHtml($html);
        $dompdf->render();

        // Trigger an 'afterRenderPdf' event
        $afterEvent = new PdfEvent([
            'orders' => $event->orders,
            'option' => $event->option,
            'template' => $event->template,
            'variables' => $variables,
            'pdf' => $dompdf->output(),
        ]);
        $this->trigger(self::EVENT_AFTER_RENDER_PDF, $afterEvent);

        return $afterEvent->pdf;
    }
    
    public function registerActions(RegisterElementActionsEvent $event)
    {
        // $settings         = MultipleOrderPdf::$plugin->getSettings();
        // $useCustomActions = $settings->useCustomActions;
        // $customActions    = $settings->actions;

        // if ($useCustomActions && !empty($customActions)) {
        //     $elementsService = Craft::$app->getElements();
        //     foreach ($customActions as $action) {
        //         $label    = $action['label'] ?? null;
        //         $template = $action['template'] ?? null;

        //         if (empty($label) || empty($template)) {
        //             throw new InvalidArgumentException('Both action label and template need to be specified.');
        //         }

        //         $event->actions[] = $elementsService->createAction([
        //             'type'     => ExportAction::class,
        //             'label'    => Craft::t('checkinpdf', $label),
        //             'template' => $template,
        //         ]);
        //     }
        // }
        // else {
        //     $event->actions[] = ExportAction::class;
        // }
        // $event->actions[] = SelectAction::class;
    }

        public function generatePdfs($orders = null, array $ids = [], $template): array
    {
        if (!$orders) {
            return false;
        }

        $option = null;
        $view   = Craft::$app->getView();

        // Set the config options
        $mergePaths     = [];
        $pathService    = Craft::$app->getPath();
        $tempPath       = $pathService->getTempPath() . '/batchpdfs/';
        $fileNameMerged = 'Orders-' . implode('-', $ids) . '.pdf';
        $filenameFormat = 
        Commerce::getInstance()->getSettings()->orderPdfFilenameFormat;

        FileHelper::createDirectory($tempPath);

        /** @var Order $order */
        foreach ($orders as $order) {
            $pdfOutput = Commerce::getInstance()->getPdf()->renderPdfForOrder($order, $option, $template);
            $fileName  = $view->renderObjectTemplate($filenameFormat, $order);

            if (empty($fileName)) {
                $fileName = 'Order-' . $order->number;
            }

            // Append random suffix and pdf ending
            $fileName   = rtrim($fileName, '.pdf') . '-' . StringHelper::randomString(8) . '.pdf';
            $outputPath = $tempPath . '/' . $fileName;

            // Write temp file
            FileHelper::writeToFile($outputPath, $pdfOutput);

            $mergePaths[] = $outputPath;
        }


        // Merge PDF
        $m = new Merger();

        foreach ($mergePaths as $mergePath) {
            $m->addFile($mergePath);
        }

        $output = [
            'output'   => $m->merge(),
            'filename' => $fileNameMerged,
        ];

        return $output;
    }
}
