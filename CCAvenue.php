<?php
namespace ramprasadm1986\ccavenue\CCAvenue;

use ramprasadm1986\ccavenue\Crypto;

class CCAvenue
{
    
   
    public $live;
    public $merchant_id;
    public $access_code;
    public $working_key;
    public $cancel_url;
    public $redirect_url;
    public $language;
    
    public $url;
    
   
    
    
    
    private function InitClient()
    {
        
        
       if(!$this->live)
        $this->url = "https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction";
       else
        $this->url = "https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction";
    
       if($this->language=="")
       $this->language="EN";
       
    }
    
    public function createRequest($data){
        $this->InitClient();
        $merchant_data='';
        foreach ($_POST as $key => $value){
            $merchant_data.=$key.'='.$value.'&';
        }
        $merchant_data.='merchant_id='.$this->merchant_id.'&';
        $merchant_data.='redirect_url='.$this->redirect_url.'&';
        $merchant_data.='cancel_url='.$this->cancel_url.'&';
        $merchant_data.='language='.$this->language.'&';
        
        $encrypted_data=Crypto::encrypt( $merchant_data,$this->working_key);
        
        return ['access_code'=>$this->access_code,"encrypted_data"=>$encrypted_data];
    }
    
    
    public function processResponse($response){
        $this->InitClient();
        
        
        $encResponse=$response['encResp'];
        
        $rcvdString=Crypto::decrypt($encResponse,$this->working_key);
        
        $order_status="";
        $decryptValues=explode('&', $rcvdString);
        $dataSize=sizeof($decryptValues);
        
        for($i = 0; $i < $dataSize; $i++) 
        {
            $information=explode('=',$decryptValues[$i]);
            if($i==3)	$order_status=$information[1];
        }
        if(! in_array($order_status,["Success","Aborted","Failure"]))
            $order_status="Illegal access detected";
            
        $data=[];
        
        
        for($i = 0; $i < $dataSize; $i++) 
            {
                $information=explode('=',$decryptValues[$i]);            
                $data[$information[0]]=$information[1];
                    
            }
        
        return ['status'=>$order_status,'data'=>$data];        
        
    }
    
    
    
}