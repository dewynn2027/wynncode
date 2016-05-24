<?php
        $xmlresponse  = "<RSP>";
        $xmlresponse .= "<mid>".$_GET['mid']."</mid>";
        $xmlresponse .= "<oid>".$_GET['oid']."</oid>";
        $xmlresponse .= "<cur>".$_GET['cur']."</cur>";
        $xmlresponse .= "<amt>".$_GET['amt']."</amt>";
        $xmlresponse .= "<status>".$_GET['status']."</status>";
        $xmlresponse .= "<cartid>".$_GET['cartid']."</cartid>";
        $xmlresponse .= "<signature>".$_GET['signature']."</signature>";
        $xmlresponse .= "</RSP>";
?>
