<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace importantcoding\multipleorderpdf\services;
use importantcoding\multipleorderpdf\MultipleOrderPdf;
use importantcoding\multipleorderpdf\events\PdfEvent;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\Plugin;
use craft\helpers\ArrayHelper;
use craft\helpers\FileHelper;
use craft\web\View;
use Dompdf\Dompdf;
use Dompdf\Options;
use yii\base\Component;
use yii\base\Exception;

/**
 * Pdf service.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class MySms extends Component
{
    private $apiKey = "v92NHVfGd_hWTign_1O65A";
	
	private $authToken = false;
	
	private $BaseUrl = 'https://api.mysms.com/';
	
	function __construct($apikey, $authtoken = false) {
		
		$this->apiKey = $apikey;
		$this->authToken = $authtoken;
		
	}
	
	
	public function setAuthToken($authtoken)
	{
		  $this->authToken = $authtoken;
	}
	
	public function singleCall(){
		//lets login user to get AuthToken
		$login_data = array('msisdn' => '18503540421', 'password' => 'haha69XD');
		$login = $this->ApiCall('json', '/user/login', $login_data);
		$user_info = json_decode($login); //decode json string to get AuthToken
        // $_SESSION['AuthToken'] = $user_info->authToken; //saving auth Token in session for more calls
        $this->setAuthToken($user_info->authToken); //setting up auth Token in class (optional)
        $authToken = $user_info->authToken;
        $phonenumber = '8503540421';
        $body = "this is from checkbox";
		$req_data = array('recipients' => array($phonenumber), 'message' => $body, 'encoding' => 0, 'smsConnectorId' => 0, 'store' => true, 'authToken' => $authToken); //providing AuthToken as per mysms developer doc
		$info = $this->ApiCall('json', '/remote/sms/send', $req_data); //calling method ->ApiCall
		$enc = json_decode($info);
		// if($info->errorCode != 0){
		// 	$_SESSION['Error'] = "Oh no";
		// }
		// $_SESSION['Info'] = $info;
		// $_SESSION['Error'] = $enc->errorCode;
		var_dump($enc->errorCode);
	}
	
	public function apiCall($rest, $resource, $data)
	{
		  if($rest == '' && $rest != 'json' && $rest != 'xml') die('Please provide valid REST type: xml/json!'); //check if $rest is xml or json
		  
		  elseif(filter_var($this->BaseUrl.$rest.$resource, FILTER_VALIDATE_URL) == false) die('Provided Resource or MountUrl is not Valid!'); //check if https://api.mysms.com/$rest/$resource is valid url
		  
		  elseif(!is_array($data)) die('Provided data is not an Array!'); //check if provided $data is valid array
		  
		  else{
				  
				  //insert api key into $data
				  $data['apiKey'] = $this->apiKey;
				
				  $result = $this->curlRequest($rest.$resource, $data);
				  return $result;
		  }


		  
    }
    
    public function setupCall($phonenumber, $body){
        $req_data = array('recipients' => array($phonenumber), 'message' => $body, 'encoding' => 0, 'smsConnectorId' => 0, 'store' => true, 'authToken' => $authToken); //providing AuthToken as per mysms developer doc
		$info = $$this->ApiCall('json', '/remote/sms/send', $req_data); //calling method ->ApiCall
		$enc = json_decode($info);
		if($info->errorCode != 0){
			$_SESSION['Error'] = "Oh no";
		}
		$_SESSION['Info'] = $info;
		$_SESSION['Error'] = $enc->errorCode;
    }
	
	
	
	private function curlRequest($resource, $data)
	{
		 $json_encoded_data = json_encode($data);
		 
		  $curl = curl_init();
		  curl_setopt ($curl, CURLOPT_URL, $this->BaseUrl.$resource);
		  curl_setopt($curl, CURLOPT_POSTFIELDS, $json_encoded_data);
		  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
			'Content-Type: application/json;charset=utf-8',                                                                           
			'Content-Length: ' . strlen($json_encoded_data))                                                                       
			); 
		  return curl_exec ($curl);
		  
	}
}