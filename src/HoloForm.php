<?php

/**
 * @author William Raendchen
 * @date 2018-12-03
 */

/***********************
 * Usage example:
$form = (new Form("userUploadForm"))
	->addInput("csvFile", "file")
	->addInput("b2bUser", "checkbox", "1")
	->addInput("roleId", "text", $roleId)
	->addInput('submit', "submit", "Submit");
	->setEnctype(Form::ENC_MULTI);

if($request->has($form->id)){
	$formValues = $request->input($form->id);
	// do something with form values

	// Repopulate the form with its own submission data
	$form->updateValues($formValues);
}
 */

namespace Holonaut\HoloForm;

class HoloForm
{
    CONST ENC_DEFAULT = 'application/x-www-form-urlencoded';
    CONST ENC_MULTI = 'multipart/form-data';

    public $method;
    public $action;
    public $classes;
    public $enctype;
    public $id;
    public $inputs;

	/**
	 * Form constructor.
	 * @param string $id
	 * @param string $method
	 * @param string $action
	 * @param string $classes
	 * @param string $extra
	 */
    public function __construct(string $id = 'form', string $method = 'POST', string $action = '', string $classes = '', string $extra = '')
    {
        $this->method = $method;
        $this->action = $action;
        $this->classes = $classes;
        $this->extra = $extra;
        $this->id = $id;
        $this->inputs = [];
        $this->enctype = self::ENC_DEFAULT;
    }

    public function setEnctype($enctype){
        $this->enctype = $enctype;
    }

	/**
	 * @param string $name
	 * @param string $type
	 * @param string|null $value
	 * @param string $extra
	 * @param string|null $classes
	 * @param array $selectOptions
	 * @param bool $multiple
	 * @return $this
	 */
    public function addInput(
    	string $name,
		string $type,
		string $value = null,
		string $extra = '',
		string $classes = null,
		array $selectOptions = [],
		$multiple = false,
		$additionalOptions = []
	) {
    	$inputName = $this->id . '[' . $name . ']';

    	if($multiple == true){
    		$inputName.= '[]';
		}

        $input = (object)[
        	'id' => $name,
            'name' => $inputName,
            'type' => $type,
            'extra' => $extra,
            'value' => $value,
			'classes' => $classes,
			'selectOptions' => $selectOptions
        ];

    	// add restricted additional options if necessary
		$allowedOptions = ['comment'];
    	if(!empty($additionalOptions)){
    		$options = HoloHelper::cleanArray($additionalOptions, $allowedOptions);

    		foreach ($options as $key => $option) {
    			$input->{$key} = $option;
			}
		}

        $this->inputs[$name] = $input;
        return $this;
    }

    public function updateValues($params)
    {
        foreach($params as $key => $param) {
            $this->inputs[$key]->value = $param;
        }
    }
}