<?php
/**
 * sfEfWidgetFormSelectCheckbox represents an array of checkboxes
 *
 * @package    sfEfWidgetFormExtraPlugin
 * @subpackage widget
 * @author     Yaismel Miranda Pons <yaismelmp@googlemail.com>
 * @version    SVN: $Id$
 */
class sfEfWidgetFormSelectCheckbox extends sfWidgetForm
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * choices:         An array of possible choices (required)
   *  * label_separator: The separator to use between the input checkbox and the label
   *  * class:           The class to use for the main <ul> tag
   *  * separator:       The separator to use between each input checkbox
   *  * formatter:       A callable to call to format the checkbox choices
   *                     The formatter callable receives the widget and the array of inputs as arguments
   *  * template:        The template to use when grouping option in groups (%group% %options%)
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('choices');

    $this->addOption('class', 'checkbox_list');
    $this->addOption('label_separator', '&nbsp;');
    $this->addOption('separator', "\n");
    $this->addOption('formatter', array($this, 'formatter'));
    $this->addOption('template', '%group% %options%');

    $this->addOption('theme', 'default');
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The value selected in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if ('[]' != substr($name, -2))
    {
      $name .= '[]';
    }

    if (is_null($value))
    {
      $value = array();
    }

    $choices = $this->getOption('choices');
    if ($choices instanceof sfCallable)
    {
      $choices = $choices->call();
    }

    // with groups?
    if (count($choices) && is_array(current($choices)))
    {
      $parts = array();
      foreach ($choices as $key => $option)
      {
        $parts[] = strtr($this->getOption('template'), array('%group%' => $key, '%options%' => $this->formatChoices($name, $value, $option, $attributes)));
      }

      return implode("\n", $parts);
    }
    else
    {
      return $this->formatChoices($name, $value, $choices, $attributes);;
    }
  }

  protected function formatChoices($name, $value, $choices, $attributes)
  {
    $inputs = array();
    
    foreach ($choices as $key => $option)
    {
      if (is_array($option))
      {
        $inputs[] = array(
        	'input' => strtr($this->getOption('template'), array('%group%' => $key, '%options%' => $this->formatChoices($name, $value, $option, $attributes))), 
        	'label' => ''
        );
      }
      else
      {
        $baseAttributes = array(
        	'name' => $name, 
        	'type' => 'checkbox', 
        	'value' => self::escapeOnce($key), 
        	'id' => $id = $this->generateId($name, self::escapeOnce($key))
        );
        
        if ((is_array($value) && in_array(strval($key), $value)) || strval($key) == strval($value))
        {
          $baseAttributes['checked'] = 'checked';
        }
        
        $inputs[] = array(
        	'input' => $this->renderTag('input', array_merge($baseAttributes, $attributes)), 
        	'label' => $this->renderContentTag('label', $option, array('for' => $id))
        );
      }
    }
    
    return call_user_func($this->getOption('formatter'), $this, $inputs);
  }

  public function formatter($widget, $inputs)
  {
    $rows = array();
    foreach ($inputs as $input)
    {
      $rows[] = $this->renderContentTag('li', $input['input'].$this->getOption('label_separator').$input['label']);
    }
    return $this->renderContentTag('ul', implode($this->getOption('separator'), $rows), array('class' => $this->getOption('class'))) .
    sprintf(<<<EOF
<script type="text/javascript">
  jQuery(document).ready(function() {
    jQuery("ul.%s input:checkbox").checkbox(jQuery.extend({}, {
      imagesPath: '/sfEfWidgetFormExtraPlugin/images/'
    }));
  });
</script>
EOF
   ,
   $this->getOption('class'));
  }

  public function __clone()
  {
    if ($this->getOption('choices') instanceof sfCallable)
    {
      $callable = $this->getOption('choices')->getCallable();
      $class = __CLASS__;
      if (is_array($callable) && $callable[0] instanceof $class)
      {
        $callable[0] = $this;
        $this->setOption('choices', new sfCallable($callable));
      }
    }
  }

  /**
   * Gets the stylesheet paths associated with the widget.
   *
   * @return array An array of stylesheet paths
   */
  public function getStylesheets()
  {
    return array('/sfEfWidgetFormExtraPlugin/css/jquery.checkbox.'. $this->getOption('theme') .'.css' => 'all');
  }

  /**
   * Gets the JavaScript paths associated with the widget.
   *
   * @return array An array of JavaScript paths
   */
  public function getJavascripts()
  {
    return array('/sfEfWidgetFormExtraPlugin/js/jquery.checkbox.js' => '/sfEfWidgetFormExtraPlugin/js/jquery.checkbox.js');
  }
}