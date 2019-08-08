<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Offene Statistik</title>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <br/>
                <br/>
            </div>
        </div>
        <?php if($oSurvey->hasLogins) { ?> 
            <div class="row">
                <div class="col-xs-12 jumbotron jumbotron-default well">
                    <h2>Bitte loggen sie sich mit den Ihnen zugesandten Daten ein: </h2>
                    <form action="<?=$formUrl?>" method="post" name="loginforPublicStatistics" class="form">
                        <div class="row">
                            <div class="form-group col-md-6 col-xs-12">
                                <label for="email">E-mail-Addresse</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email">
                            </div>
                            <div class="form-group col-md-6 col-xs-12">
                                <label for="password">Passwort</label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                            <div class="form-group col-xs-12">
                                <input type="submit" class="btn btn-primary" id="submit" name="submit" value="Submit">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php } else { ?>
            <div class="row">
                <div class="col-xs-12 jumbotron jumbotron-default well">
                    <h2>Bitte geben Sie den Zugangsschlüssel ein: </h2>
                    <form action="<?=$formUrl?>" method="post" name="loginforPublicStatistics" class="form">
                        <div class="row">
                            <div class="form-group col-xs-12">
                                <label for="email">Token</label>
                                <input type="text" class="form-control" id="token" name="token" placeholder="Token">
                            </div>
                            <div class="form-group col-xs-12">
                                <input type="submit" class="btn btn-primary" id="submit" name="submit" value="Submit">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php } ?>
    </div>
</body>

</html>
