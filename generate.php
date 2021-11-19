<?php require_once('base.php'); require_once("smscoding.php"); 
      require_once('fileau.php'); require_once('dtmf.php'); 
      require_once('fskL12.php'); require_once('fskL3.php');
                                     function getReq($varname, $defaultVal='')
                                     {  return array_key_exists($varname, $_REQUEST) ?$_REQUEST[$varname] :$defaultVal;     }

   $type      =getReq('type',      '');                   if (!$type) { echo "err: no type (dtmf/fsk) in request."; return; }
   
   $digits    =getReq('digits',    '');
   
   $smscphone =getreq('smscphone', ''); $senderphone    =getreq('senderphone',     '');
   $smstext   =getreq('smstext',   ''); $receiverphone  =getreq('receiverphone',   '');
   $errcode   =getreq('errcode',   ''); 
   $msgtype   =getreq('msgtype',   ''); 

   
   $systemfilefolder ="generated/sounds"; 
   $webfilefolder    ="generated/sounds"; //radi na racun ludih rewrite-rules-a
   $resultfilename ='' .time();
   
   //thanks to: christian schmidt, http://aggemam.dk/code/dtmf
   //samples per second
   $sample_rate    = 48000; //48000 semplova u sekundi, 1200 bps => 40 semplova za 1 bit
   $signal_length  =    80; $charLen  =$signal_length/1000;
   $break_length   =   100; $breakLen =$break_length /1000;
   $pause_length   =   100; $pauseLen =$pause_length /1000;
   $amplitude      =    64; //amplitude of wave file in the range 0-64
  
   $sampleangle =2*M_PI /$sample_rate;
   
   $samplesPerSecond =$sample_rate;
   $samplesPerBit    =floor($samplesPerSecond/1200); //40 samples for one bit      
   $angleStepPerSample =array(2100*$sampleangle, 1300*$sampleangle); //2100Hz=0,zero(space). http://www.itu.int/rec/T-REC-V.23-198811-I
                                                                     //1300Hz=1, mark. //bell uses 2200/1200hz v.23 etsi is 2100/1300   
   
//////////////MAIN:
   $preparedBin =''; $preparedLen =0;
   if ($type =='dtmf')     $preparedBin =createDTMF($digits, $samplesPerSecond, $charLen, $breakLen, $pauseLen, $amplitude);
   else if ($type =='fsk') 
   {  $params =new cL3params();
      switch ($msgtype)
      {  case 1: //SMS SM to TE
                 $params->smscaddr   =new cAddress($smscphone);
                 $params->senderaddr =new cAddress($senderphone);
                 $params->smstext    =$smstext;
                 break;
         case 2: //SMS TE to SM
                 $params->recvaddr   =new cAddress($receiverphone);
                 $params->smstext    =$smstext;
                 break;
         case 3: //L2 ERROR
                 $params->errcode =$errcode; 
                 break;
         //L2 ESTABLISH //L2 RELEASE //L2 ACKNOWLEDGEMENT //L2 NOT ACKNOWLEDGE
         case 4:        case 5:      case 6:              case 7: 
                 break;
      }
      $preparedBin =encodeFsk($msgtype, $params); 
   }
   else { echo 'err: unknown type'; return; }
   if (!($preparedLen =strlen($preparedBin))) { echo "err: zero bytes created."; return; }
   
   ///////file create//////
   createAuWav($preparedBin, "$systemfilefolder/$resultfilename", $samplesPerSecond);
   system("$systemfilefolder/delexceptnewest.sh"); 
   ////////////////////////
   $bitscnt =($preparedLen/$samplesPerBit);
                                         $strmsgtype =choose($fskDllMessages, choose($msgType2Dll, $msgtype, 0), '');
   echo "<br>ok, file created, <b> $type $strmsgtype $digits </b> ; "
       ."AU version for WINAMP: <a href='$webfilefolder/$resultfilename.au'>$resultfilename.au</a> ; "
       ."wav version for browser: <a href='$webfilefolder/$resultfilename.wav'>$resultfilename.wav</a><br />";
   echo "<audio controls='controls'>"
       ."<source src='$webfilefolder/$resultfilename.wav' type='audio/wav' />"
       ."Your browser does not support the audio tag."
       ."</audio><br />";
   echo "additional info: <textarea rows=1>"
       ."file bytes: $preparedLen.\n"
       ."samples per second: $samplesPerSecond\n"
       ."samples per bit: $samplesPerBit\n"
       ."amplitude: $amplitude\n";
   switch ($type)
   {  case  'fsk': $cntL2 =($bitscnt-88)/10; $cntL3 =$cntL2-3;
                   echo "FSK info:\nencoded bits: $bitscnt.\n"
                       ."L2 bytes cnt: $cntL2.\nL3 bytes cnt: $cntL3.\n"
					   ."L3 data: " .bin2hex($globalL3) .".";
                   break;
      case 'dtmf': $octetscnt =$bitscnt/8;
                   echo "DTMF info:\ntotal octets: $octetscnt\ndigit length: $signal_length ms.\n"
                       ."pause ',' length: $pause_length ms.\npause beetween digits: $break_length ms.\n";
                   break;
   }
   echo "</textarea>";
?>
