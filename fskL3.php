<?php 
   function bcd($ascii){ $len =strlen($ascii); $result =''; if (!$len) return '';
                         for ($i=0; $i<$len-1; $i+=2) $result .=$ascii[$i+1] .$ascii[$i];
                         if (($len%2)==1) $result .='F' .$ascii[$len-1];
                         return $result;                                                             }
   function choose($arr, $key, $def) {  return array_key_exists($key, $arr) ?$arr[$key] :$def; }
   function semiOct($val) { return hex2bin(bcd($val)); }
   date_default_timezone_set('Europe/Sarajevo');
   function currentTimeStamp() { return semiOct( date('ymdHis')."04" ); }
   
   ///////////////L3 - PRESENTATION LAYER http://www.dreamfabric.com/sms
   $globalL3 ='';
   function encodeFskL3_smsSM2TE($params) //SMS_DELIVER
   {  $smscinfo =chr($params->smscaddr->type)     //type of address - international format default
                .$params->smscaddr->semiOct();    //'7283010010F5' // "+27381000015" (odd => +F)
      global $globalL3;
	  $globalL3 =//chr(strlen($smscinfo))           //Length of the SMSC information (in this case 7 octets)
                 //.$smscinfo.  //smsc-info(above)
                 "\x04"                           //first octet TP-MMS http://www.dreamfabric.com/sms/deliver_fo.html
                .chr($params->senderaddr->len)    //Address-Length
                .chr($params->senderaddr->type)   //'80' //type of address http://www.dreamfabric.com/sms/type_of_address.html
                .$params->senderaddr->semiOct()   //.'7283880900F1' //sender number + odd->f
                ."\x00" //pid
                ."\x00" //dcs
                .currentTimeStamp()               //time stamp http://www.dreamfabric.com/sms/scts.html
                .chr(strlen($params->smstext))    //udlength NOTE!!!: length of septets(7bits), it is 9 bytes below, but string length is 10(A)
                .ud827($params->smstext);         //'E8329BFD4697D9EC37'; //ud 7bit 'hellohello'
	  return $globalL3;
   }
   function encodeFskL3_smsTE2SM($params) //SMS-SUBMIT
   {  
      return     "\x00"                            //optional byte ??? smc info length 0
                ."\x11"                            //first octet of SMS-SUBMIT
                .chr($params->recvaddr->len)       //Address-Length
                .chr($params->recvaddr->type)      //'80' //type of address http://www.dreamfabric.com/sms/type_of_address.html
                .$params->recvaddr->semiOct()      //.'7283880900F1' //sender number + odd->f
                ."\x00"                            //pid
                ."\x00"                            //dcs
                ."\xAA"                            //validation period 4 days
                .chr(strlen($params->smstext))     //udlength NOTE!!!: length of septets(7bits), it is 9 bytes below, but string length is 10(A)
                .ud827($params->smstext);          //'E8329BFD4697D9EC37'; //ud 7bit 'hellohello'
   }
   function encodeFskL3_L2Err($params) { return chr($params->errcode); } 
   
   //////////////////ALL LEVELS:///////////////////
   $fskDllMessages =array(0x91=>'DLL_SMS_DATA', 0x92=>'DLL_SMS_ERROR', 0x93=>'DLL_SMS_EST',
                          0x94=>'DLL_SMS_REL',  0x95=>'DLL_SMS_ACK',   0x96=>'DLL_SMS_NACK');
   $msgType2Dll    =array(1=>0x91, 2=>0x91, 3=>0x92, 4=>0x93, 5=>0x94, 6=>0x95, 7=>0x96);
   function encodeFsk($msgType, $params)
   {  global $msgType2Dll; $fskL3bin ='';
      $dllmsg =choose($msgType2Dll, $msgType, 0);  if (!$dllmsg) { echo "err: dll type not valid."; return ''; }
      switch ($msgType)
      {  case 1: //SMS SM to TE
                 $fskL3bin =encodeFskL3_smsSM2TE($params); break;
         case 2: //SMS TE to SM
                 $fskL3bin =encodeFskL3_smsTE2SM($params); break;
         case 3: //L2 ERROR
                 $fskL3bin =encodeFskL3_L2Err($params);    break;
                 break;
         //L2 ESTABLISH //L2 RELEASE //L2 ACKNOWLEDGEMENT //L2 NOT ACKNOWLEDGE
         case 4:        case 5:      case 6:              case 7: 
                 break;
      }
      return encodeFskL1( encodeFskL2($dllmsg, $fskL3bin) );
   }
   ////////////////////////////////////////////////
   class cAddress
   {  public $len=0; public $type=0xA1; //national-isdn
      public $addr ='';
      public function __construct($asciiAddr, $type=0xA1)
      {  $this->addr =$asciiAddr;
         $this->len =strlen($asciiAddr);
         $this->type =$type;
      }
      public function semiOct() { return semiOct($this->addr); }
   }
   class cL3params //set it manually before call encodeFsk
   { public $smscaddr=0, $senderaddr=0, $smstext=0, $recvaddr =0, $errcode =0; } 
   /////////////////////////////////////////////////
   