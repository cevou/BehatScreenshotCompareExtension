<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>BehatScreenshotCompareExtension Testpage</title>
    <style>
        .box {
            background-color: #ff9900;
            border: <?php echo isset($_GET['borderwidth']) ? $_GET['borderwidth'] : 1;?>px solid #000000;
            width: 200px;
            height: 200px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="box"></div>
</body>
</html>