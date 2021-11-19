<?php
   //$np ="\xEF\xBF\xBD"; 
   $np ='&nbsp;';
   //$fe ='¬'; // $np(�)=my sign for nonPrintable charactes, $fe(¬) =my sign for feeds (cr/lf/ff)
   $lf ="\xE2\x90\x8A";
   $ff ="\xE2\x90\x8C";
   $cr ="\xE2\x90\x8D"; //http://www.unicode.org/Public/MAPPINGS/ETSI/GSM0338.TXT
   $uGsmStandard =array(  '@', '£', '$', '¥', 'è', 'é', 'ù', 'ì', 'ò', 'Ç', $lf, 'Ø', 'ø', $cr, 'Å', 'å', 
                          'Δ', '_', 'Φ', 'Γ', 'Λ', 'Ω', 'Π', 'Ψ', 'Σ', 'Θ', 'Ξ', '©', 'Æ', 'æ', 'ß', 'É', 
                          ' ', '!', '"', '#', '¤', '%', '&', "'", '(', ')', '*', '+', ',', '-', '.', '/', 
                          '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':', ';', '<', '=', '>', '?', 
                          '¡', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 
                          'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'Ä', 'Ö', 'Ñ', 'Ü', '§', 
                          '¿', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 
                          'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'ä', 'ö', 'ñ', 'ü', 'à'   );
   $uGsmExtend1B =array(  10=>$ff, 20=>'^', 40=>'{', '}', 47=>'\\', 60=>'[', '~', ']', 64=>'|', 101=>'€'   ); 
   function gsm2Utf8($binval) 
   {  $len =strlen($binval); $result =''; global $np, $uGsmStandard, $uGsmExtend1B;
      for ($i=0; $i<$len; $i++)
      {  $conv =$np; $cur =ord($binval[$i]);
         if ($cur <128) {  if ($cur ==27) { if ($i<$len-1) if (isset( $uGsmExtend1B[ ord($binval[1+$i]) ] )) 
                                                               $conv =$uGsmExtend1B[ ord($binval[1+$i]) ];   $i++; } 
         else $conv =$uGsmStandard[$cur];  }
         $result .=$conv;
      }
      return $result;
   }
   $ibm437graphics =array( 1 =>'☺', '☻', '♥', '♦', '♣', '♠', '•', '◘', '○', '◙', '♂', '♀', '♪', '♫', '☼',
                   '►', '◄', '↕', '‼', '¶', '§', '▬', '↨', '↑', '↓', '→', '←', '∟', '↔', '▲', '▼', 127=>'⌂' );

   $mynpc =ord('~');//169; //'c' in circle - copyright - used for unknown characters by this decoding
   $grnpc =ord('~');//174; //'r' in circle - original - for non printable characters - greek alfabet insige gsm 03.38
   $fff   =ord('~');//172; //'_' - upper linefor linefeed, cariagereturn, formfeeed
                                                                                 
   $lookupGsm2Ascii =array //http://www.dreamfabric.com/sms/default_alphabet.html          //lf              //cr
   (     64,  163,     36,    165,    232,    233,    249,    236,          242,    199,   $fff,  216,  248, $fff,  197,  229, 
     $grnpc,   95, $grnpc, $grnpc, $grnpc, $grnpc, $grnpc, $grnpc,       $grnpc, $grnpc, $grnpc,   27,  198,  230,  223,  201,    
         32,   33,     34,     35,    164,     37,     38,   39,             40,     41,     42,   43,   44,   45,   46,   47,     
         48,   49,     50,     51,     52,     53,     54,   55,             56,     57,     58,   59,   60,   61,   62,   63,     
        161,   65,     66,     67,     68,     69,     70,   71,             72,     73,     74,   75,   76,   77,   78,   79,     
         80,   81,     82,     83,     84,     85,     86,   87,             88,     89,     90,  196,  214,  209,  220,  167,    
        191,   97,     98,     99,    100,    101,    102,  103,            104,    105,    106,  107,  108,  109,  110,  111,    
        112,  113,    114,    115,    116,    117,    118,  119,            120,    121,    122,  228,  246,  241,  252,  224  );                          
                               //ff
   $lookupGsmExt1B = array(10=>$fff, 20=>94, 40=>123, 41=>125, 47=>92, 60=>91, 61=>126, 62=>93, 64=>124, 101=>164);
   //last one: 101->164 is Euro sign, from iso 8859-15 ambiguity with upper 36->164 (currency sign from 8859-1)...
  
   function convertGsm2Ascii($binval)
   {  $len =strlen($binval); $result =''; global $mynpc, $lookupGsm2Ascii, $lookupGsmExt1B;
      for ($i=0; $i<$len; $i++)
      {  $cur =ord($binval[$i]); $conv =$mynpc;
         if ($cur <128) {  if ($cur ==27) { if ($i<$len-1) if (isset( $lookupGsmExt1B[ ord($binval[$i+1]) ] ))
                                                               $conv =$lookupGsmExt1B[ ord($binval[1+$i++]) ]; }
                           else $conv =$lookupGsm2Ascii[$cur];  }
         ///echo "checking: $cur... adding: $conv <br>";
         $result .=chr($conv);
      }
      return $result;
   }
   
   //http://www.columbia.edu/kermit/ucs2.html
   $ucs2conversions =array(   0x0106=>'C',  0x0107=>'c',    //cinija
                              0x010C=>'C',  0x010D=>'c',    //kuca 
                              0x0160=>'S',  0x0161=>'s',    //slag na torti
                              0x017D=>'Z',  0x017E=>'z',    //zaba
                              0x0110=>'Dj', 0x0111=>'dj'    //djurdjevak
                          ); 
   function ucs16to8($binval)
   {  $len =strlen($binval); global $ucs2conversions;
      $result =''; $zeros =0;
      for ($i=0; $i<$len-1; $i+=2)
      {  $hi =ord($binval[$i]); $lo =ord($binval[$i+1]); $ww =($hi*256) +$lo;
         if (isset($ucs2conversions[$ww]))   $result .=$ucs2conversions[$ww];
         else                              { $result .=chr($lo);  if ($hi==0 &$lo!=0) $zeros++; }
      }
      return array($result, $zeros);
   }
   function hexNoRightZeroes($binstr) //input is string binary, output is hex val without ending zeros
   {  $result =strtoupper(preg_replace('/0+$/', '',  bin2hex($binstr)));
      if (strlen($result) &1) $result .='0';             return $result;
   }
   function countnice($val)
   {  $regchs ='/[a-z0-9A-Z\:\,\ \.\)\(\?\!\*\-]/'; $dummy =array(); 
      return preg_match_all($regchs, $val, $dummy);
   }
   function checkIfLong($val)
   {  $result =false; $len =strlen($val); if ($len<7) return false; 
      switch (substr($val,0,3))      //shift        //header                   //value
      {  case "\x05\x00\x0C": $result =array(  0, ud827(substr($val, 0, 7)), substr($val, 7, $len- 7) ); break; 
         case "\x05\x00\x03": $result =array( -1,       substr($val, 0, 6) , substr($val, 6, $len- 6) ); break; 
         case "\x09\x00\x03": $result =array(  3,       substr($val, 0,10) , substr($val,10, $len-10) ); break; 
         case "\x0B\x00\x03": $result =array(  5,       substr($val, 0,12) , substr($val,12, $len-12) ); break; 
      }  return $result;
   }
   function trimrz($val) { return preg_replace('/\x00+$/', '', $val); } //clear ending zeros
   function chooseSmsCoding($value, $decoding=1, $unicode=0, $addStart=1) //input is byte array
   {  $value =trimrz($value); $len =strlen($value); if (!$len) return '';
                                                    if (!$decoding) return strtoupper(bin2hex($value));
      $start =''; $ud =''; $shift =0; $header =''; //ud == returning text
      $rlong =checkIfLong($value); if ($rlong !==false) //Long Message !!!!!
                                   {  list($shift, $header, $value) =$rlong; 
                                      $start ='[' .ord($header[5]) .'/' .ord($header[4]) .']';  } 

           $ud7             =trimrz(ud728($value, $shift));
           $ud8             =$value; 
      list($ud16, $zeros16) =ucs16to8($value);
      
      $cnt7  =countnice($ud7); $cnt8  =countnice($ud8); $cnt16 =countnice($ud16) +$zeros16; 
      $cntmax =max($cnt7, $cnt8, $cnt16);
      ///echo "... max=$cntmax (7=$cnt7, 8=$cnt8, 16=$cnt16) <br>";
      switch ($cntmax)
      {  case $cnt16: $start .='[u]'; $ud = $ud16; break; //priority:16->8->7
         case $cnt8:  $start .='[a]'; $ud = convertGsm2Ascii($ud8);  break;
         case $cnt7:  $start .='[7]'; $ud = convertGsm2Ascii($ud7);  break;
      }
      global $mynpc, $fff; //clearing UD:
      $ud =preg_replace('/[\x0A\x0D]/',  chr($fff),   $ud); //cr/lf
      $ud =preg_replace('/[\x00-\x1F\x80-\xFF]/', chr($mynpc), $ud); //nonprintable [\x00-\x1F/
      $result =($addStart ?$start :'') .$ud; //v1.49 addStart bool...
                           if ($unicode) $result =iconv("ISO-8859-15", "UTF-8", $result);
      return $result;
   }
   function ud728($val, $shift=0) //input binary, output
   {  if ($shift) $val =binShift($val, $shift);
      $conv =''; $carry =0; $maskpos =0;  $len =strlen($val); 
      for ($i=0; $i<$len; $i++)
      {  $mask =(1<<(7-$maskpos))-1;  
         $conv .=chr( ( (ord($val[$i]) &$mask) <<$maskpos ) +$carry);
         $carry =(ord($val[$i]) &~$mask) >>(7-$maskpos);
         if (++$maskpos >6) { $maskpos =0; $conv .=chr($carry); $carry =0; }
      }
      return $conv;
   }
   function ud827($instr, $shift=0) //shift 0..6
   {  $slen =strlen($instr);   $result ="\x0";
      for ($inpos=0; $inpos<$slen; $inpos++)
      {  $currin =ord($instr[$inpos]) &0x7f;     $outpos =intval($inpos -1 -floor($inpos /8));
         $bf =$inpos %8;    $bs =7 -$bf;         $mmf=(1 <<$bf) -1;           $mms =255 -$mmf;        
         if ($bf>0) $result[$outpos]    =chr( ord($result[$outpos]) | ((($currin & $mmf) <<(8-$bf)) &0xff));
         if ($bs>0) $result[$outpos+1]  =chr(                         ((($currin & $mms) >>(  $bf)) &0xff));
      }
      if ($shift) $result =binShift($result, $shift);                      return $result;
   }
   if (!function_exists('hex2bin')) {
   function hex2bin($hex)
   {  $len =strlen($hex); $bin ='';          for ($i=0; $i<$len-1; $i+=2) 
      { $ss =sscanf($hex[$i].$hex[$i+1], "%02X"); $bin .=chr($ss[0]); }    return $bin;
   }
   }
   function binShift($val, $shift)
   {                                                 if (!$shift) return $val; 
      if ($shift <0) { $upShift=false; $shift =-$shift; } else $upShift =true; 
      $len =strlen($val); $rval =''; $nshift =8-$shift;  $val ="\x0" .$val ."\x0";
      if ($upShift) for ($i=1; $i<$len+1; $i++) $rval .=chr( (ord($val[$i-1]) >>$nshift) | ((ord($val[$i  ]) <<$shift) &0xff) );
               else for ($i=1; $i<$len+1; $i++) $rval .=chr( (ord($val[$i  ]) >>$shift ) | ((ord($val[$i+1]) <<$nshift)&0xff) );
      return $rval;
   }
   function stringToPdu7($instr, $shift=0) //shift 0..6
   {  $prefixbits =1;
      $slen =strlen($instr);     $result ="";  $resultarray =array();
      for ($inpos=0; $inpos<$slen; $inpos++)
      {  $currin =ord($instr[$inpos]) &0x7f;  
         $outpos =intval($inpos -1 -floor($inpos /8));
         $bf =$inpos %8;        $bs =7 -$bf;  $mmf=(1 <<$bf) -1;           $mms =255 -$mmf;
         
         if ($bf>0) $resultarray[$outpos]   |=(($currin & $mmf) <<(8-$bf)) &0xff;
         if ($bs>0) $resultarray[$outpos+1]  =(($currin & $mms) >>(  $bf)) &0xff;
      }
      
      if ($shift) {  $r2 =array(0=>0); $mask =1<<$shift-1; $cnt =0;
                     foreach ($resultarray as $val) { $r2[  $cnt] |=$val >>$shift; $r2[++$cnt]  =$val &$mask;  }
                     $resultarray =$r2;
                  }
      foreach ($resultarray as $val) $result .=sprintf("%02X", $val);      return $result;
   }
   function pdu7tostr($val, $shift=0)
   {  if ($shift) 
      {  $len =strlen($val); $val2 =array(); $val3 =array(0=>0);
         for ($i=0; $i<$len; $i++) $val2[] =ord($val[$i]);
         $mask =(1<<(8-$shift)-1); $cnt =0;
         for ($i=0; $i<$len; $i++) 
         {  $val3[$i  ] |=($val2[$i] <<$shift) &0xff;
            $val3[$i+1]  =(($val2[$i] &$mask) <<(8-$shift)) &0xff;
         }
         $val =''; foreach ($val3 as $v) $val .=$v;
      }
      $conv =''; $carry =0; $maskpos =0;  $len =strlen($val); 
      for ($i=0; $i<$len; $i++)
      {  $mask =(1<<(7-$maskpos))-1;  
         $conv .=chr( ( (ord($val[$i]) &$mask) <<$maskpos ) +$carry);
         $carry =(ord($val[$i]) &~$mask) >>(7-$maskpos);
         if (++$maskpos >6) { $maskpos =0; $conv .=chr($carry); $carry =0; }
      }
      return $conv;
   }
   function pdu8tostr($value) { return $value; }
?>
