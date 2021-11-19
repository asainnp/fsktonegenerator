<?php 
   function encode_int($ival)
   {  $s =''; for ($i=0; $i<4; $i++) { $i8 =$i*8; $s =chr(($ival&(0xff<<$i8))>>$i8) .$s; }
      return $s;   
   }
   function createAuWav($rawaudio, $fname, $samplesPerSecond)
   {  $output = ".snd"                         //"magic number"
                ."\0\0\0\x18"                  //data offset
                .encode_int(strlen($rawaudio)) //data size (0xffffffff = unknown)
                ."\0\0\0\2"                    //encoding (2 = 8-bit linear PCM, 3 = 16-bit linear PCM)
                .encode_int($samplesPerSecond) //sample rate
                ."\0\0\0\1"                    //channels
                .$rawaudio;  //description of snd/au format available at http://www.wotsit.org/search.asp?s=music
      $fp =fopen("$fname.au", 'w'); fwrite($fp, $output); fclose($fp);

      echo system("ffmpeg -y -i $fname.au $fname.wav"); //linux
   }
?>
