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
use craft\web\Controller;


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
class MySmsController extends Controller
{

    private $mysms;

    public function actionSetAuth(){
        //API Key
        $api_key = 'v92NHVfGd_hWTign_1O65A';

        //initialize class with apiKey and AuthToken(if available)
        $mysms = new mysms($api_key);

        //lets login user to get AuthToken
        // $login_data = array('msisdn' => '18503540421', 'password' => 'haha69XD');

        $mysms->singleCall();  //providing REST type(json/xml), resource from http://api.mysms.com/index.html and POST data

        // $user_info = json_decode($login); //decode json string to get AuthToken
        // // $_SESSION['AuthToken'] = $user_info->authToken; //saving auth Token in session for more calls
        // $mysms->setAuthToken($user_info->authToken); //setting up auth Token in class (optional)
        // $authToken = $user_info->authToken;
        // $phonenumber = '8503540421';
        // $body = "something";
		// $req_data = array('recipients' => array($phonenumber), 'message' => $body, 'encoding' => 0, 'smsConnectorId' => 0, 'store' => true, 'authToken' => $authToken); //providing AuthToken as per mysms developer doc
		// $info = $mysms->ApiCall('json', '/remote/sms/send', $req_data); //calling method ->ApiCall
		// $enc = json_decode($info);
		// if($info->errorCode != 0){
		// 	$_SESSION['Error'] = "Oh no";
		// }
		// $_SESSION['Info'] = $info;
		// $_SESSION['Error'] = $enc->errorCode;
    }
    
    public function actionSendSms(){

        $this->requirePostRequest();
        $api_key = 'v92NHVfGd_hWTign_1O65A';
        // $request = Craft::$app->getRequest();
        // $phonenumber = $request->getBodyParam('phonenumber');
        $phonenumber = '8503540421';
        $body = "something";
        MultipleOrderPdf::$plugin->mySms->setupCall($phonenumber, $body);
        MultipleOrderPdf::$plugin->multipleOrderPdfService->renderPdfForOrder($orders, $option);
        // MultipleOrderPdf::getInstance()->getMysms()->setupCall($phonenumber, $body);
        
		// $mysms = new MySms($api_key);
        // $authToken = $mysms->authToken;
		// echo $_SESSION['AuthToken'];
		// $req_data = array('recipients' => array($phonenumber), 'message' => $body, 'encoding' => 0, 'smsConnectorId' => 0, 'store' => true, 'authToken' => $authToken); //providing AuthToken as per mysms developer doc
		// $info = $mysms->ApiCall('json', '/remote/sms/send', $req_data); //calling method ->ApiCall
		// $enc = json_decode($info);
		// if($info->errorCode != 0){
		// 	$_SESSION['Error'] = "Oh no";
		// }
		// $_SESSION['Info'] = $info;
		// $_SESSION['Error'] = $enc->errorCode;
    }
}