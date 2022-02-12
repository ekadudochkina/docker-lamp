<?php
/* @var $this AdminController */
/* @var $models AccessLog[] */
/* @var $statistics AccessLog */

?>

<h2>Statistics:</h2>
<hr>
 <a href='<?=$this->createAbsoluteUrl("admin/detailedStatistic")?>' class="btn btn-success btn-lg btn-icon icon-left"><i class="entypo-info"></i>More information</a>  
    <br />
    <br />
    <div class="row">
        <div class="panel panel-primary">
            <table class="table table-bordered table-responsive">
                <thead>
                    <tr>
                        <th>Url</th>
                        <th>Ip</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                  <?php $statistics = $filter->getModels(); ?>
                    <?php foreach ($statistics as $statistic){ ?>
                        <tr>
                            <td><?=$statistic->url ?></td>
                            <td><?=$statistic->ip ?></td>                           
                            <td><?=$statistic->visitDate ?></td>                           
                           
                        </tr>
                      <?php } ?> 
                </tbody>
            </table>
            
               
        </div>
    </div>	
 <?=$this->renderPartial("views.admin.pagination.simple",array("filter"=>$filter))?>

