<?php 
$TermUrl = (!empty($_GET['TermUrl'])) ? $_GET['TermUrl'] : "";
$PaReq = (!empty($_GET['PaReq'])) ? $_GET['PaReq'] : "";
$MD = (!empty($_GET['MD'])) ? $_GET['MD'] : "";
?>
<iframe id="myiframe" src="https://apistage.paymentsystemsintegration.com/test/send3d?PaReq=<?php echo $PaReq;?>&MD=<?php echo $MD;?>&TermUrl=<?php echo $TermUrl;?>" height="400px" width="400px">
</iframe>


<script type="text/javascript">

// #Create IE + others compatible event handler
var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
var eventer = window[eventMethod];
var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

// #Listen to message from child window
eventer(messageEvent,function(e)
{
	// #console.log('parent received message!:  ',e.data);
	try {JSON.parse(e.data);}catch(err){alert(e.data);}
	
},false);


 </script> 