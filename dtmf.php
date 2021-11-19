<?php 
   //charLen, breakLen, pauseLen in Seconds (ex: 0.08 ... for 80ms)
   
   function createDTMF($digits, $samplesPerSecond, $charLen, $breakLen, $pauseLen, $amplitude)
   {  if (!$digits &&$digits!='0') { echo 'err: no digits sent <br />'; return ''; }
      $sampleangle =2*M_PI /$samplesPerSecond;
      $highfreqsX = array(1209, 1336, 1477, 1633);        $lowfreqsY  = array( 697,  770,  852,  941);  
      $signals    = array( '1',  '2',  '3',  'A', //697
                           '4',  '5',  '6',  'B', //770
                           '7',  '8',  '9',  'C', //852
                           '*',  '0',  '#',  'D', //941
                           'T'); //2130/2750
      $highlow  =array();  $highlow['T'] =array(2130, 2750);
      for ($y=0; $y<4; $y++) for ($x=0; $x<4; $x++) $highlow[ $signals[$y*4+$x] ] =array($highfreqsX[$x], $lowfreqsY[$y]);
      $highlowA =array(); foreach ($highlow as $hlk=>$hlv) for ($i=0; $i<2; $i++) $highlowA[$hlk][$i] =$hlv[$i]*$sampleangle;
      //^highlowA = all 17 values of highlow multiplied by $sampleangle
      
      $output = '';
      for ($i =0; $i <strlen($digits); $i++) 
      {   $signal = $digits[$i];       
          if  ($signal == ',') $output .= str_repeat("\0", $pauseLen * $samplesPerSecond);
          else   
          {    $hh =$highlowA[$signal][0]; $hl =$highlowA[$signal][1];
               for ($j = 0; $j < $charLen *$samplesPerSecond; $j++) 
                    $output .= chr( floor( $amplitude * ( sin($j *$hh) +sin($j *$hl) ) ) );
          }
          $output .= str_repeat("\0", $breakLen *$samplesPerSecond);
      }
      if (strlen($output) == 0) $output = "\0";   //make sure that all output contains at least 1 byte excl. the header
      return $output;
   }
?>
