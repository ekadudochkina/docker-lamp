<?php
    if(!defined(YII_DEBUG)){
	$message = "Sorry but we cannot find page";
	$code = "404";
    }
?>
<section class="wpo-mainbody clearfix notfound-page error-page">
	<section class="container">
		<div class="page_not_found text-center clearfix space-padding-tb-100">
			<div class="col-sm-12 ">
                            <div class="error-page__body-container">
                                <div class="col-sm-12 error-page__body-inner">
                                    <div class="clearfix"></div>
                                    <div class="title error-page__title">
                                        <span class="error-page__code"><?=$code?></span>
                                        <span class="sub error-page__message"><?=CHtml::encode($message)?></span>
                                    </div>

                                    <div class="page-action error-page__button-holder">

                                            <a class="btn btn-lg btn-inverse-light radius-6x button button--green" href="<?=$this->createAbsoluteUrl("/")?>">Go back to homepage</a>
                                    </div>
                                </div>
				
                            </div>
                           
			</div>
		</div>
	</section>
</section>