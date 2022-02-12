<?php
/**
 * Класс виджета формы.
 * 
 *  
 * @author Sarychev Alexei <freddis336@gmail.com>
 * @package Hs\Forms
 * @todo использовать магические методы, чтобы избежать дублирования кода
 */
class ActiveForm extends CActiveForm
{

    /**
     * Флаг отмены передачи данных через массив.
     * @var Bool 
     */
    protected $noNamespaceFlag = false;

    /**
     * Имя массива, который передается с формой.
     *
     * @var String 
     */
    protected $namespace = null;

    /**
     * Эта функция позволяет задать имя параметра в который попадут данные формы.
     * По-умолчанию имя массива является именем модели, но если в одной форме две модели одинакового класса, то будет проблема.
     * Например $this->getRequest()->getParam("MyNamespace"); вместо $this->getRequest()->getParam("User");
     * @param String $namespace
     */
    public function setNamespace($namespace = null)
    {
	$this->noNamespaceFlag = false;
	$this->namespace = $namespace;
    }

    /**
     * Эта функция делает так, что данные передаются без массива.
     * То есть данные формы нужно будет забирать по отдельности по имени полe.
     * Например $this->getRequest()->getParam("name"); вместо $this->getRequest()->getParam("LoginForm")['name']
     * @param String $namespace
     */
    public function noNamespace()
    {
	$this->noNamespaceFlag = true;
	return true;
    }

    /**
     * Renders a text field for a model attribute.
     * This method is a wrapper of {@link CHtml::activeTextField}.
     * Please check {@link CHtml::activeTextField} for detailed information
     * about the parameters for this method.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field
     */
    public function textField($model, $attribute, $htmlOptions = array())
    {
	if ($this->noNamespaceFlag)
	    $htmlOptions['name'] = $attribute;
	else if ($this->namespace != null)
	    $htmlOptions['name'] = $this->namespace . '[' . $attribute . ']';

	return parent::textField($model, $attribute, $htmlOptions);
    }

    /**
     * Renders a file field for a model attribute.
     * This method is a wrapper of {@link CHtml::activeFileField}.
     * Please check {@link CHtml::activeFileField} for detailed information
     * about the parameters for this method.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions additional HTML attributes
     * @return string the generated input field
     */
    public function fileField($model, $attribute, $htmlOptions = array())
    {
	if ($this->noNamespaceFlag)
	    $htmlOptions['name'] = $attribute;
	else if ($this->namespace != null)
	    $htmlOptions['name'] = $this->namespace . '[' . $attribute . ']';

	return parent::fileField($model, $attribute, $htmlOptions);
    }

    /**
     * Renders a password field for a model attribute.
     * This method is a wrapper of {@link CHtml::activePasswordField}.
     * Please check {@link CHtml::activePasswordField} for detailed information
     * about the parameters for this method.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field
     */
    public function passwordField($model, $attribute, $htmlOptions = array())
    {
	if ($this->noNamespaceFlag)
	    $htmlOptions['name'] = $attribute;
	else if ($this->namespace != null)
	    $htmlOptions['name'] = $this->namespace . '[' . $attribute . ']';

	return parent::passwordField($model, $attribute, $htmlOptions);
    }

