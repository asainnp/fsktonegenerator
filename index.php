<!DOCTYPE HTML>
<html>
<head>
   <meta charset='UTF-8'>
   <title>FSK Tone Generator</title>
   <link rel="stylesheet" type="text/css" href="tonegenerator.css">
   <script src="../../forall/jquery.js" type="text/javascript"></script>
</head>
<body>
   <div class='divfields' id='divdtmf'>
      <fieldset>
         <legend>Generating DTMF</legend>
         <ul>
            <li>DTMF Digits: <input type='text' id='dtmfdigits' value=',D,062898019' check='^[T0-9a-dA-D\,]*$' /> <!-- /^[T0-9a-dA-D\,]*$/ -->
            <li>&nbsp;
            <li><button id='generatedtmf'>Generate</button>
         </ul>
      </fieldset>
   </div>
   <div class='divfields' id='divfsk'>
      <fieldset>
         <legend>Generating FSK of FSMS message</legend>
         <ul>
            <li>MSG TYPE: <select id='msgtype'>
                                  <option value='1' selected>SMS SM to TE</option>
                                  <option value='2'>SMS TE to SM</option>
                                  <option value='3'>L2 ERROR</option>
                                  <option value='4'>L2 ESTABLISH</option>
                                  <option value='5'>L2 RELEASE</option>
                                  <option value='6'>L2 ACKNOWLEDGEMENT</option>
                                  <option value='7'>L2 NOT ACKNOWLEDGE</option>   </select>
            <li>SMS Center Number:<input type='text' id='smscphone'     value='27381000015' check='^[0-9]{1,13}$' showw='1' />
            <li>Sender Number:    <input type='text' id='senderphone'   value='27388890001' check='^[0-9]{1,13}$' showw='1' />
            <li>Receiver Number:  <input type='text' id='receiverphone' value='27388890001' check='^[0-9]{1,13}$' showw='2' />
            
            <li>SMS Text: <input type='text' id='smstext' value='hellohello' showw='1,2' />
            <li>ErrorCode: <select id='errcode' showw='3'>
                                                 <option value='1' selected> 1-Wrong checksum </option>
                                                 <option value='2'> 2-Wrong message length </option>
                                                 <option value='3'> 3-Unknown message type </option>
                                                 <option value='4'> 4-Extension mechanism not supported </option>
                                                 <option value='255'> 255 - Unspecified error cause </option>     </select>
            <li>&nbsp;
            <li><button id='generatefsk'>Generate</button>
         </ul>
      </fieldset>
   </div>
   <div class='clear'></div>
   <div id='results'></div>
   <script>
      $('#msgtype').change(function() //hide inputs that are not related to current message
      {  var currmsgtype =$(this).val();
         $('#divfsk input, #divfsk select').each(function()
         {  var showw =$(this).attr('showw');
            if (showw) if (-1 ==showw.search(currmsgtype)) $(this).parent().hide(); 
                                                      else $(this).parent().show();
         });
      });
      $('#msgtype').change(); //call it once on page start
      
      //GENERATE clicks
      $('#generatedtmf').click(function() 
      {  var params ='type=dtmf&digits=' +$('#dtmfdigits').val();
         $.post('generate.php', params, resultFunction);
      });
      $('#generatefsk').click(function() 
      {  var params ='type=fsk';
         $('#divfsk input, #divfsk select').each(function() { params +='&'+$(this).attr('id') +'=' +$(this).val(); });
         $.post('generate.php', params, resultFunction);
      });
      function resultFunction(data)
      {  $('#results').prepend("<div class='resultline'>"+data+"<div>"); 
         $('#results div:first').hide(); $('#results div:first').fadeIn('fast');
      }
      
      //check REGEX
      $('input').bind('input paste', function() 
      {  var check =$(this).attr('check'); if (check==null) return;
         var regex = new RegExp(check);
         var result = regex.test($(this).val());
         $(this).css('background', (result)?'white':'red');
      });
      //play on 1,2,3,4,5,6,7,8,9 keys
      $('input').keypress(function(e) { e.stopPropagation(); } );
      $(document).keypress(function(e) 
      {  if (e.keyCode <0x30 ||e.keyCode >0x39) return; key =e.keyCode -0x30;
         //if (focus ... input text) return !!!;
         $('#results div:nth-child(' + key +') audio')[0].play();
      });      
   </script>
</body>
</html>
