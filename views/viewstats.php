<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>LimeSurvey public statistics</title>
</head>

<body class="<?=$theme?>">
    <div id="appmain">
        <app 
            get-data-url='<?=$getDataUrl?>' 
            :word-cloud-settings='<?=json_encode($wordCloudSettings)?>'
            :surveydata='<?=json_encode($surveyData)?>'
        />
    </div>
</body>

</html>