    /**
     * Displays the first validation error for a model attribute.
     * This is similar to {@link CHtml::error} except that it registers the model attribute
     * so that if its value is changed by users, an AJAX validation may be triggered.
     * @param CModel $model the data model
     * @param string $attribute the attribute name
     * @param array $htmlOptions additional HTML attributes to be rendered in the container div tag.
     * Besides all those options available in {@link CHtml::error}, the following options are recognized in addition:
     * <ul>
     * <li>validationDelay</li>
     * <li>validateOnChange</li>
     * <li>validateOnType</li>
     * <li>hideErrorMessage</li>
     * <li>inputContainer</li>
     * <li>errorCssClass</li>
     * <li>successCssClass</li>
     * <li>validatingCssClass</li>
     * <li>beforeValidateAttribute</li>
     * <li>afterValidateAttribute</li>
     * </ul>
     * These options override the corresponding options as declared in {@link options} for this
     * particular model attribute. For more details about these options, please refer to {@link clientOptions}.
     * Note that these options are only used when {@link enableAjaxValidation} or {@link enableClientValidation}
     * is set true.
     * <ul>
     * <li>inputID</li>
     * </ul>
     * When an CActiveForm input field uses a custom ID, for ajax/client validation to work properly 
     * inputID should be set to the same ID
     * 
     * Example:
     * <pre>
     * <div class="form-element">
     *    <?php echo $form->labelEx($model,'attribute'); ?>
     *    <?php echo $form->textField($model,'attribute', array('id'=>'custom-id')); ?>
     *    <?php echo $form->error($model,'attribute',array('inputID'=>'custom-id')); ?>
     * </div>
     * </pre>
     * 
     * When client-side validation is enabled, an option named "clientValidation" is also recognized.
     * This option should take a piece of JavaScript code to perform client-side validation. In the code,
     * the variables are predefined:
     * <ul>
     * <li>value: the current input value associated with this attribute.</li>
     * <li>messages: an array that may be appended with new error messages for the attribute.</li>
     * <li>attribute: a data structure keeping all client-side options for the attribute</li>
     * </ul>
     * This should NOT be a function but just the code, Yii will enclose the code you provide inside the
     * actual JS function.
     * @param boolean $enableAjaxValidation whether to enable AJAX validation for the specified attribute.
     * Note that in order to enable AJAX validation, both {@link enableAjaxValidation} and this parameter
     * must be true.
     * @param boolean $enableClientValidation whether to enable client-side validation for the specified attribute.
     * Note that in order to enable client-side validation, both {@link enableClientValidation} and this parameter
     * must be true. This parameter has been available since version 1.1.7.
     * @return string the validation result (error display or success message).
     * @see CHtml::error
     */
    public function error($model, $attribute, $htmlOptions = array(), $enableAjaxValidation = true, $enableClientValidation = true)
    {

	if (!isset($htmlOptions['class']))
	    $htmlOptions['class'] = 'alert-danger';
	$ret = parent::error($model, $attribute, $htmlOptions, $enableAjaxValidation, $enableClientValidation);
	return $ret;
    }

    /**
     * Displays a summary of validation errors for one or several models.
     * This method is very similar to {@link CHtml::errorSummary} except that it also works
     * when AJAX validation is performed.
     * @param mixed $models the models whose input errors are to be displayed. This can be either
     * a single model or an array of models.
     * @param string $header a piece of HTML code that appears in front of the errors
     * @param string $footer a piece of HTML code that appears at the end of the errors
     * @param array $htmlOptions additional HTML attributes to be rendered in the container div tag.
     * @return string the error summary. Empty if no errors are found.
     * @see CHtml::errorSummary
     */
    public function errorSummary($models, $header = null, $footer = null, $htmlOptions = array())
    {
	$header = "<div class='tb_alert alert_warning '>";
	$header.= "<span class='tb_alert_icon icon-exclamation-sign'></span>    ";
	$footer = '</div>';
	$ret = parent::errorSummary($models, $header, $footer, $htmlOptions);
	return $ret;
    }
    
    /**
     * Создает поле календаря для выбора дат. Использует виджеет JQueryUI.
     * 
     * @see CJuiDatePicker
     * @param CActiveRectod $model Модель
     * @param String $attribute Название поля модели
     * @param String $options Опции виджета 
     * @param String[] $htmlOptions Ассоциативный массив аттрибутов HTML тега
     */
    public function datepickerField($model,$attribute,$options= null,$htmlOptions=array())
    {
	Yii::import("zii.widgets.jui.CJuiDatePicker");
	$widget = new CJuiDatePicker($this);
	$widget->init();
	$widget->model = $model;
	$widget->attribute = $attribute;
	$widget->htmlOptions = $htmlOptions;
	if($options)
	    foreach($options as $key=>$opt)
		$widget[$key] = $opt;
	
	$widget->run();
    }
    
    /**
     * Начало формы. Запрещено к использованию, так как не экономит время - используй HTML.
     * 
     * @deprecated 
     * @return type
     */
    public function begin($action = '', $method = 'post', $htmlOptions = array())
    {
	return CHtml::beginForm($action, $method, $htmlOptions);
    }

    /**
     * Завершение формы. Запрещено к использованию, так как не экономит время - используй HTML.
     * 
     * @deprecated 
     * @return type
     */
    public function end()
    {
	return CHtml::endForm();
    }

}
