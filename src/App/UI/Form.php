<?php namespace Redooor\Redminportal\App\UI;

class Form
{
    /*
     * Generate an HTML input form
     *
     * @param string Name and ID of input form
     * @param string Value of input form
     * @param array Options for input form
     * @return View
     */
    public function inputer($name, $value = null, $options = array())
    {
        $data = [
            'input_name' => $name,
            'input_id' => $name,
            'input_value' => $value,
        ];
        
        // Check for class
        if (array_key_exists('class', $options)) {
            $data['wrapper_classes'] = $options['class']; // Save value
            unset($options['class']); // Remove key from array
        }
        
        // Check for ID
        if (array_key_exists('id', $options)) {
            $data['input_id'] = $options['id']; // Save value
            unset($options['id']); // Remove key from array
        }
        
        // Check for Label
        if (array_key_exists('label', $options)) {
            $data['label'] = $options['label']; // Save value
            unset($options['label']); // Remove key from array
        }
        
        // Check for Label Class
        if (array_key_exists('label_classes', $options)) {
            $data['label_classes'] = $options['label_classes']; // Save value
            unset($options['label_classes']); // Remove key from array
        }
        
        // Check for Help Text
        if (array_key_exists('help_text', $options)) {
            $data['help_text'] = $options['help_text']; // Save value
            unset($options['help_text']); // Remove key from array
        }
        
        $data['input_options'] = $options; // Remaining options
                
        return view('redminportal::partials.form-input', $data);
    }
    
    /*
     * Generate an HTML input form with tagging labels
     *
     * @param string Value of the input form (optional, defaults to null)
     * @param string Title of the form (optional, defaults to Tags)
     * @param string Footnote for the form (optional, defaults to standard wording)
     * @return View
     */
    public function tagger($value = null, $title = null, $footnote = null)
    {
        $data = [
            'value' => $value,
            'title' => $title,
            'footnote' => $footnote
        ];
        
        return view('redminportal::partials.form-tagsinput', $data);
    }
    
    /*
     * Generate an HTML input form with email suggestion
     *
     * @param string Value of the input form (optional, defaults to null)
     * @param bool True if the form is a required field (optional, defaults to true)
     * @return View
     */
    public function emailInputer($value = null, $required = true)
    {
        $data = [
            'value' => $value,
            'required' => $required
        ];
        
        return view('redminportal::partials.form-input-email-typeahead', $data);
    }
}
