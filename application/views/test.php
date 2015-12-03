<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="http://upcdn.b0.upaiyun.com/libs/jquery/jquery-2.0.3.min.js"></script>
    <script src="../../resources/js/pingpp-pc.js"></script>

    <script>
        $(function () {
            $("#pay-btn").click(function () {
                console.log('button-clicked');
                $.ajax({
                    url: "http://localhost:3005/pingpp/charge", success: function (charge) {
                        var object = JSON.parse(charge);
                        console.dir(object);
                        $("#qrcode").attr('src', object.credential.alipay_qr);
                    }
                });
            });
        })();
    </script>
</head>

<body>

<div id="container">
    <h1>Test</h1>

    <div id="body">
        <form method="post" action="http://up.qiniu.com" enctype="multipart/form-data">
            <input name="token" placeholder="token">
            <input name="file" type="file"/>
            <input type="submit" value="上传"/>
        </form>
    </div>

    <div>

        <button id="pay-btn">pay</button>

        <img id="qrcode" src="">
    </div>
</div>

</body>
</html>