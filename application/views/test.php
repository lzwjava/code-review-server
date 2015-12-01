<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
</div>

</body>
</html>