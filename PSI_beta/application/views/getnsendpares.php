<?php
// header("access-control-allow-origin: *");
// header("Access-Control-Allow-Methods: GET, POST, PUT");
echo $response;
?>
<script type="text/javascript">	parent.postMessage('<?php echo $response;?>', '*');</script>