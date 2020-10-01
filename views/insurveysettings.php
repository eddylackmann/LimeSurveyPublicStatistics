<!---------Main Frame start-------->
<div class='side-body <?= getSideBodyClass(false); ?>'>
    <div class="container-center">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="pagetitle"><?php echo  PSTranslator::translate("Public Statistics - Settings") ?></h3>
            </div>
        </div>
        <?php if ($isActive) : ?>
            <?php if ($initialised) : ?>
                <!-------------Settings form start-------->
                
                <?php echo TbHtml::form(array("plugins/direct/plugin/PublicStatistics/method/saveinsurveysettings"), 'post', array('name' => 'psinsurveysettings', 'id' => 'psinsurveysettings')); ?>
                
                    <!--Action buttons start-->
                    <div class="row">
                        <input type="hidden" id="currentSurveyId" name="sid" value="<?= $sid ?>" />
                        <div class="col-sm-12 text-right ls-space margin bottom-10">
                            <button type="submit" class="btn btn-success pull-right" id="ps--save-button">
                                <i class="fa fa-save"></i>
                                <?php echo  PSTranslator::translate("Save settings") ?>
                            </button>
                        </div>
                    </div>
                     <!--Action buttons stop-->

                    <hr>

                    <!-----Links settings start------>
                    <div class="row">
                        
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4><?php echo  PSTranslator::translate("Shareable links") ?></h4>
                                </div>
                                <div class="panel-body">
                                    <div class="list-group">
                                        <div class="list-group-item row">
                                            <div class="col-sm-4 col-xs-12 background-muted">
                                                <?php echo  PSTranslator::translate("Basic link (for logged in users)") ?>
                                            </div>
                                            <div class="col-sm-6 col-xs-12" style="display:block;">
                                                <?= Yii::app()->createAbsoluteUrl(
                                                    'plugins/direct',
                                                    [
                                                        'plugin' => 'PublicStatistics',
                                                        'method' => 'viewdirect',
                                                        'surveyid' => $sid
                                                    ]
                                                ) ?>
                                            </div>
                                            <div class="col-sm-2 col-xs-12" style="display:block;">
                                                <a href="<?= Yii::app()->createAbsoluteUrl(
                                                                'plugins/direct',
                                                                [
                                                                    'plugin' => 'PublicStatistics',
                                                                    'method' => 'viewdirect',
                                                                    'surveyid' => $sid
                                                                ]
                                                            ) ?>" class="btn btn-default" target="_blank">
                                                    <?php echo  PSTranslator::translate("Open Link") . " " ?><i class="fa fa-external-link"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="list-group-item row">
                                            <div class="col-sm-4 col-xs-12 background-muted">
                                                <?php echo  PSTranslator::translate("Basic link (for non-logged in users)") ?>
                                            </div>
                                            <div class="col-sm-6 col-xs-12" style="display:block;">
                                                <?= Yii::app()->createAbsoluteUrl(
                                                    'plugins/unsecure',
                                                    [
                                                        'plugin' => 'PublicStatistics',
                                                        'method' => 'viewunsecure',
                                                        'surveyid' => $sid,
                                                        'token' => $PS['token']
                                                    ]
                                                ) ?>
                                            </div>
                                            <div class="col-sm-2 col-xs-12" style="display:block;">
                                                <a href="<?= Yii::app()->createAbsoluteUrl(
                                                                'plugins/direct',
                                                                [
                                                                    'plugin' => 'PublicStatistics',
                                                                    'method' => 'viewunsecure',
                                                                    'surveyid' => $sid,
                                                                    'token' => $PS['token']
                                                                ]
                                                            ) ?>" class="btn btn-default" target="_blank">
                                                    <?php echo  PSTranslator::translate("Open Link") ?> <i class="fa fa-external-link"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                       
                    </div>
                    <!-----Links settings stop------>
                    
                    <!-----Basic and additional settings start------>
                    <div class="row">

                        <!-----Basic------>                                          
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4><?php echo  PSTranslator::translate("Basic settings") ?></h4>
                                </div>
                                <div class="panel-body form">
                                    <div class="list-group">
                                        <div class="list-group-item row">
                                            <div class="col-sm-6 col-xs-12">
                                                <label><?php echo  PSTranslator::translate("Activate public statistic for this survey?") ?>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <?php $this->widget(
                                                    'yiiwheels.widgets.switch.WhSwitch',
                                                    array(
                                                        'name' => 'activated',
                                                        'id' => 'ps--activate',
                                                        'value' => $PS['activated'],
                                                        'onLabel' => gT('On'),
                                                        'offLabel' => gT('Off')
                                                    )
                                                ); ?>
                                            </div>
                                        </div>
                                        <div class="list-group-item row">
                                            <div class="col-sm-6 col-xs-12">
                                                <label for="ps--token">
                                                    <?php echo  PSTranslator::translate("Set Token? (Leave empty for none)") ?>
                                                </label>
                                            </div>
                                            <div class="col-sm-6 col-xs-12 input-group">
                                                <input type="text" id="ps--token" name="token" value="<?= $PS['token'] ?>" class="form-control" />
                                                <span class="input-group-addon" id="">
                                                    <button id="PS--action--generate-token" class="btn btn-xs btn-primary"> <?php echo  PSTranslator::translate("Generate") ?></button>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="list-group-item row">
                                            <div class="col-sm-6 col-xs-12">
                                                <label for="ps--expire">
                                                    <?php echo  PSTranslator::translate("Set expiry date? (Leave empty for none)") ?>
                                                </label>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <input type="date" id="ps--expire" name="expire" value="<?= $PS['expire'] ?>" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="list-group-item row">
                                            <div class="col-sm-6 col-xs-12">
                                                <label for="ps--begin">
                                                    <?php echo  PSTranslator::translate("Set begin date? (Leave empty for none)") ?>
                                                </label>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <input type="date" id="ps--begin" name="begin" value="<?= $PS['begin'] ?>" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="list-group-item row">
                                            <div class="col-sm-6 col-xs-12">
                                                <label for="ps--begin">
                                                    <?php echo  PSTranslator::translate("Use logins?") ?>
                                                </label>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <?php $this->widget(
                                                    'yiiwheels.widgets.switch.WhSwitch',
                                                    array(
                                                        'name' => 'logins',
                                                        'id' => 'ps--logins',
                                                        'value' => $uselogins,
                                                        'onLabel' => gT('On'),
                                                        'offLabel' => gT('Off')
                                                    )
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-----Additional------>
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4> <?php echo  PSTranslator::translate("Additional settings") ?></h4>
                                </div>
                                <div class="panel-body form">
                                    <div class="list-group">
                                        <div class="list-group-item row">
                                            <div class="col-sm-6 col-xs-12">
                                                <label><?php echo  PSTranslator::translate("Default visualisation") ?></label>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <?php $this->widget(
                                                    'yiiwheels.widgets.buttongroup.WhButtonGroup',
                                                    array(
                                                        'name' => 'data[initialChartType]',
                                                        'value' => $data['initialChartType'],
                                                        'selectOptions' => array(
                                                            'bar' => 'Bar',
                                                            'pie' => 'Pie',
                                                            'doughnut' => 'Doughnut',
                                                            'line' => 'Line',
                                                        )
                                                    )
                                                ); ?>
                                            </div>
                                        </div>
                                        <div class="list-group-item row">
                                            <div class="col-sm-6 col-xs-12">
                                                <label for="PS--companyImage"><?php echo  PSTranslator::translate("Path to customer logo") ?></label>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <input type="text" class="form-control" id="PS--companyImage" name="data[companyImage]" rows="5" value="<?= $data['companyImage'] ?>" />
                                            </div>
                                        </div>
                                        <div class="list-group-item row">
                                            <div class="col-sm-6 col-xs-12">
                                                <label for="PS--contactinformation--field"><?php echo  PSTranslator::translate("Contact to show on demand") ?></label>
                                                <textarea class="form-control" id="PS--contactinformation--field" name="data[contactinformation]"><?= $data['contactinformation'] ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-----Standard and additional settings stop------>

                </form>
                <!-------------Settings form stop-------->

                <div class="row hidden" id="ps--selector--logintable">
                    <div class="col-xs-12">
                        <div class="panel panel-default ">

                            <div class="panel-heading">
                                <?php echo  PSTranslator::translate("Available logins") ?>
                                <button class="btn btn-default btn-sm pull-right" id="ps--action--newRow">
                                    <i class="fa fa-plus-circle"></i>
                                </button>
                            </div>
                            <div class="panel-body">
                                <table class="table" id="possiblelogintable">
                                    <thead>
                                        <tr>
                                            <th> <?php echo  PSTranslator::translate("Email address") ?></th>
                                            <th><?php echo  PSTranslator::translate("Valid (from/to)") ?></th>
                                            <th> <?php echo  PSTranslator::translate("Action") ?> </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (safecount($aLogins) == 0):?>
                                            <tr class="identifier--noinsertrow">
                                                <td colspan="3"><?php echo  PSTranslator::translate("None added yet") ?> </td>
                                            </tr>
                                            <?php else : ?>
                                            <?php foreach ($aLogins as $oLogin):?>
                                          
                                                <tr class="ps--selector--row" data-loginid="<?= $oLogin->id ?>">
                                                    <td>
                                                        <?= $oLogin->email ?>
                                                    </td>
                                                    <td>
                                                        <?=  date("d.m.Y", strtotime( $oLogin->begin)); ?> / <?=  date("d.m.Y", strtotime( $oLogin->expire )); ?>
                                                    </td>
                                                   
                                                    <td>
                                                        <button data-loginid="<?= $oLogin->id ?>" class="btn btn-sm btn-default action--resetPassword" data-toggle="tooltip" title="Reset and resend password">
                                                            <i class="fa fa-refresh"></i>
                                                        </button>
                                                        <button data-loginid="<?= $oLogin->id ?>" class="btn btn-sm btn-danger action--deleteLogin" data-toggle="tooltip" title="Delete this login">
                                                            <i class="fa fa-trash-o"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>  
                                        <?php endif; ?>     
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="row">
                    <div class="col-xs-12 jumbotron jumbotron-default well">
                        <?php echo TbHtml::form(array("plugins/direct/plugin/PublicStatistics/method/saveinsurveysettings"), 'post', array('name' => 'psinsurveysettings', 'id' => 'psinsurveysettings')); ?>
                        <input type="hidden" id="currentSurveyId" name="sid" value="<?= $sid ?>" />
                        <h2><?php echo  PSTranslator::translate("Public Statistics not initialised.") ?></h2>
                        <p class="lead"><?php echo  PSTranslator::translate("") ?></p>
                        <div class="col-sm-12 text-center ls-space margin bottom-10">
                            <button type="submit" class="btn btn-success" id="ps--save-button">
                                <i class="fa fa-rocket"></i>
                                <?php echo  PSTranslator::translate("Initialise") ?>
                            </button>
                        </div>
                        </form>
                    </div>

                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="row">
                <div class="col-md-offset-2 col-md-8 col-sm-12 well jumbotron">
                    <h4> <?php echo  PSTranslator::translate("Public statistic is only available for an activated survey!") ?> </h4>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!---------Main Frame stop-------->


































<div class="modal fade" id="newLoginFormModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo  PSTranslator::translate("Add new login") ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="newRowEmail"><?php echo  PSTranslator::translate("Email address") ?></label>
                    <input type="email" class="form-control" id="newRowEmail" placeholder="name@example.com">
                </div>
                <div class="form-group">
                    <label for="newRowvalidfrom"><?php echo  PSTranslator::translate("Valid from (leave empty for unlimited)") ?></label>
                    <input type="date" class="form-control" id="newRowvalidfrom">
                </div>
                <div class="form-group">
                    <label for="newRowvalidtil"><?php echo  PSTranslator::translate("Valid until (leave empty for unlimited)") ?></label>
                    <input type="date" class="form-control" id="newRowvalidtil">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo  PSTranslator::translate("Close") ?></button>
                <button type="button" id="ps--action--saveNewRow" class="btn btn-primary"><?php echo  PSTranslator::translate("Save") ?></button>
            </div>
        </div>
    </div>
</div>



<x-template id="loginrow-template" class="hidden">
    <tr class="ps--selector--row" data-loginid="[[loginid]]">
        <td>
            [[email]]
        </td>
        <td>
            [[lastlogin]]
        </td>
        <td>
            [[validfrom]]/[[validtil]]
        </td>
        <td>
            <button data-loginid="[[loginid]]" class="btn btn-sm btn-default action--resetPassword" data-toggle="tooltip" title="Reset and resend password">
                <i class="fa fa-reload"></i>
            </button>
            <button data-loginid="[[loginid]]" class="btn btn-sm btn-danger action--deleteLogin" data-toggle="tooltip" title="Delete this login">
                <i class="fa fa-trash-o"></i>
            </button>
        </td>
    </tr>
</x-template>



<?php
Yii::app()->getClientScript()->registerScript('PSSettings', 'PublicStatisticsSettings()', LSYii_ClientScript::POS_POSTSCRIPT);
?>