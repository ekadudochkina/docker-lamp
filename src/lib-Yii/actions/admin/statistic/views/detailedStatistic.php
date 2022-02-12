<?php
/* @var $this AdminController */
?>

<h2>Site visits:</h2> 
<hr>
    <br />
    <br />
    <div class="row">
        <div class="panel panel-primary">
            <table class="table table-bordered table-responsive">
                <thead>
                    <tr>
                        <th>Online now</th>
                        <th>Number of users per day</th>
                        <th>Number of users per week</th>
                        <th>Number of users per month</th>
                    </tr>
                </thead>
                <tbody>                                   
                        <tr>
                            <td><?=$onlineNow ?></td>
                            <td><?=$userNumberOnSitePerDay ?></td>                           
                            <td><?=$userNumberOnSitePerWeek ?></td>                           
                            <td><?=$userNumberOnSitePerMonth ?></td>                           
                           
                        </tr>                    
                </tbody>
            </table>
        </div>
    </div>	