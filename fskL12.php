<?php 
   //ETSI EN 300 659-1 physical layer   ...annex D
   // and http://www.etsi.org/deliver/etsi_en/300700_300799/30077802/01.02.01_60/en_30077802v010201p.pdf
   // and main //layer1: http://www.etsi.org/deliver/etsi_es/201900_201999/201912/01.01.01_50/es_201912v010101m.pdf
   
   //LAYER 1   
   function addSingleBitL1($bit, &$angle)
   {  global $samplesPerBit, $angleStepPerSample, $amplitude; 
      $samplebytes ='';
      for ($s =0; $s<$samplesPerBit; $s++) //40 samples per bit
      {  $samplebytes .=chr(floor($amplitude *sin($angle))); 
         $angle +=$angleStepPerSample[$bit];            if ($angle >2*M_PI) $angle -=2*M_PI;
      }
      return $samplebytes;
   }
   
   function encodeFskL1($rawdataL2)
   {  $len =strlen($rawdataL2); 
      $rawfsk =''; $angle =0.0;
      
      //channelseizure: //for ($i=0; $i<300; $i++) $rawfsk .=addSingleBitL1(($i&1), $angle); //01010101...01 (300bits)
      //etsi 778 says: no channel seizure in offhook data transmisions by etsi ... and mark is 80+/-25 not 180+/-25
      
      for ($i=0; $i<80; $i++) $rawfsk .=addSingleBitL1(1, $angle); //mark signal should'nt be enveloped :)
                                                                   //80+/-25 condition shows it (not octets)
      for ($i =0; $i<$len; $i++) //n bytes
      {  $byte =ord($rawdataL2[$i]);
         $rawfsk .=addSingleBitL1(0, $angle); //stopbit - space - 0 zero   .. 0xxx1 ENVELOPE
         
         for ($b =0; $b<8; $b++) //8 bits
         {  $bit =($byte>>$b) &1;                         
            $rawfsk .=addSingleBitL1($bit, $angle);
         }
         
         $rawfsk .=addSingleBitL1(1, $angle); //stopbit - mark  - 1        .. 0xxx1 ENVELOPE
      }
      for ($i=0; $i<8; $i++) $rawfsk .=addSingleBitL1(1, $angle);   //1 to 10 stop bits after checksum...
      return $rawfsk;
   }

   //LAYER 2  
   function crc8($bin) { $len =strlen($bin); $sum =0; for ($i=0; $i<$len; $i++) $sum +=ord($bin[$i]);
                         return 256 -($sum%256);                                                     }

   function encodeFskL2($dllMsgType, $rawL3='')
   {  $msgType =$dllMsgType |0x80;       //no segmentation in this file version
	  $len=strlen($rawL3);
      $resultNoCrc =chr($msgType)        //type i.e=dll_sms_data 0x11 + extension bit = 1 !!! 
                   .chr($len)  //Length
                   .$rawL3;              //L3 data
	  $crc =crc8($resultNoCrc);
	  printf("encoding 0x%02X (len=%d, crc=%d)", $msgType, $len, $crc);
     return $resultNoCrc .chr($crc);
   }
?>