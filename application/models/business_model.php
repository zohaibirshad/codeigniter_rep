<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/* Author: Jorge Torres
 * Description: Login model class
 */

class business_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}
    public function getBlance($userID){
    	
		$this->db->query('SELECT (sum(credit)-sum(debit)) as balance  FROM accounts WHERE  userId='.$userID);
		return $query->result();
		
    }
    
    public function getMD5Password($pwd){
    	
		// $password=md5($pwd);
		$password=$pwd;
		return $password;
		
    }
	
	// by raj
	
	public function getLeads($state,$category,$ip,$pub,$subpub){
    	
		// $password=md5($pwd);
		
		$this->db->select('*');
		$this->db->from('ad_info');
		$this->db->where('userActivation','1');
		$this->db->where('isActive','1');
		$this->db->where('isApproved','1');
		$this->db->where('isDeleted','0');
		if($state!=NULL && $state!=""){
			
		$this->db->where('stateName',strtolower($state));
		}
		
		if($category!=NULL && $category!='0')
		{
			$this->db->where('categoryId',$category);
			
		}
		
		$this->db->order_by('bid_ppc','desc');
		$query=$this->db->get();
		$ad_statusData=$query->result();
		if(empty($ad_statusData)){
			
			return $ad_statusData;
		}
		$statusids=$this->saveViewedLeads($ip,$pub,$ad_statusData,$subpub);
		
		$statusDetails= $this->getStatusDetails($statusids);
		$adinfo= $query->result();
		$results=array();
		$i=0;
		foreach($adinfo as $ad){
			
			$results[$i]['adId']=$ad->adId;
			$results[$i]['adName']=$ad->adName;
			$results[$i]['bannerImage']=$ad->bannerImage;
			$results[$i]['bannerBackground']=$ad->bannerBackground;
			$results[$i]['adType']=$ad->adType;
			$results[$i]['adIcon_display']=$ad->adIcon_display;
			$results[$i]['adDiscription_display']=$ad->adDiscription_display;
			$results[$i]['adTitle_display']=$ad->adTitle_display;
			$results[$i]['userId']=$ad->userId;
			foreach($statusDetails as $as){
				
				if($ad->adId==$as->adId){
					
					$results[$i]['adStatusId']=$as->adStatusId;
					$results[$i]['created']=$as->created;
					break;
				}	
				
			}
			$i++;
		}
		
		//$allinfo = array($adinfo,$statusDetails);
		
		return $results;
		
		
		
    }
	public function saveViewedLeads($ip,$pub,$ad_statusData,$subpub){
			
		$statusIds=array();
	        //print_r($ad_statusData);
		foreach ($ad_statusData as $ad_status) {
			
		$data['isAdminApproved']='1';
		$data['publisherId']=$pub;
		$data['stateId']=$ad_status->state;
		$data['isViewed']='1';
		$data['adId']=$ad_status->adId;
		$data['advertiserId']= $ad_status->userId;
		$data['ipaddress']=$ip;
		$data['created']=time();
		$data['clicktime']=time();
		$data['subPublisherId']=$subpub;
			
		$this->db->insert('ad_status',$data);
		//echo $this->db->last_query();
		array_push($statusIds,$this->db->insert_id());
		}
		$statusId=implode(",",$statusIds);
		return $statusId;
		
		
		
	}
	
	public function getStatusDetails($statusids)
	{
		$query=$this->db->query("select * from ad_status where adStatusId in ($statusids)");
		 return $query->result();
		
	}
	
	public function makeCommets()
	{
		
		if(!empty($_POST)){
		$status['formFillId']	=	$this->insertFormValues();
		$status['clickTime'] = time();
		$status['formFillDate']=time();
		$status['isAdminApproved']=1;
		$status['isClicked']=1;
		$created=$this -> security -> xss_clean($this -> input -> post('cteationTime'));
		$adID=$this -> security -> xss_clean($this -> input -> post('adId'));
		$adStatusID=$this -> security -> xss_clean($this -> input -> post('StatusID'));
		$advertiserID=$this -> security -> xss_clean($this -> input -> post('advID'));
		$publisherID=$this -> security -> xss_clean($this -> input -> post('pub'));
		$this->db->where('created',$created);
		$this->db->where('adId',$adID);
		$query=$this->db->update('ad_status',$status);
		$comission=$this->getAdminProfit($publisherID,$adID);
		
		$finalCommision='';
		if($comission->adminCommission!='0' && $comission->adminCommission!=NULL){
			
			$finalCommision=$comission->adminCommission;
		}else{
				
			$finalCommision=$comission->commission;
		}
		$query1=$this->makeAccountChange($adStatusID,$comission->bidPrice,$publisherID,$advertiserID,$finalCommision);
		
		}
		return TRUE;
	}
	
	public function insertFormValues(){
	         if(!empty($_POST['sinleLine'])){
               $emailForm['singleText'] 	= 	implode('!&#' , $this->security->xss_clean($this->input->post('sinleLine')));
			   $emailForm['singleText_label'] 	= 	implode('!&#' , $this->security->xss_clean($this->input->post('sinleLine_label')));
				}
			if(!empty($_POST['NumberText'])){
               $emailForm['number'] 		= 	implode('!&#' , $this->security->xss_clean($this->input->post('NumberText')));
			   $emailForm['number_label'] 		= 	implode('!&#' , $this->security->xss_clean($this->input->post('number_label')));
				}
			if(!empty($_POST['paraText'])){
              $emailForm['paragraph'] 		= 	implode('!&#' , $this->security->xss_clean($this->input->post('paraText')));
              $emailForm['paragraph_label'] 		= 	implode('!&#' , $this->security->xss_clean($this->input->post('pText_label')));
				}
			if(!empty($_POST['multipleChoiceRadio'])){
         $emailForm['multipleChoice']= 	implode('!&#' , $this->security->xss_clean($this->input->post('multipleChoiceRadio')));
         $emailForm['multipleChoice_label']= 	implode('!&#' , $this->security->xss_clean($this->input->post('multipleChoice_label')));
				}
			if(!empty($_POST['dropDownList'])){
         $emailForm['dropDown']	= 	implode('!&#' , $this->security->xss_clean($this->input->post('dropDownList')));
         $emailForm['dropDown_label']	= 	implode('!&#' , $this->security->xss_clean($this->input->post('dropDown_label')));
				}
			if(!empty($_POST['checkboxChoice'])){
         $emailForm['checkbox'] = 	implode('!&#' , $this->security->xss_clean($this->input->post('checkboxChoice')));
         $emailForm['checkbox_label'] = 	implode('!&#' , $this->security->xss_clean($this->input->post('checkbox_label')));
				}
		    if(!empty($_POST['websiteText'])){
         $emailForm['website'] 	= 	implode('!&#', $this->security->xss_clean($this->input->post('websiteText')));
         $emailForm['website_label'] 	= 	implode('!&#', $this->security->xss_clean($this->input->post('websiteText_label')));
				}	
			if(!empty($_POST['priceText'])){
         $emailForm['price'] 			= 	implode('!&#' , $this->security->xss_clean($this->input->post('priceText')));
         $emailForm['price_label'] 			= 	implode('!&#' , $this->security->xss_clean($this->input->post('priceText_label')));
				}
			if(!empty($_POST['emailText'])){
         $emailForm['email'] 			= 	implode('!&#' , $this->security->xss_clean($this->input->post('emailText')));
         $emailForm['email_label'] 			= 	implode('!&#' , $this->security->xss_clean($this->input->post('emailText_label')));
				}
			if(!empty($_POST['nameFirst'])){
					
						$name = array();
						for($i=0; $i < count($_POST['nameFirst']);$i++){
							$name[$i] = $_POST['nameFirst'][$i] . ' ' . $_POST['nameLast'][$i];
						}
						//print_r($name);
			$emailForm['name']	= 	implode('!&#',$name);
			$emailForm['name_label']	= 	implode('!&#' , $this->security->xss_clean($this->input->post('nameText_label')));
					}
			if(!empty($_POST['dateMm'])){
					
						$name = array();
						for($i=0; $i < count($_POST['dateMm']);$i++){
							$timeData = $_POST['dateMm'][$i] . '/' . $_POST['dateDd'][$i]. '/' . $_POST['dateYy'][$i];
							$date_[$i]  = strtotime($timeData);
						}
						//print_r($name);
			$emailForm['date_']	= 	implode('!&#',$date_);
			$emailForm['date_label']	= 		implode('!&#' , $this->security->xss_clean($this->input->post('dateText_label')));
					}
			if(!empty($_POST['timeHh'])){
					
						$name = array();
						for($i=0; $i < count($_POST['timeHh']);$i++){
							$timeData = $_POST['timeHh'][$i] . ':' . $_POST['timeMm'][$i]. ':' . $_POST['TimeSs'][$i]. ' '.$_POST['timeLabel'][$i] ;
							$time_[$i]  = strtotime($timeData);
						}
						//print_r($name);
			$emailForm['time_']	= 	implode('!&#',$time_);
			$emailForm['time_label']	= 		implode('!&#' , $this->security->xss_clean($this->input->post('timeText_label')));
					}
			if(!empty($_POST['phoneStart'])){
					
						$name = array();
						for($i=0; $i < count($_POST['phoneStart']);$i++){
							$phonea[$i] = $_POST['phoneStart'][$i] . '-' . $_POST['phoneMid'][$i]. '-' . $_POST['phoneLast'][$i];
						
						}
						
			$emailForm['phone']	= 	implode('!&#',$phonea);
			$emailForm['phone_label']	= 		implode('!&#' , $this->security->xss_clean($this->input->post('phoneText_label')));
					}
					
					
			if(!empty($_POST['addressName'])){
					
						$name = array();
						for($i=0; $i < count($_POST['addressName']);$i++){
							$add = $_POST['addressName'][$i] . '  ' . $_POST['addressStreet'][$i]. '  ' . $_POST['addresscity'][$i]. '  ' . $_POST['addressRegion'][$i]. '  ' . $_POST['addressZip'][$i]. '  ' . $_POST['addressCountry'][$i];
						$address[$i]  = $add;	
						}
			$emailForm['address']	= 	implode('!&#',$address);
			$emailForm['address_label']	= 		implode('!&#' , $this->security->xss_clean($this->input->post('addressText_label')));
					}
					
			$emailForm['formId']	= 	$this->security->xss_clean($this->input->post('formId'));
					
			$this->db->insert('form_submit',$emailForm);
			return $this->db->insert_id();
			
	
	}
	
	public function getAdminProfit($publisherID,$adID){
		
		$query=$this->db->query("select commission,(SELECT adminCommission FROM user_profile where userId=$publisherID) as adminCommission,(select bid_ppc from ad_info where adId=$adID) as bidPrice from setting_admin_commission ");
		$adminCommision=$query->row();
		return $adminCommision;
		
	}
	public function makeAccountChange($adStatusID,$bidPrice,$publisherID,$advertiserID,$adminCussimion){
		
		
		
		$admin_profit=($bidPrice/100)*$adminCussimion;
		$pub_amount=$bidPrice-$admin_profit;
		$accounts['credit']=$pub_amount;
		$accounts['transactionTime']=time();
		$accounts['comment']='1';
		$accounts['adStatusId']=$adStatusID;
		$accounts['userId']=$publisherID;
		$accounts['memo']='on ad click amount credit to publisher';
		$accounts['admin_profile']=$admin_profit;
		$this->db->insert('accounts',$accounts);
		
		$ad_accounts['debit']=$bidPrice;
		$ad_accounts['comment']='2';
		$ad_accounts['transactionTime']=time();
		$ad_accounts['adStatusId']=$adStatusID;
		$ad_accounts['userId']=$advertiserID;
		$ad_accounts['memo']='on ad click amount debit from advertiser';
		$this->db->insert('accounts',$ad_accounts);
		//echo $this->db->last_query();
		return TRUE;
		
	}
	
	
	// for auto insurance form
	
	public function AutoInsurance($topAdvsId){
		$categoryid=$this->security->xss_clean($this->input->post('categoryid'));
                //echo $categoryid;
                $savedata['recomanded_advertiser']= $this->security->xss_clean($topAdvsId);
                $recomanded_advertiser = $savedata['recomanded_advertiser'];
                //     $topAdvsData=$this->security->xss_clean($this->input->post('topAdvrData'));
           
             //   echo $recomanded_advertiser ;
		$mails=$this->security->xss_clean($this->input->post('email-input'));
                $name = $this->security->xss_clean($this->input->post('full-name-input'));
                //echo $mails;
                
                $isPrevoisCategory=$this->isPrevoiusVisitorForSameCategory($mails,AUTO_INSURANCE);
                //echo $isPrevoisCategory;
                if($isPrevoisCategory ==0 ){
                         //echo "sarvesh";
			$this->sendmailforleadsForUniqueUser($mails, AUTO_INSURANCE,$name);
                         //exit();
			// for visitors how have visited in 1 day
		}
               //exit;
		$isprevois=$this->isPrevoiusVisitor($mails,AUTO_INSURANCE,$recomanded_advertiser);
		if($isprevois >0 ){
			$savedata['fraud'] = 1;
			// for visitors how have visited in 2 months
		}
		$carcount=	$this->security->xss_clean($this->input->post('carcount'));
		$driverCount =	$this->security->xss_clean($this->input->post('carcount'));
		$driverCount =	$this->security->xss_clean($this->input->post('carcount'));
		$caryear =	$this->security->xss_clean($this->input->post('car-year'));
		$carmake =	$this->security->xss_clean($this->input->post('car-make-select'));
		/*$carseries =	$this->security->xss_clean($this->input->post('car-series-select'));
                $carown =	$this->security->xss_clean($this->input->post('car-own-select'));
                $carpark =	$this->security->xss_clean($this->input->post('car-park-select'));
                $caruse =	$this->security->xss_clean($this->input->post('car-use-select'));
                $carmileage =	$this->security->xss_clean($this->input->post('car-mileage-select'));*/
		$carmodel =	$this->security->xss_clean($this->input->post('car-model-select'));
                $carcost =	$this->security->xss_clean($this->input->post('cost-of-vehicle'));

		$vehiclecount=1;
		$formxml='<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd"><auto><vehicle>';
$formxml=$formxml.'<vehicleyear>'.$caryear.'</vehicleyear>';
$formxml=$formxml.'<vehiclemake>'.$carmake.'</vehiclemake>';
$formxml=$formxml.'<vehiclemodel>'.$carmodel.'</vehiclemodel>';
$formxml=$formxml.'<vehiclecost>'.$carcost.'</vehiclecost></vehicle>';
//$formxml=$formxml.'<vehicleseries>'.$carseries.'</vehicleseries>';
//$formxml=$formxml.'<vehicleown>'.$carown.'</vehicleown>';
//$formxml=$formxml.'<vehiclepark>'.$carpark.'</vehiclepark>';
//$formxml=$formxml.'<vehicleuse>'.$caruse.'</vehicleuse>';
//$formxml=$formxml.'<vehiclemileage>'.$carmileage.'</vehiclemileage></vehicle>';


		while($vehiclecount<10){
				
			if($carcount>$vehiclecount){
			if($this->security->xss_clean($this->input->post('car-year'.$vehiclecount)))
			{
				
$formxml=$formxml.'<vehicle><vehicleyear>'.$this->security->xss_clean($this->input->post('car-year'.$vehiclecount)).'</vehicleyear>';
$formxml=$formxml.'<vehiclemake>'.$this->security->xss_clean($this->input->post('car-make-select'.$vehiclecount)).'</vehiclemake>';
$formxml=$formxml.'<vehiclemodel>'.$this->security->xss_clean($this->input->post('car-model-select'.$vehiclecount)).'</vehiclemodel>';
$formxml=$formxml.'<vehiclecost>'.$this->security->xss_clean($this->input->post('cost-of-vehicle'.$vehiclecount)).'</vehiclecost></vehicle>';
//$formxml=$formxml.'<vehicleseries>'.$this->security->xss_clean($this->input->post('car-series-select'.$vehiclecount)).'</vehicleseries>';
//$formxml=$formxml.'<vehicleown>'.$this->security->xss_clean($this->input->post('car-own-select'.$vehiclecount)).'</vehicleown>';
//$formxml=$formxml.'<vehiclepark>'.$this->security->xss_clean($this->input->post('car-park-select'.$vehiclecount)).'</vehiclepark>';
//$formxml=$formxml.'<vehicleuse>'.$this->security->xss_clean($this->input->post('car-use-select'.$vehiclecount)).'</vehicleuse>';
//$formxml=$formxml.'<vehiclemileage>'.$this->security->xss_clean($this->input->post('car-mileage-select'.$vehiclecount)).'</vehiclemileage></vehicle>';
				
				
				
				
			}}else{
				
				break;
				
			}
			$vehiclecount++;
		}
                
//$dob=$dob1=$this->security->xss_clean($this->input->post('driver-birth-select'));
$formxml=$formxml.'<driver><fullname>'.$this->security->xss_clean($this->input->post('full-name-input')).'</fullname>';
//$formxml=$formxml.'<firstname>'.$this->security->xss_clean($this->input->post('first-name-input')).'</firstname>';
//$formxml=$formxml.'<last-name>'.$this->security->xss_clean($this->input->post('last-name-input')).'</last-name>';
//$formxml=$formxml.'<gender>'.$this->security->xss_clean($this->input->post('gender-select')).'</gender>';
//$formxml=$formxml.'<dob>'.$dob.'</dob>';
//$formxml=$formxml.'<marital>'.$this->security->xss_clean($this->input->post('marital-status-select')).'</marital>';
//$formxml=$formxml.'<education>'.$this->security->xss_clean($this->input->post('education-select')).'</education>';
//$formxml=$formxml.'<homeowner>'.$this->security->xss_clean($this->input->post('owns-home-select')).'</homeowner>';
$formxml=$formxml.'<occupation>'.$this->security->xss_clean($this->input->post('occupation-select')).'</occupation>';
//$formxml=$formxml.'<licance>'.$this->security->xss_clean($this->input->post('license-status-select')).'</licance>';
//$formxml=$formxml.'<accidents>'.$this->security->xss_clean($this->input->post('incident-select')).'</accidents></driver>';
$formxml=$formxml.'</driver>';
$drcount=1;
		while($drcount<10){
				
			if($driverCount>$drcount){
			if($this->security->xss_clean($this->input->post('full-name-input'.$drcount)))
			{
//$dob1=$this->security->xss_clean($this->input->post('driver-birth-select'.$drcount));
$formxml=$formxml.'<driver><fullname>'.$this->security->xss_clean($this->input->post('full-name-input'.$drcount)).'</fullname>';
//$formxml=$formxml.'<firstname>'.$this->security->xss_clean($this->input->post('first-name-input'.$drcount)).'</firstname>';
//$formxml=$formxml.'<last-name>'.$this->security->xss_clean($this->input->post('last-name-input'.$drcount)).'</last-name>';
//$formxml=$formxml.'<gender>'.$this->security->xss_clean($this->input->post('gender-select'.$drcount)).'</gender>';
//$formxml=$formxml.'<dob>'.$dob1.'</dob>';
//$formxml=$formxml.'<marital>'.$this->security->xss_clean($this->input->post('marital-status-select'.$drcount)).'</marital>';
//$formxml=$formxml.'<education>'.$this->security->xss_clean($this->input->post('education-select'.$drcount)).'</education>';
//$formxml=$formxml.'<homeowner>'.$this->security->xss_clean($this->input->post('owns-home-select'.$drcount)).'</homeowner>';
$formxml=$formxml.'<occupation>'.$this->security->xss_clean($this->input->post('occupation-select'.$drcount)).'</occupation>';
//$formxml=$formxml.'<licance>'.$this->security->xss_clean($this->input->post('license-status-select'.$drcount)).'</licance>';
//$formxml=$formxml.'<accidents>'.$this->security->xss_clean($this->input->post('incident-select'.$drcount)).'</accidents></driver>';
$formxml=$formxml.'</driver>';				
				
				
				
			}}else{
				
				break;
				
			}
			$drcount++;
		}

$formxml=$formxml.'<insured>'.$this->security->xss_clean($this->input->post('insured-select')).'</insured>';
$formxml=$formxml.'<insurer>'.$this->security->xss_clean($this->input->post('current-insurer-select')).'</insurer>';
$formxml=$formxml.'<phone>'.$this->security->xss_clean($this->input->post('phone-input')).'</phone>';
$formxml=$formxml.'<email>'.$this->security->xss_clean($this->input->post('email-input')).'</email>';
$formxml=$formxml.'<zip>'.$this->security->xss_clean($this->input->post('zip-input')).'</zip>';
$formxml=$formxml.'<address>'.$this->security->xss_clean($this->input->post('address-input')).'</address></auto>';
		
		$savedata['ipaddress']= $_SERVER['REMOTE_ADDR'];
		$savedata['publisherId']= $this->security->xss_clean($this->input->post('publisherid'));
		$savedata['sub_publisherId']= $this->security->xss_clean($this->input->post('subpublisherid'));
                $savedata['state_name']=$this->security->xss_clean($this->input->post('state_name'));
		$savedata['categoryId']= AUTO_INSURANCE;
                $savedata['category_name']='Auto Insurance';
		$savedata['recomanded_advertiser']= $this->security->xss_clean($topAdvsId);
		$savedata['form_data']=$formxml;
		$savedata['datetime']=time();
		//print_r($savedata['recomanded_advertiser']);
		$this->db->insert('ad_form_data',$savedata);
		$form_data_id= $this->db->insert_id();
                //echo $isprevois;
               // echo "sarvesh";
                //if($isprevois == 0 ){//echo $isprevois;
                    $this->AddLeadInPurchase($form_data_id,$savedata['recomanded_advertiser']);
                    $this->CreditAccounts($savedata['recomanded_advertiser'], $savedata['publisherId'], $form_data_id,$categoryid);
                 //}
		return true;
		
		
		
		
	}
        public function getUsersBalance(){
            $query = $this->db->query("SELECT userId, (SUM( credit ) - SUM( debit )) AS balance FROM accounts GROUP BY userId");
            return $query->result();
        }
	public function getPreferedAdvertisers($catId){
		$advs = array();
		$query=$this->db->query("SELECT userId,name,activecategory,email FROM user_profile where userType = 1 and isActive = 1 and isAccepted = 1" );
		$results = $query->result();
                
                $balance = $this->getUsersBalance();
                $advForUpdateAndMail = array();
                //print_r($results);
                foreach ($results as $adv){
                    $catstr = $adv->activecategory;
                    $catPriceArr = strlen($catstr) > 0 ? explode(",", $catstr) : array();
                    foreach($catPriceArr as $cp){
                        $catpr = isset($cp) && strlen($cp) > 0  ? explode(":", $cp) : NULL;
                        $cat = isset($catpr[0]) ? $catpr[0] : NULL;
                        $pr = isset($catpr[1]) ? $catpr[1] : NULL;
                        if(isset($cat) && isset($pr)){
                            if($cat == $catId){
                                // now we have to check that this adv have enough balance in his account.
                                // if not so . then add him in $advForUpdateAndMail array and send him mail.
                                foreach($balance as $bal){
                                    if($adv->userId == $bal->userId){
                                        // if total balance of advertiser is greater than bid price of that category
                                        if($bal->balance >= $pr){
                                            if(isset($advs[$pr])){
                                                $newArray = $advs[$pr];
                                            }else{
                                                $newArray = array();
                                            }
                                            $newArray = array_merge($newArray,array($adv));
                                            //if(isset($advs[]))
                                            $advs[$pr] = $newArray;
                                            
                                            
                                            // now perform sorting..
                                            /*foreach($advs as $advert){
                                                
                                                $i ++;
                                            }*/
                                            /*for($i=0;$i<count($advs);$i++){
                                                for($j=0;$j<count($advs);$j++){
                                                    if($advs[$i] > $advs[$j]){
                                                        $temp = $advs[$i];
                                                        $advs[$i] = $advs[$j];
                                                        $advs[$j] = $temp;
                                                    }
                                                }
                                            }*/
                                        }else{
                                            $advForUpdateAndMail[$adv->userId] = $adv;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                //print_r($advs);
                /*foreach($advs as $ad){
                    
                    echo "<br/><br/>";
                }*/
                if(count($advForUpdateAndMail) > 0){
                    $this->updateAdvAndSendMail($advForUpdateAndMail);
                }
                //print_r($advs);
                //echo "<br/>";
                // sort associative array by its key value
                ksort($advs,SORT_NUMERIC);
                $advs = array_reverse($advs,true);
                // print_r($advs);
                return $advs;
	}
        // this method sends mail and update those adv. who have low account balance.
        private function updateAdvAndSendMail($advs){
            require_once("mailer/Email.php");
            $strUserIds = implode(",", array_keys($advs));
            $query = $this->db->query("UPDATE user_profile SET isActive = 2 WHERE userId IN ($strUserIds)");
            $mailData['Site_name'] 		= SITE_NAME;
            //print_r($advs);
            foreach($advs as $adv){
                $user_email     = $adv->email;
                $name 		= $adv->name;
                $mailData["User_name_data"] = $name;
                $emailSender = new Email();
                $emailSender->SendEmail('low_balance',$mailData,$user_email,ADMIN_MAIL,ADMIN_MAIL_PASSWORD,SITE_NAME,'Low Account Balance');
            }
        }
        public function getActiveAdvertisers($catId){
		$advs = array();
		$query=$this->db->query("SELECT userId,name,activecategory,email FROM user_profile where userType = 1 and isActive = 1 and isAccepted = 1" );
		$results = $query->result();
                
                $balance = $this->getUsersBalance();
                $advForUpdateAndMail = array();
                //print_r($results);
                foreach ($results as $adv){
                    $catstr = $adv->activecategory;
                    $catPriceArr = strlen($catstr) > 0 ? explode(",", $catstr) : array();
                    foreach($catPriceArr as $cp){
                        $catpr = isset($cp) && strlen($cp) > 0  ? explode(":", $cp) : NULL;
                        $cat = isset($catpr[0]) ? $catpr[0] : NULL;
                        $pr = isset($catpr[1]) ? $catpr[1] : NULL;
                        if(isset($cat) && isset($pr)){
                            if($cat == $catId){
                                // now we have to check that this adv have enough balance in his account.
                                // if not so . then add him in $advForUpdateAndMail array and send him mail.
                                foreach($balance as $bal){
                                    if($adv->userId == $bal->userId){
                                        // if total balance of advertiser is greater than bid price of that category
                                        if($bal->balance >= $pr){
                                            if(isset($advs[$pr])){
                                                $newArray = $advs[$pr];
                                            }else{
                                                $newArray = array();
                                            }
                                            $newArray = array_merge($newArray,array($adv));
                                            //if(isset($advs[]))
                                            $advs[$pr] = $newArray;
                                            
                                            
                                            // now perform sorting..
                                            /*foreach($advs as $advert){
                                                
                                                $i ++;
                                            }*/
                                            /*for($i=0;$i<count($advs);$i++){
                                                for($j=0;$j<count($advs);$j++){
                                                    if($advs[$i] > $advs[$j]){
                                                        $temp = $advs[$i];
                                                        $advs[$i] = $advs[$j];
                                                        $advs[$j] = $temp;
                                                    }
                                                }
                                            }*/
                                        }else{
                                            $advForUpdateAndMail[$adv->userId] = $adv;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                //print_r($advs);
                /*foreach($advs as $ad){
                    
                    echo "<br/><br/>";
                }*/
                if(count($advForUpdateAndMail) > 0){
                    $this->updateAdvAndSendMail($advForUpdateAndMail);
                }
                //print_r($advs);
                //echo "<br/>";
                // sort associative array by its key value
                ksort($advs,SORT_NUMERIC);
                $advs = array_reverse($advs,true);
                // print_r($advs);
                return $advs;
	}
	
	public function isPrevoiusVisitor($emailID, $catId,$recomanded_advertiser){
            //echo "sarvesh";
            //echo $emailID;
           // echo $catId;
		//echo $recomanded_advertiser;
		//this is the time before a month
		//$previousTime=time()-2592000;
            //this is the time before 2 weeks
                $previousTime=time()-1209600;
		$emailid=$emailID;
		
		$this->db->select(" * FROM ad_form_data where form_data like '%$emailid%' and datetime > $previousTime and categoryId=$catId and recomanded_advertiser=$recomanded_advertiser");
		$query= $this -> db -> get();
		$res= $query->result();
                //echo count($res);
		return count($res);
	}
	
	
	
	// end of auto insuraance form
	
	
	// for auto insurance form
	
	public function submithome($topAdvsId){
           // echo "submitting";
//		$title=	$this->security->xss_clean($this->input->post('selecttitle'));
		$fullname =	$this->security->xss_clean($this->input->post('fullname'));
//		$marital =	$this->security->xss_clean($this->input->post('selectmarital'));
		$email =	$this->security->xss_clean($this->input->post('email'));
                $categoryid=$this->security->xss_clean($this->input->post('categoryid'));
                $savedata['recomanded_advertiser']= $this->security->xss_clean($topAdvsId);
                $recomanded_advertiser = $savedata['recomanded_advertiser'];
                $isPrevoisCategory=$this->isPrevoiusVisitorForSameCategory($email,HOME_INSURANCE);
                //echo $isPrevoisCategory;
                if($isPrevoisCategory ==0 ){
                         //echo "sarvesh";
			  $this->sendmailforleadsForUniqueUser($email, HOME_INSURANCE, $fullname);
                         //exit();
			// for visitors how have visited in 1 day
		}
		$isprevois=$this->isPrevoiusVisitor($email,HOME_INSURANCE,$recomanded_advertiser);
		
		if($isprevois >0 ){
			$savedata['fraud'] = 1;
			// for visitors how have visited in 2 months
		}
		$phone =	$this->security->xss_clean($this->input->post('phone'));
//		$employment =	$this->security->xss_clean($this->input->post('selectemployment'));
		$street =	$this->security->xss_clean($this->input->post('street'));
		$statename =	$this->security->xss_clean($this->input->post('statename'));
		$zip =	$this->security->xss_clean($this->input->post('zip'));
		
		$property_type =	$this->security->xss_clean($this->input->post('propetyType'));
		//print_r($property_type);
		if($property_type == "NONE"){
			$property_type=$this->security->xss_clean($this->input->post('property_type'));
		}
		$number_rooms =	$this->security->xss_clean($this->input->post('number_rooms'));
                $cost_of_home =	$this->security->xss_clean($this->input->post('cost-of-home'));
                $insuranceType=$this->security->xss_clean($this->input->post('insuranceType'));
		$number_toilets =	$this->security->xss_clean($this->input->post('number_toilets'));
                //print_r($property_type);
		

		$formxml='<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd"><home_detail>';
$formxml=$formxml.'<name>'.$fullname.'</name>';
//$formxml=$formxml.'<marital_status>'.$marital.'</marital_status>';
$formxml=$formxml.'<email>'.$email.'</email>';
$formxml=$formxml.'<phone>'.$phone.'</phone>';
//$formxml=$formxml.'<employment>'.$employment.'</employment>';
$formxml=$formxml.'<address>'.$street.'</address>';
$formxml=$formxml.'<state>'.$statename.'</state>';
$formxml=$formxml.'<zipcode>'.$zip.'</zipcode>';
$formxml=$formxml.'<property_type>'.$property_type.'</property_type>';
$formxml=$formxml.'<number_of_rooms>'.$number_rooms.'</number_of_rooms>';
$formxml=$formxml.'<insurance_type>'.$insuranceType.'</insurance_type>';
$formxml=$formxml.'<cost_of_home>'.$cost_of_home.'</cost_of_home>';
$formxml=$formxml.'<number_of_bathrooms>'.$number_toilets.'</number_of_bathrooms></home_detail>';


		
		$savedata['ipaddress']= $_SERVER['REMOTE_ADDR'];
		$savedata['publisherId']= $this->security->xss_clean($this->input->post('publisherid'));
		$savedata['sub_publisherId']= $this->security->xss_clean($this->input->post('subpublisherid'));
		$savedata['categoryId']= HOME_INSURANCE;
		$savedata['category_name']='home Insurance';
		$savedata['state_name']=$this->security->xss_clean($this->input->post('statename'));
		$savedata['recomanded_advertiser']= $this->security->xss_clean($topAdvsId);
		$savedata['form_data']=$formxml;
		$savedata['datetime']=time();
		
		$this->db->insert('ad_form_data',$savedata);
		$form_data_id= $this->db->insert_id();
                //if($isprevois  == 0 ){
                    $this->AddLeadInPurchase($form_data_id,$savedata['recomanded_advertiser']);
                    $this->CreditAccounts($savedata['recomanded_advertiser'], $savedata['publisherId'], $form_data_id,$categoryid);
		//}
		return true;
	}
	
	public function AddLeadInPurchase($form_data_id, $userId)
	{
		$savedata['form_dataId']=$form_data_id;
		$savedata['givenDate']=time();
		$savedata['advertiserId']=	$userId;
		$this->db->insert('advertiser_form_data',$savedata);
		
		return true;
		
	}
	
	public function CreditAccounts($userId,$publisherId,$form_data_id,$categoryid)
	{ 
         
           
              $advertiser = $this->getPreferedAdvertisers($categoryid);
              //print_r($advertiser);
             $advIds = array();
                foreach($advertiser as $advmoney){
                    foreach ($advmoney as $adv){
                        $advIds[] = $adv->userId;
                       $advActivecategory[] = $adv->activecategory;
                    }
                }
                //echo $advIds[0];
                //echo $advActivecategory[0];
                if(isset($advIds)&&!empty($advIds)){
                if($advIds[0]==$userId){
                    $firstActivecategory = explode(',',$advActivecategory[0]); 
                    foreach ($firstActivecategory as $cats){
                        $catpr = isset($cats) && strlen($cats) > 0  ? explode(":", $cats) : NULL;
                                for($i=0;$i<count($catpr);$i++){
                                        if($catpr[$i]==$categoryid){
                                        $bid_price	=	$catpr[$i+1]	;
                                        }
                                }
                        }
                    if(isset($advActivecategory[1])){
                            $firstActivecategory1 = explode(',',$advActivecategory[1]); 
                            foreach ($firstActivecategory1 as $cats1){
                                $catpr1 = isset($cats1) && strlen($cats1) > 0  ? explode(":", $cats1) : NULL;
                                        for($i=0;$i<count($catpr1);$i++){
                                                if($catpr1[$i]==$categoryid){
                                                $bid_price1	=	$catpr1[$i+1]	;
                                                }
                                        }
                            }
                    if($bid_price>$bid_price1+50){//echo "sarvesh";
                        $bid_priceToken = $_POST['bid_priceToken']+1;
                        $bid_priceTokenValue = $bid_price1 + 50; 
                    }else{
                        $bid_priceToken				=	$_POST['bid_priceToken'];
                        $bid_priceTokenValue		=	$_POST['bid_priceTokenValue'];
                    }
                    }else{
                    $bid_priceToken				=	$_POST['bid_priceToken'];
		    $bid_priceTokenValue		=	$_POST['bid_priceTokenValue'];
                    }
                    }else{
                    $bid_priceToken				=	$_POST['bid_priceToken'];
		    $bid_priceTokenValue		=	$_POST['bid_priceTokenValue'];
                    }
                    }else{
                    $bid_priceToken				=	$_POST['bid_priceToken'];
		    $bid_priceTokenValue		=	$_POST['bid_priceTokenValue'];
                    }
                                    //echo $bid_price;
                                   // echo $bid_price1;
                               
                            
                // echo $advertiser->bidprice;
				//$bid_priceToken				=	$_POST['bid_priceToken'];
				//$bid_priceTokenValue		=	$_POST['bid_priceTokenValue'];
                               
                             //echo $bid_priceToken;
                               // echo $bid_priceTokenValue;
		$query1=$this->db->query("select * FROM setting_admin_commission,user_profile where userId=$publisherId");
		$publisher=$query1->row();
                if(isset($publisher->userId)){
                    $this->db->select('*');
                    $this -> db -> from('user_profile');
                    $this->db->where('userId',$userId);
                    $query=$this->db->get();
                    $advertiser=$query->row();
                    if(isset($advertiser->userId)){
                        //get category for which the form has been submitted . 
                        $this->db->select("categoryId");
                        $this->db->where("form_data_id" , $form_data_id);
                        $query = $this->db->get("ad_form_data");
                        $category =  $query->row();
                        if(isset($category->categoryId)){
                            $bidpriceArr = isset($advertiser->activecategory) ? explode(",",$advertiser->activecategory) : array();
                            //print_r($advertiser);
                            $bidNotSet = false;
                            foreach($bidpriceArr as $bid){
                                $bp = explode(":",$bid);
                                if(isset($bp[0]) && $category->categoryId == $bp[0]){
                                    $advertiser->bidprice = $bp[1];
                                    $bidNotSet = false;
                                    break;
                                }else{
                                    $bidNotSet = true;
                                }
                            }
                            // if bid price is not set by advertiser then pick up default value from categories table.
                            if($bidNotSet){
                                $this->db->select("minbidprice");
                                $this->db->from("categories");
                                $this->db->where("categoryId",$category->categoryId);
                                $query = $this->db->get();
                                $row = $query->row();
                                $advertiser->bidprice = $row->minbidprice;
                            }
                            $pubprofit=0;
                            $adminProfit=0;
                            // vaibhav place
//                            if($bid_priceToken==1){
//                                    $advertiser->bidprice	= $bid_priceTokenValue;	
//                            }
							
                            if($publisher->adminCommission !='' && $publisher->adminCommission !=0 && $publisher->adminCommission != null)
                            {
                                $adminProfit=($advertiser->bidprice*$publisher->adminCommission)/100;
                                //print_r($advertiser);
                                //print_r($publisher);
                            }else{
                                    $adminProfit=($advertiser->bidprice*$publisher->commission)/100;

                            }
                           // echo $advertiser->bidprice;
                            $pubprofit=$advertiser->bidprice - $adminProfit;
                            $res=0;

                            $setprice['debit']=$advertiser->bidprice;
                            $setprice['transactionTime']=time();
                            $setprice['comment']=2;
                            $setprice['userId']=$userId;
                            $setprice['memo']="Lead completed for advertiser";
                            $setprice['form_data_id']=$form_data_id;

                            $res=$this->db->insert('accounts',$setprice);



                            $setprice1['credit']=$pubprofit;
                            $setprice1['transactionTime']=time();
                            $setprice1['comment']=1;
                            $setprice1['userId']=$publisherId;
                            $setprice1['memo']="Lead completed for advertiser profit added to publisher";
                            $setprice1['admin_profile']=$adminProfit;
                            $setprice1['form_data_id']=$form_data_id;
                            $this->db->insert('accounts',$setprice1);
                        }else{
                            // no such category
                        }
                    }else{
                        // no such advertiser.
                    }
                }else{
                  //  echo "no such publisher";
                }
	}
	
	
	// end raj
        
	// start by nitesh
        //for Life Insurance Form
       public function LifeInsurance($topAdvsId){
            $mails=$this->security->xss_clean($this->input->post('email'));
            $name = $this->security->xss_clean($this->input->post('fullname'));
            $categoryid=$this->security->xss_clean($this->input->post('categoryid'));
            $savedata['recomanded_advertiser']= $this->security->xss_clean($topAdvsId);
            $recomanded_advertiser = $savedata['recomanded_advertiser'];
            //$recomanded_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser")); 
            $isPrevoisCategory=$this->isPrevoiusVisitorForSameCategory($mails,LIFE_INSURANCE);
                //echo $isPrevoisCategory;
                if($isPrevoisCategory ==0 ){
                         //echo "sarvesh";
			  $this->sendmailforleadsForUniqueUser($mails, LIFE_INSURANCE, $name);
                         //exit();
			// for visitors how have visited in 1 day
		}
            $isprevois=$this->isPrevoiusVisitor($mails,LIFE_INSURANCE,$recomanded_advertiser);
            if($isprevois >0 ){
                $savedata['fraud'] = 1;
                    // for visitors how have visited in 2 months
            }
            $state_name = $this->security->xss_clean($this->input->post("state_name"));
            $publisherId = $this->security->xss_clean($this->input->post("publisherid"));
            $subPublisherId = $this->security->xss_clean($this->input->post("subpublisherid"));
            //$categoryid = $this->security->xss_clean($this->input->post("categoryid"));
         //   $prefered_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser"));
            $residence = $this->security->xss_clean($this->input->post("statename"));
            $selectgender = $this->security->xss_clean($this->input->post("selectgender"));
            $dob = $this->security->xss_clean($this->input->post("dob"));
            $conamount = $this->security->xss_clean($this->input->post("conamount"));
            $termlenght = $this->security->xss_clean($this->input->post("teamlenght"));
            $Tobacco_Nicotine_user = $this->security->xss_clean($this->input->post("Tobacco_Nicotine_user"));
            $health = $this->security->xss_clean($this->input->post("health"));
            $fullname = $this->security->xss_clean($this->input->post("fullname"));
            $phone = $this->security->xss_clean($this->input->post("phone"));
            
            $formxml = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
    <life>
        <person>
            <fullname>
              $fullname
            </fullname>
            <email>
              $mails
            </email>
            <phone>
              $phone
            </phone>
            <gender>
              $selectgender
            </gender>
            <dateofbirth>
              $dob
            </dateofbirth>
            <statename>
              $residence
            </statename>
            <coverageAmount>
              $conamount
            </coverageAmount>
            <termlenght>
              $termlenght
            </termlenght>
            <addicted>
              $Tobacco_Nicotine_user
            </addicted>
            <health>
              $health
            </health>
          </person>
      </life>
                    
EOD;
                $savedata['ipaddress']= $_SERVER['REMOTE_ADDR'];
		$savedata['publisherId']= $publisherId;
		$savedata['sub_publisherId']= $subPublisherId;
		$savedata['categoryId']= LIFE_INSURANCE;
		$savedata['category_name']='Life Insurance';
		$savedata['state_name']= $state_name;
		$savedata['recomanded_advertiser']= $this->security->xss_clean($topAdvsId);
		$savedata['form_data']=$formxml;
		$savedata['datetime']=time();
		
		$this->db->insert('ad_form_data',$savedata);
		$form_data_id= $this->db->insert_id();
                //if($isprevois  == 0 ){
                    $this->AddLeadInPurchase($form_data_id,$savedata['recomanded_advertiser']);
                    $this->CreditAccounts($savedata['recomanded_advertiser'], $savedata['publisherId'], $form_data_id,$categoryid);
		//}
		return true;
        }
        
         public function HealthInsurance($topAdvsId){
            $mails=$this->security->xss_clean($this->input->post('email'));
            $fullname = $this->security->xss_clean($this->input->post("fullname"));
            $categoryid=$this->security->xss_clean($this->input->post('categoryid'));
             $savedata['recomanded_advertiser']= $this->security->xss_clean($topAdvsId);
            $recomanded_advertiser = $savedata['recomanded_advertiser']; 
             $isPrevoisCategory=$this->isPrevoiusVisitorForSameCategory($mails,Health_insurance);
                //echo $isPrevoisCategory;
                if($isPrevoisCategory ==0 ){
                         //echo "sarvesh";
			 $this->sendmailforleadsForUniqueUser($mails, Health_insurance,$fullname);
                         //exit();
			// for visitors how have visited in 1 day
		}
            $isprevois=$this->isPrevoiusVisitor($mails,Health_insurance,$recomanded_advertiser);
            if($isprevois >0 ){
                $savedata['fraud'] = 1;
                    // for visitors how have visited in 2 months
            }
            $state_name = $this->security->xss_clean($this->input->post("state_name"));
            $publisherId = $this->security->xss_clean($this->input->post("publisherid"));
            $subPublisherId = $this->security->xss_clean($this->input->post("subpublisherid"));
            //$categoryid = $this->security->xss_clean($this->input->post("categoryid"));
          //  $prefered_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser"));
            $residence = $this->security->xss_clean($this->input->post("statename"));
//            $selectgender = $this->security->xss_clean($this->input->post("selectgender"));
//            $dob = $this->security->xss_clean($this->input->post("dob"));
            $Tobacco_Nicotine_user = $this->security->xss_clean($this->input->post("Tobacco_Nicotine_user"));
//            $insurance= $this->security->xss_clean($this->input->post('insured-select'));
            $health = $this->security->xss_clean($this->input->post("health"));
            $selectPeople = $this->security->xss_clean($this->input->post("selectPeople"));
            $streetadd = $this->security->xss_clean($this->input->post('address-input'));
            $phone = $this->security->xss_clean($this->input->post("phone"));
            
            
            $formxml = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
    <life>
        <person>
            <fullname>
              $fullname
            </fullname>
            <email>
              $mails
            </email>
            <phone>
              $phone
            </phone>
            <statename>
              $residence
            </statename>
            <noofpeople>
                $selectPeople
            </noofpeople>
           <addicted>
              $Tobacco_Nicotine_user
            </addicted>
            <health>
              $health
            </health>
            <address>
                $streetadd
            </address>
          </person>
      </life>
                    
EOD;
                $savedata['ipaddress']= $_SERVER['REMOTE_ADDR'];
		$savedata['publisherId']= $publisherId;
		$savedata['sub_publisherId']= $subPublisherId;
		$savedata['categoryId']= Health_insurance;
		$savedata['category_name']='Health Insurance';
		$savedata['state_name']= $state_name;
		$savedata['recomanded_advertiser']= $this->security->xss_clean($topAdvsId);
		$savedata['form_data']=$formxml;
		$savedata['datetime']=time();
		
		$this->db->insert('ad_form_data',$savedata);
		$form_data_id= $this->db->insert_id();
                //if($isprevois  == 0 ){
                    $this->AddLeadInPurchase($form_data_id,$savedata['recomanded_advertiser']);
                    $this->CreditAccounts($savedata['recomanded_advertiser'], $savedata['publisherId'], $form_data_id,$categoryid);
		//}
		return true;
        }
        
        public function BusinessInsurance($topAdvsId){
            $mails=$this->security->xss_clean($this->input->post('email'));
            $name = $this->security->xss_clean($this->input->post("name"));
            $categoryid=$this->security->xss_clean($this->input->post('categoryid'));
           // $recomanded_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser"));
             $savedata['recomanded_advertiser']= $this->security->xss_clean($topAdvsId);
             $recomanded_advertiser = $savedata['recomanded_advertiser'];
            $isPrevoisCategory=$this->isPrevoiusVisitorForSameCategory($mails,BUSINESS_INSURANCE);
                //echo $isPrevoisCategory;
                if($isPrevoisCategory ==0 ){
                         //echo "sarvesh";
			  $this->sendmailforleadsForUniqueUser($mails, BUSINESS_INSURANCE,$name);
                         //exit();
			// for visitors how have visited in 1 day
		}
            $isprevois=$this->isPrevoiusVisitor($mails,BUSINESS_INSURANCE,$recomanded_advertiser);
            if($isprevois >0 ){
                $savedata['fraud'] = 1;
                    // for visitors how have visited in 2 months
            }
            $state_name = $this->security->xss_clean($this->input->post("state_name"));
            $publisherId = $this->security->xss_clean($this->input->post("publisherid"));
            $subPublisherId = $this->security->xss_clean($this->input->post("subpublisherid"));
            //$categoryid = $this->security->xss_clean($this->input->post("categoryid"));
            
            //business info
          //  $prefered_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser"));
            //$fullemployees = $this->security->xss_clean($this->input->post("full_employees"));
            //$parttime_employee = $this->security->xss_clean($this->input->post("parttime_employee"));
            $insurance_type = $this->security->xss_clean($this->input->post("insurance_type"));
            $business_policy = $this->security->xss_clean($this->input->post("business_policy"));
            $business_address = $this->security->xss_clean($this->input->post("business_address"));
            //echo $insurance_type;
            //personal info
            
            $dob = $this->security->xss_clean($this->input->post("dob"));
            $phone = $this->security->xss_clean($this->input->post("phone"));
            
            $formxml = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
  <businessinsurance>
          <businessinsurancetype>
            $insurance_type
          </businessinsurancetype>
          <businesspolicy>
            $business_policy
          </businesspolicy>
          <businessaddress>
            $business_address
          </businessaddress>

          <name>
            $name
          </name>
          <dateofbirth>
            $dob
          </dateofbirth>                
          <email>
            $mails
          </email>
          <phone>
            $phone
          </phone>
  </businessinsurance>
EOD;
                $savedata['ipaddress']= $_SERVER['REMOTE_ADDR'];
		$savedata['publisherId']= $publisherId;
		$savedata['sub_publisherId']= $subPublisherId;
		$savedata['categoryId']= BUSINESS_INSURANCE;
		$savedata['category_name']='Business Insurance';
		$savedata['state_name']= $state_name;
		$savedata['recomanded_advertiser']= $this->security->xss_clean($topAdvsId);
		$savedata['form_data']=$formxml;
		$savedata['datetime']=time();
		
		$this->db->insert('ad_form_data',$savedata);
		$form_data_id= $this->db->insert_id();
               // if($isprevois == 0 ){
                    $this->AddLeadInPurchase($form_data_id,$savedata['recomanded_advertiser']);
                    $this->CreditAccounts($savedata['recomanded_advertiser'], $savedata['publisherId'], $form_data_id,$categoryid);
               // }
		return true;
        }
     public function TravelInsurance($topAdvsId){
            $name = $this->security->xss_clean($this->input->post("name"));
            $mails=$this->security->xss_clean($this->input->post('email'));
            $categoryid=$this->security->xss_clean($this->input->post('categoryid'));
            $savedata['recomanded_advertiser']= $this->security->xss_clean($topAdvsId);
            $recomanded_advertiser = $savedata['recomanded_advertiser'];
           // $recomanded_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser")); 
             $isPrevoisCategory=$this->isPrevoiusVisitorForSameCategory($mails,TRAVEL_INSURANCE);
                //echo $isPrevoisCategory;
                if($isPrevoisCategory ==0 ){
                         //echo "sarvesh";
			 $this->sendmailforleadsForUniqueUser($mails, TRAVEL_INSURANCE,$name);
                         //exit();
			// for visitors how have visited in 1 day
		}
            $isprevois=$this->isPrevoiusVisitor($mails,TRAVEL_INSURANCE,$recomanded_advertiser);
            if($isprevois >0 ){
                $savedata['fraud'] = 1;
                    // for visitors how have visited in 2 months
            }
            $state_name = $this->security->xss_clean($this->input->post("state_name"));
            $publisherId = $this->security->xss_clean($this->input->post("publisherid"));
            $subPublisherId = $this->security->xss_clean($this->input->post("subpublisherid"));
            //$categoryid = $this->security->xss_clean($this->input->post("categoryid"));
            
            //travel Info
            //$prefered_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser"));
            $travel_location = $this->security->xss_clean($this->input->post("travel_location"));
            $location_name = '';
            if(trim($travel_location) == 'Outside-The-Country'){
                $location_name = $this->security->xss_clean($this->input->post('location_name'));
            }
            //$travelling_country = $this->security->xss_clean($this->input->post("travelling_country"));
            $travel_start_date = $this->security->xss_clean($this->input->post("travel_start_date"));
            $travel_duration = $this->security->xss_clean($this->input->post("travel_duration"));
            $policy_type = $this->security->xss_clean($this->input->post("policy_type"));
            $require_cover_high_value_items = $this->security->xss_clean($this->input->post("require_cover_high_value_items"));
            $coverperson = $this->security->xss_clean($this->input->post("cover_persons"));
            //personal info
            
            $dob = $this->security->xss_clean($this->input->post("dob"));
            $phone = $this->security->xss_clean($this->input->post("phone"));
            
            $formxml = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
  <travelinsurance>
          <travellocation>
            $travel_location
          </travellocation>
          <location_name>
            $location_name
          </location_name>
          <travelstartdate>
            $travel_start_date
          </travelstartdate>
          <travelduration>
            $travel_duration
          </travelduration>
          <policytype>
            $policy_type
          </policytype>
          <coverhighvalueitem>
            $require_cover_high_value_items
          </coverhighvalueitem>
          <name>
            $name
          </name>
          <dateofbirth>
            $dob
          </dateofbirth>                
          <email>
            $mails
          </email>
          <phone>
            $phone
          </phone>
           <coverperson>
            $coverperson
          </coverperson>         
  </travelinsurance>
EOD;
                $savedata['ipaddress']= $_SERVER['REMOTE_ADDR'];
		$savedata['publisherId']= $publisherId;
		$savedata['sub_publisherId']= $subPublisherId;
		$savedata['categoryId']= TRAVEL_INSURANCE;
		$savedata['category_name']='Travel Insurance';
		$savedata['state_name']= $state_name;
		$savedata['recomanded_advertiser']= $this->security->xss_clean($topAdvsId);
		$savedata['form_data']=$formxml;
		$savedata['datetime']=time();
		
		$this->db->insert('ad_form_data',$savedata);
		$form_data_id= $this->db->insert_id();
                //if($isprevois == 0 ){
                    $this->AddLeadInPurchase($form_data_id,$savedata['recomanded_advertiser']);
                    $this->CreditAccounts($savedata['recomanded_advertiser'], $savedata['publisherId'], $form_data_id,$categoryid);
               // }
		return true;
    }
    public function EducationInsurance(){
        $mails=$this->security->xss_clean($this->input->post('email'));
        $this->sendmailforleads($mails);
       $recomanded_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser")); 
        $isprevois=$this->isPrevoiusVisitor($mails,TRAVEL_INSURANCE,$recomanded_advertiser);
        if($isprevois >0 ){
            $savedata['fraud'] = 1;
                // for visitors how have visited in 2 months
        }
        $state_name = $this->security->xss_clean($this->input->post("state_name"));
        $publisherId = $this->security->xss_clean($this->input->post("publisherid"));
        $subPublisherId = $this->security->xss_clean($this->input->post("subpublisherid"));
        //$categoryid = $this->security->xss_clean($this->input->post("categoryid"));

        //education Info
        $prefered_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser"));
        $degreelevel = $this->security->xss_clean($this->input->post("degreelevel"));
        $area = $this->security->xss_clean($this->input->post("areaofinterest"));
        
        //personal info
        $name = $this->security->xss_clean($this->input->post("fullname"));
        $phone = $this->security->xss_clean($this->input->post("phone"));

        $formxml = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<educationinsurance>
      <degreelevel>
        $degreelevel
      </degreelevel>
      <area>
        $area
      </area>
      <name>
        $name
      </name>             
      <email>
        $mails
      </email>
      <phone>
        $phone
      </phone>
</educationinsurance>
EOD;
            $savedata['ipaddress']= $_SERVER['REMOTE_ADDR'];
            $savedata['publisherId']= $publisherId;
            $savedata['sub_publisherId']= $subPublisherId;
            $savedata['categoryId']= Education_INSURANCE;
            $savedata['category_name']='Education Insurance';
            $savedata['state_name']= $state_name;
            $savedata['recomanded_advertiser']= $prefered_advertiser;
            $savedata['form_data']=$formxml;
            $savedata['datetime']=time();

            $this->db->insert('ad_form_data',$savedata);
            $form_data_id= $this->db->insert_id();
            //if($isprevois == 0 ){
                $this->AddLeadInPurchase($form_data_id,$savedata['recomanded_advertiser']);
                $this->CreditAccounts($savedata['recomanded_advertiser'], $savedata['publisherId'], $form_data_id,Education_INSURANCE);
                    // for visitors how have visited in 2 months
           // }
            
            return true;
    }
    public function PaydayLoan(){
        $mails=$this->security->xss_clean($this->input->post('email'));
        $this->sendmailforleads($mails);
        $recomanded_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser")); 
        $isprevois=$this->isPrevoiusVisitor($mails,TRAVEL_INSURANCE,$recomanded_advertiser);
        if($isprevois >0 ){
            $savedata['fraud'] = 1;
                // for visitors how have visited in 2 months
        }
        $state_name = $this->security->xss_clean($this->input->post("state_name"));
        $publisherId = $this->security->xss_clean($this->input->post("publisherid"));
        $subPublisherId = $this->security->xss_clean($this->input->post("subpublisherid"));
        //$categoryid = $this->security->xss_clean($this->input->post("categoryid"));
        
        // PAYDAY_LOAN Info
        $prefered_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser"));
        $loan_amount = $this->security->xss_clean($this->input->post("loan_amount"));
        $fullname = $this->security->xss_clean($this->input->post("fullname"));
        $dob = $this->security->xss_clean($this->input->post("dob"));
        $residence = $this->security->xss_clean($this->input->post("residence"));
        $contacttime = $this->security->xss_clean($this->input->post("contacttime"));
        $streetaddress = $this->security->xss_clean($this->input->post("streetaddress"));
        $phone = $this->security->xss_clean($this->input->post("phone"));
        $incomesource = $this->security->xss_clean($this->input->post("incomesource"));
        $netincome = $this->security->xss_clean($this->input->post("netincome"));
        $employername = $this->security->xss_clean($this->input->post("employername"));
        $jobtitle = $this->security->xss_clean($this->input->post("jobtitle"));
        $timeemployed = $this->security->xss_clean($this->input->post("timeemployed"));
        
        $formxml = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<paydayloan>
      <loanamount>
        $loan_amount
      </loanamount>
      <fullname>
        $fullname
      </fullname>
      <dob>
        $dob
      </dob>
      <email>
        $mails
      </email>
      <residence>
        $residence
      </residence>
      <contacttime>
        $contacttime
      </contacttime>
      <streetaddress>
        $streetaddress
      </streetaddress>
      <phone>
        $phone
      </phone>
      <incomesource>
        $incomesource
      </incomesource>
      <netincome>
        $netincome
      </netincome>
      <employername>$employername</employername>
      <jobtitle>$jobtitle</jobtitle>
      <timeemployed>$timeemployed</timeemployed>   
</paydayloan>
EOD;
        
        $savedata['ipaddress']= $_SERVER['REMOTE_ADDR'];
        $savedata['publisherId']= $publisherId;
        $savedata['sub_publisherId']= $subPublisherId;
        $savedata['categoryId']= PAYDAY_LOAN;
        $savedata['category_name']='Payday Loan';
        $savedata['state_name']= $state_name;
        $savedata['recomanded_advertiser']= $prefered_advertiser;
        $savedata['form_data']=$formxml;
        $savedata['datetime']=time();

        $this->db->insert('ad_form_data',$savedata);
        $form_data_id= $this->db->insert_id();
       // if($isprevois == 0 ){
            $this->AddLeadInPurchase($form_data_id,$savedata['recomanded_advertiser']);
            $this->CreditAccounts($savedata['recomanded_advertiser'], $savedata['publisherId'], $form_data_id,PAYDAY_LOAN);
       // }
        return true;
    }
    public function Hotel(){
        $mails=$this->security->xss_clean($this->input->post('email'));
        $this->sendmailforleads($mails);
        $recomanded_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser")); 
        $isprevois=$this->isPrevoiusVisitor($mails,TRAVEL_INSURANCE,$recomanded_advertiser);
        if($isprevois >0 ){
            $savedata['fraud'] = 1;
                // for visitors how have visited in 2 months
        }
        $state_name = $this->security->xss_clean($this->input->post("state_name"));
        $publisherId = $this->security->xss_clean($this->input->post("publisherid"));
        $subPublisherId = $this->security->xss_clean($this->input->post("subpublisherid"));
        //$categoryid = $this->security->xss_clean($this->input->post("categoryid"));
        
        // Hotel Info
        $prefered_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser"));
        $preferedstate = $this->security->xss_clean($this->input->post("preferedstate"));
        $city = $this->security->xss_clean($this->input->post("city"));
        $checkin = $this->security->xss_clean($this->input->post("checkin"));
        $checkout = $this->security->xss_clean($this->input->post("checkout"));
        $rooms = $this->security->xss_clean($this->input->post("rooms"));
        $budget = $this->security->xss_clean($this->input->post("budget"));
        $pickupservice = $this->security->xss_clean($this->input->post("pickupservice"));
        $pickupinfo = $this->security->xss_clean($this->input->post("pickupinfo"));
        $fullname = $this->security->xss_clean($this->input->post("fullname"));
        $phone = $this->security->xss_clean($this->input->post("phone"));
        
        $formxml = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<hotel>
      <preferedstate>
        $preferedstate
      </preferedstate>
      <city>
        $city
      </city>
      <checkin>
        $checkin
      </checkin>             
      <checkout>
        $checkout
      </checkout>
      <rooms>
        $rooms
      </rooms>
      <budget>
        $budget
      </budget>
      <pickupservice>
        $pickupservice
      </pickupservice>
      <pickupinfo>
        $pickupinfo
      </pickupinfo>
      <fullname>
        $fullname
      </fullname>
      <phone>
        $phone
      </phone>
    <email>
        $mails
    </email>
</hotel>
EOD;
        
        $savedata['ipaddress']= $_SERVER['REMOTE_ADDR'];
        $savedata['publisherId']= $publisherId;
        $savedata['sub_publisherId']= $subPublisherId;
        $savedata['categoryId']= HOTELS;
        $savedata['category_name']='Hotel';
        $savedata['state_name']= $state_name;
        $savedata['recomanded_advertiser']= $prefered_advertiser;
        $savedata['form_data']=$formxml;
        $savedata['datetime']=time();

        $this->db->insert('ad_form_data',$savedata);
        $form_data_id= $this->db->insert_id();
       // if($isprevois == 0 ){
            $this->AddLeadInPurchase($form_data_id,$savedata['recomanded_advertiser']);
            $this->CreditAccounts($savedata['recomanded_advertiser'], $savedata['publisherId'], $form_data_id,HOTELS);
       // }
        return true;
    }
    public function sendmailforleads($mails){
        
        $mailData['Site_name']  = SITE_NAME;
        $mailData['site_number'] = SITE_CUSTOMER_CARE;
        $mailData['{base_url}'] = $this->config->base_url();

        require_once("mailer/Email.php");
        $emailSender = new Email();
        $emailSender->SendEmail('request_for_quotes',$mailData,$mails,ADMIN_MAIL,ADMIN_MAIL_PASSWORD,SITE_NAME,'Your Request for Insurance Quotes');
       
    }
    public function sendmailforthirdpartyleads($mails){
        $mailData['Site_name']  = SITE_NAME;
        $mailData['site_number'] = SITE_CUSTOMER_CARE;
        $mailData['{base_url}'] = $this->config->base_url();
         
        require_once("mailer/Email.php");
        $emailSender = new Email();
        $emailSender->SendEmail('request_for_thirdparty_quotes',$mailData,$mails,ADMIN_MAIL,ADMIN_MAIL_PASSWORD,'Insurance Quotes','Your Third Party Auto Insurance Policy');
        
    }
    
    
    //these models are added by ravindra
     public function carLoan(){
        $mails=$this->security->xss_clean($this->input->post('email'));
        $this->sendmailforleads($mails);
       $recomanded_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser")); 
        $isprevois=$this->isPrevoiusVisitor($mails,CAR_LOAN,$recomanded_advertiser);
        if($isprevois >0 ){
            $savedata['fraud'] = 1;
                // for visitors how have visited in 2 months
        }
        $state_name = $this->security->xss_clean($this->input->post("state_name"));
        $publisherId = $this->security->xss_clean($this->input->post("publisherid"));
        $subPublisherId = $this->security->xss_clean($this->input->post("subpublisherid"));
        //$categoryid = $this->security->xss_clean($this->input->post("categoryid"));
        
        // CAR_LOAN Info
        $empname = $this->security->xss_clean($this->input->post("empName"));
        $empphone = $this->security->xss_clean($this->input->post("empPhone"));
        $timeatjob = $this->security->xss_clean($this->input->post("timeatJob"));
        //$empname = $this->security->xss_clean($this->input->post("empName"));
        $occupation = $this->security->xss_clean($this->input->post("occupation"));
        $income = $this->security->xss_clean($this->input->post("monthlyIncome"));
        $prefered_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser"));
        $birthdate = $this->security->xss_clean($this->input->post("birthdate"));
        $crdProb = $this->security->xss_clean($this->input->post("crdProb"));
        $timeatResidence = $this->security->xss_clean($this->input->post("timeatResidence"));
        $rent = $this->security->xss_clean($this->input->post("rent"));
        $curr = $this->security->xss_clean($this->input->post("curr"));
       //echo $timeatResidance;
        $formxml = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<carloan>
      <empname>
        $empname
      </empname>
      <empphone>
        $empphone
      </empphone>
       <email>
        $mails
      </email>
      <timeatjob>
        $timeatjob
      </timeatjob>
      <occupation>
        $occupation
      </occupation>
      <income>
        $income
      </income>
      <birthdate>
        $birthdate
      </birthdate>
      <crdProb>
        $crdProb
      </crdProb>
      <timeatResidence>
        $timeatResidence
      </timeatResidence>
      <rent>$rent</rent>
      <curr>$curr</curr>       
</carloan>
EOD;
        
        $savedata['ipaddress']= $_SERVER['REMOTE_ADDR'];
        $savedata['publisherId']= $publisherId;
        $savedata['sub_publisherId']= $subPublisherId;
        $savedata['categoryId']= CAR_LOAN;
        $savedata['category_name']='Car Loan';
        $savedata['state_name']= $state_name;
        $savedata['recomanded_advertiser']= $prefered_advertiser;
        $savedata['form_data']=$formxml;
        $savedata['datetime']=time();

        $this->db->insert('ad_form_data',$savedata);
        $form_data_id= $this->db->insert_id();
       // if($isprevois == 0 ){
            $this->AddLeadInPurchase($form_data_id,$savedata['recomanded_advertiser']);
            $this->CreditAccounts($savedata['recomanded_advertiser'], $savedata['publisherId'], $form_data_id,CAR_LOAN);
        //}
        return true;
    }
    
     public function autoQuotes(){
        $mails=$this->security->xss_clean($this->input->post('email'));
        $this->sendmailforleads($mails);
        $recomanded_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser")); 
        $isprevois=$this->isPrevoiusVisitor($mails,AUTO_QUOTES, $recomanded_advertiser);
        if($isprevois >0 ){
            $savedata['fraud'] = 1;
                // for visitors how have visited in 2 months
        }
        $state_name = $this->security->xss_clean($this->input->post("state_name"));
        $publisherId = $this->security->xss_clean($this->input->post("publisherid"));
        $subPublisherId = $this->security->xss_clean($this->input->post("subpublisherid"));
        //$categoryid = $this->security->xss_clean($this->input->post("categoryid"));
        
        // Auto_quotes Info
        $vyear = $this->security->xss_clean($this->input->post("vyear"));
        $vmake = $this->security->xss_clean($this->input->post("vmake"));
        $vmodel = $this->security->xss_clean($this->input->post("vmodel"));
        $extcolour = $this->security->xss_clean($this->input->post("extColour"));
        $intcolour = $this->security->xss_clean($this->input->post("intColour"));
        $btFrame = $this->security->xss_clean($this->input->post("btFrame"));
        $paymentmethod = $this->security->xss_clean($this->input->post("paymentMethod"));
        $prefered_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser"));
        $fname = $this->security->xss_clean($this->input->post("fname"));
        $address = $this->security->xss_clean($this->input->post("address"));
        $state = $this->security->xss_clean($this->input->post("state"));
        $phoneno = $this->security->xss_clean($this->input->post("phoneNo"));
       // $email = $this->security->xss_clean($this->input->post("email"));
        $contact = $this->security->xss_clean($this->input->post("contact"));
       
        $formxml = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<autoquotes>
      <vyear>
        $vyear
      </vyear>
      <vmake>
        $vmake
      </vmake>
      <vmodel>
        $vmodel
      </vmodel>
      <extcolour>
        $extcolour
      </extcolour>
      <intcolour>
        $intcolour
      </intcolour>
      <btframe>
        $btFrame
      </btframe>
      <paymentmethod>
        $paymentmethod
      </paymentmethod>
      <fname>
        $fname
      </fname>
      <address>
        $address
      </address>
      <state>$state</state>
      <phone>$phoneno</phone>
      <email>$mails</email>
      <contact>$contact</contact>            
</autoquotes>
EOD;
        
        $savedata['ipaddress']= $_SERVER['REMOTE_ADDR'];
        $savedata['publisherId']= $publisherId;
        $savedata['sub_publisherId']= $subPublisherId;
        $savedata['categoryId']= AUTO_QUOTES;
        $savedata['category_name']='Auto Quotes';
        $savedata['state_name']= $state;
        $savedata['recomanded_advertiser']= $prefered_advertiser;
        $savedata['form_data']=$formxml;
        $savedata['datetime']=time();

        $this->db->insert('ad_form_data',$savedata);
        $form_data_id= $this->db->insert_id();
        //if($isprevois == 0 ){
            $this->AddLeadInPurchase($form_data_id,$savedata['recomanded_advertiser']);
            $this->CreditAccounts($savedata['recomanded_advertiser'], $savedata['publisherId'], $form_data_id,AUTO_QUOTES);
       // }
        return true;
    }
    
    public function thirdpartyonly(){
        $mails=$this->security->xss_clean($this->input->post('email'));
        //$this->sendmailforthirdpartyleads($mails);
        $isPrevoisCategory=$this->isPrevoiusVisitorForSameCategory($mails,THIRD_PARTY_ONLY);
                //echo $isPrevoisCategory;
                if($isPrevoisCategory ==0 ){
                         //echo "sarvesh";
			 $this->sendmailforthirdpartyleads($mails);
                         //exit();
			// for visitors how have visited in 1 day
		}
        $recomanded_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser")); 
        $isprevois=$this->isPrevoiusVisitor($mails,THIRD_PARTY_ONLY,$recomanded_advertiser);
        if($isprevois >0 ){
            $savedata['fraud'] = 1;
                // for visitors how have visited in 2 months
        }
        $state_name = $this->security->xss_clean($this->input->post("state_name"));
        $publisherId = $this->security->xss_clean($this->input->post("publisherid"));
        $subPublisherId = $this->security->xss_clean($this->input->post("subpublisherid"));
        //$categoryid = $this->security->xss_clean($this->input->post("categoryid"));
        
        // Third_Party Info
        //$vyear = $this->security->xss_clean($this->input->post("vyear"));
        //$vmake = $this->security->xss_clean($this->input->post("vmake"));
        //$vmodel = $this->security->xss_clean($this->input->post("vmodel"));
        $prefered_advertiser = $this->security->xss_clean($this->input->post("prefered_advertiser"));
        $firstname = $this->security->xss_clean($this->input->post("firstname"));
        $lastname = $this->security->xss_clean($this->input->post("lastname"));
        //$streetaddress = $this->security->xss_clean($this->input->post("streetaddress"));
        $state = $this->security->xss_clean($this->input->post("state"));
        $telephone = $this->security->xss_clean($this->input->post("telephone"));
       // $email = $this->security->xss_clean($this->input->post("email"));
        
        $formxml = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<thirdparty>
      <firstname>
        $firstname
      </firstname> 
      <lastname>
        $lastname
      </lastname>    
      <state>$state</state>
      <telephone>$telephone</telephone>
      <email>$mails</email>            
</thirdparty>
EOD;
        
        $savedata['ipaddress']= $_SERVER['REMOTE_ADDR'];
        $savedata['publisherId']= $publisherId;
        $savedata['sub_publisherId']= $subPublisherId;
        $savedata['categoryId']= THIRD_PARTY_ONLY;
        $savedata['category_name']='Third Party Only';
        $savedata['state_name']= $state;
        $savedata['recomanded_advertiser']= $prefered_advertiser;
        $savedata['form_data']=$formxml;
        $savedata['datetime']=time();

        $this->db->insert('ad_form_data',$savedata);
        $form_data_id= $this->db->insert_id();
        //if($isprevois == 0 ){
            $this->AddLeadInPurchase($form_data_id,$savedata['recomanded_advertiser']);
            $this->CreditAccounts($savedata['recomanded_advertiser'], $savedata['publisherId'], $form_data_id,THIRD_PARTY_ONLY);
       // }
        return true;
    }
    
    public function getthirdpartylink($userID){
    	
		$this -> db -> select('Third_Party_Only');
		$this -> db -> from('user_profile');
		$this -> db -> where('userId', $userID);
		$query = $this -> db -> get();
		$re= $query->result();
               // return $re['Third_Party_Only'];
                 return $re[0]->Third_Party_Only;
		
    }
    
    public function getRestrictUsers($userId){
        $this -> db -> select('restrict_adv_id');
        $this -> db -> from('thirdparty_restricted');
        $this -> db -> where('publisher_id', $userId);
        $query = $this -> db -> get();
        return $query->result();
      
    }
    //by sarvesh
    public function getAdvDetail($savedata) {
        $this->db->select('*');
        $this->db->from("user_profile");
        $this->db->where('userId',$savedata);
        $query = $this->db->get();
        return $query->result();
    }
    public function getLeadalertSetting() {
          $this->db->select('*');
          $this->db->from('admin_info');
          $query = $this->db->get();
          return $res = $query->result();
      }
    
    //end

      //code  by sarvesh
    public function getManageBannerUpdateData($advertiserIds) {
        $this->db->reconnect();
        $this->db->select('*');
        $this->db->from('manage_banner');
        
        $this->db->where_in('advertiser_id', $advertiserIds);
        $query = $this->db->get();
        return  $query->result();
    }
    	public function getPreferedAdvertisersValue($catId){
		$advs = array();
		$query=$this->db->query("SELECT userId,name,activecategory,email FROM user_profile where userType = 1 and isActive = 1 and isAccepted = 1" );
		$results = $query->result();
                $balance = $this->getUsersBalance();
                $advForUpdateAndMail = array();
                //print_r($results);
                foreach ($results as $adv){
                    $catstr = $adv->activecategory;
                    $catPriceArr = strlen($catstr) > 0 ? explode(",", $catstr) : array();
                    foreach($catPriceArr as $cp){
                        $catpr = isset($cp) && strlen($cp) > 0  ? explode(":", $cp) : NULL;
                        $cat = isset($catpr[0]) ? $catpr[0] : NULL;
                        $pr = isset($catpr[1]) ? $catpr[1] : NULL;
                        if(isset($cat) && isset($pr)){
                            if($cat == $catId){
                                // now we have to check that this adv have enough balance in his account.
                                // if not so . then add him in $advForUpdateAndMail array and send him mail.
                                foreach($balance as $bal){
                                    if($adv->userId == $bal->userId){
                                        // if total balance of advertiser is greater than bid price of that category
                                        if($bal->balance >= $pr){
                                            if(isset($advs[$pr])){
                                                $newArray = $advs[$pr];
                                            }else{
                                                $newArray = array();
                                            }
                                            $newArray = array_merge($newArray,array($adv));
                                            //if(isset($advs[]))
                                            $advs[$pr] = $newArray;
                                            
                                            
                                            // now perform sorting..
                                            /*foreach($advs as $advert){
                                                
                                                $i ++;
                                            }*/
                                            /*for($i=0;$i<count($advs);$i++){
                                                for($j=0;$j<count($advs);$j++){
                                                    if($advs[$i] > $advs[$j]){
                                                        $temp = $advs[$i];
                                                        $advs[$i] = $advs[$j];
                                                        $advs[$j] = $temp;
                                                    }
                                                }
                                            }*/
                                        }else{
                                            $advForUpdateAndMail[$adv->userId] = $adv;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
                if(count($advForUpdateAndMail) > 0){
                    $this->updateAdvAndSendMail($advForUpdateAndMail);
                }
                
                // sort associative array by its key value
                ksort($advs,SORT_NUMERIC);
                $advs = array_reverse($advs,true);
                //print_r($advs);
                return $advs;
	}
        public function getSelectedAdvertisers($userID) {
            $this->db->select('name');
            $this->db->from('user_profile');
            $this->db->where('userId',$userID);
            $query = $this->db->get();
            return $query->result();
        }
        public function getSelectedAdvertisersData($userID) {
            $this->db->select('*');
            $this->db->from('user_profile');
            $this->db->where('userId',$userID);
            $query = $this->db->get();
            return $query->result();
        }
        
        //code by sarvesh(14/10/2014)
        
        public function isPrevoiusVisitorForSameCategory($emailID, $catId){
            //echo "sarvesh";
            //echo $emailID;
           // echo $catId;
		//echo $recomanded_advertiser;
		//this is the time before a month
		//$previousTime=time()-2592000;
            //this is the time before 2 weeks
                $previousTime=time()-86400;
		$emailid=$emailID;
		
		$this->db->select(" * FROM ad_form_data where form_data like '%$emailid%' and datetime > $previousTime and categoryId=$catId");
		$query= $this -> db -> get();
		$res= $query->result();
                //echo count($res);
		return count($res);
	}
    public function sendmailforleadsForUniqueUser($mails,$catId,$name){
        $mailData['user_name'] = $name; 
        $mailData['Site_name']  = SITE_NAME;
        $mailData['site_number'] = SITE_CUSTOMER_CARE;
        $mailData['{base_url}'] = $this->config->base_url();
        //echo "pandey";
        //echo $catId;
        require_once("mailer/Email.php");
        $emailSender = new Email();
        if($catId===AUTO_INSURANCE){
        $emailSender->SendEmail('request_for_quotes_AUTO_INSURANCE',$mailData,$mails,ADMIN_MAIL,ADMIN_MAIL_PASSWORD,SITE_NAME,'Your Request for Insurance Quotes');
        }elseif($catId===LIFE_INSURANCE){
            $emailSender->SendEmail('request_for_quotes_LIFE_INSURANCE',$mailData,$mails,ADMIN_MAIL,ADMIN_MAIL_PASSWORD,SITE_NAME,'Your Request for Insurance Quotes');
        }elseif($catId===BUSINESS_INSURANCE){
            $emailSender->SendEmail('request_for_quotes_BUSINESS_INSURANCE',$mailData,$mails,ADMIN_MAIL,ADMIN_MAIL_PASSWORD,SITE_NAME,'Your Request for Insurance Quotes');
        }elseif($catId===TRAVEL_INSURANCE){
            $emailSender->SendEmail('request_for_quotes_TRAVEL_INSURANCE',$mailData,$mails,ADMIN_MAIL,ADMIN_MAIL_PASSWORD,SITE_NAME,'Your Request for Insurance Quotes');
        }elseif($catId===Health_insurance){
            $emailSender->SendEmail('request_for_quotes_Health_insurance',$mailData,$mails,ADMIN_MAIL,ADMIN_MAIL_PASSWORD,SITE_NAME,'Your Request for Insurance Quotes');
        }elseif($catId===HOME_INSURANCE){
            $emailSender->SendEmail('request_for_quotes_HOME_INSURANCE',$mailData,$mails,ADMIN_MAIL,ADMIN_MAIL_PASSWORD,SITE_NAME,'Your Request for Insurance Quotes');
        }
    }
    //End sarvesh code
    //By neeta
    public function getBannerOfCategory($catId){
        $this->db->select('*');
        $this->db->where('cat_id',$catId);
        $query = $this->db->get('manage_form_banner');
        return $query->result();
    }
    public function getLeadQuotes($userId,$catId){
            $this->db->select("*");
            $this->db->where("user_id" , $userId);
            $this->db->where("cat_id" , $catId);
            $query = $this->db->get("lead_quotes");
            $row = $query->row();
            return $row;
    }
    public function getCategoryOfId($catId){
        $this->db->select("*");
        $this->db->where("categoryId" , $catId);
        $query = $this->db->get("categories");
        $row = $query->row();
        return $row;
    }
    public function checkUserSMSAlert($phone,$catId){
        $currentTime = time();
        $this->db->select("*");
        $this->db->where("category_id" , $catId);
        $this->db->where("user_phone" , $phone);
        $this->db->where("start_time <" , $currentTime);
        $this->db->where("end_time >" , $currentTime);
        $query = $this->db->get("user_sms_lead_alert");
        $result = $query->result();
        return $result;
    }
    public function insertUserSMSAlert($data){
        $this->db->insert('user_sms_lead_alert', $data); 
    }
    public function isPrevoiusVisitorForSameCategoryWithPhone($phone, $catId,$topAdvsId){
                $previousTime=time()-86400;
		$this->db->select(" * FROM ad_form_data where form_data like '%$phone%' and datetime > $previousTime and isRoleback = 0 and categoryId=$catId and recomanded_advertiser = $topAdvsId");
		$query= $this -> db -> get();
		$res= $query->result();
                return count($res);
	}
        public function checkUserActiveCategory($userid,$categoryid){
                $advs = array();
		$query=$this->db->query("SELECT userId,name,activecategory,email FROM user_profile where userType = 1 and isActive = 1 and isAccepted = 1 and userId = $userid" );
		$results = $query->row();
                $exists = false;
                if(isset($results) && is_object($results) && !empty($results)){
                    $catstr = $results->activecategory;
                    $catPriceArr = strlen($catstr) > 0 ? explode(",", $catstr) : array();
                    if(isset($catPriceArr) && is_array($catPriceArr) && !empty($catPriceArr)){
                        foreach($catPriceArr as $cp){
                            $catpr = isset($cp) && strlen($cp) > 0  ? explode(":", $cp) : NULL;
                            $cat = isset($catpr[0]) ? $catpr[0] : NULL;
                            $pr = isset($catpr[1]) ? $catpr[1] : NULL;
                            if(isset($cat) && isset($pr)){
                                if($cat == $categoryid){
                                    $exists = true;
                                    break;
                                }else{
                                    $exists = false;
                                }
                            }
                        }
                    }
                    if($exists){
                        return true;
                    }
                }else{
                    return false;
                }
                
                
        }
}