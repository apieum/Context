<?php

/**
 * File Template.php
 *
 * PHP version 5.2
 *
 * @category Classes
 * @package  Template
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Template.php
 *
 */
/**
 * Recursively replaces properties between '{' and '}' with datas from a container
 * by calling a method passed to constructor after container.
 * 
 * Replacements depend on objects types, 'apply' will call the method specific to a type.
 * If we have :
 * - a string: replace each occurences of properties and return a string 
 * - a property: it's a string delimited by '{' '}', will return the property as is
 * - an array or object that implement ArrayAccess: apply template on each element 
 * - an object: apply template on each property
 * - others : return datas as is. 
 *
 * @category Classes
 * @package  Template
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Context_Template
 *
 */
class Context_Template
{
    protected $getter;
    protected $container;
    protected $globalPattern = '@(?<!\\\)\{(?<datas>([^{}]+ | (?R)))(?<!\\\)\}@xm';
    protected $strictPattern = '@^((?<!\\\)\{)(?<datas>[^{}]+)((?<!\\\)\})$@xm';
    /**
     * Constructor
     *
     * @param object   $container the object from wich we get datas
     * @param function $getter    the method to call to retrieve datas
     */
    public function __construct($container, $getter='about')
    {
        $this->container =& $container;
        $this->getter    =& $getter;
    }
    /**
     * retrieve datas values depending on context properties defined in description
     *
     * @param mixed $datas the datas to normalize
     *
     * @return mixed normalized datas
     */
    public function apply($datas)
    {
        $type = ($datas instanceof ArrayAccess) ? 'Array' : ucfirst(gettype($datas));
        switch ($type) {
        case 'String':
                return $this->applyString($datas);
            break;
        case 'Array':
                return $this->applyArray($datas);
            break;
        case 'Object':
                return 
                    $datas===$this->container ? $datas : $this->applyObject($datas);
            break;
        default:
            return $datas;
        }
    }
    /**
     * retrieve datas values for each element of the array 
     * 
     * @param array $datas array to normalize
     * 
     * @return array 
     */
    protected function applyArray($datas)
    {
        foreach ($datas as $key=>$value) {
            $datas[$key] = $this->apply($value);
        }
        return $datas;

    }
    /**
     * retrieve datas values for each element of the object
     *
     * @param object $datas the object on wich apply template
     *
     * @return object
     */
    protected function applyObject($datas)
    {
        $datas = clone $datas;
        foreach ($datas as $property=>$value) {
            $datas->$property = $this->apply($value);
        }
        return $datas;

    }

    /**
     * retrieve datas values if datas is a string
     *
     * @param mixed $datas the datas to normalize
     *
     * @return mixed normalized datas
     */
    protected function applyString($datas)
    {
        if (preg_match($this->strictPattern, $datas, $match)) {
            return $this->replace($match);
        }
        $method = array($this, 'replace');
        $return = preg_replace_callback($this->globalPattern, $method, $datas);
        if ($return !== $datas) {
            $return = $this->apply($return);
        }
        return $return;
    }
    /**
     * Return the replacement of the given matches
     *
     * @param array $matches the result of the pattern matches
     *
     * @return string the replacement dpending on context description
     */
    protected function replace($matches)
    {
        return 
            $this->callGetter(
                $matches['datas'], $this->markOff($matches['datas'])
            );
    }
    /**
     * protect the string with delimiters
     *  
     * @param string $string the string to delimit
     * 
     * @return string
     */
    public function markOff($string)
    {
        return '\{'.$string.'\}';
    }
    /**
     * call the getter function with searched value and default value
     *
     * @param string $datas   the searched value
     * @param mixed  $default the default value if a property not exists
     *
     * @return mixed sanitized result of a function call
     */
    public function callGetter($datas, $default='')
    {
        $datas = call_user_func(
            array($this->container, $this->getter), $datas, $default
        );
        return $this->sanitize($datas);
    }
    /**
     * Sanitize datas after getting them from container
     * 
     * @param mixed $datas set of datas to clean
     * 
     * @example remove delimiters
     * @return mixed cleaned datas
     */
    public function sanitize($datas)
    {
        if (is_string($datas)) {
            return str_replace(array('\{','\}'), array('{','}'), $datas);
        } else {
            return $datas;
        }
    }
}


