<?php
/**
 * File Context_Abstract.php
 *
 * PHP version 5.2
 *
 * @category Abstracts
 * @package  Context
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Context_Abstract.php
 *
 */
/**
 * The context helps to provides high cohesion within objects while preserving
 * lows dependencies.
 * A minimal context is set by a subject and an environment, 
 * optionaly, you can set a moment while it occurs, and describe some properties.
 * Properties can be used to make replacements in other descriptions values, 
 * in subject, environment or moment.
 * Replacements are made by normalize method defined in child class.
 * 
 * @category Abstracts
 * @package  Context
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Context_Abstract
 *
 */

abstract class Context_Abstract
{
    const DEFAULT_MOMENT = 10;
    protected $subject;
    protected $environment;
    protected $moment;
    protected $descriptions= array();
    /**
     * Constructor
     * 
     * @param mixed $subject     the context subject
     * @param mixed $environment the context environment
     * @param mixed $moment      optional property to set while the context occured
     */
    public function __construct($subject, $environment, $moment=self::DEFAULT_MOMENT)
    {
        $this
            ->with(&$subject)
            ->into(&$environment)
            ->during(&$moment);
    }
    /**
     * Give an hash that identify the current context
     * 
     * @return string a hash of contextual properties
     */
    public function identify()
    {
        return md5(
            serialize($this->subject)
            .serialize($this->environment)
            .serialize($this->moment)
            .serialize($this->descriptions)
        );
    }
    /**
     * Set the context subject 
     * 
     * @param mixed $subject the context subject to set
     * 
     * @return object $this for chaining
     */
    public function with($subject)
    {
        $this->subject =& $subject;
        return $this;
    }
    /**
     * return the normalized subject
     * 
     * @return mixed the subject normalized within the context
     */
    public function what()
    {
        return $this->normalize($this->subject);
    }
    /**
     * Set the context environment
     * 
     * @param mixed $environment the context environment
     * 
     * @return object $this for chaining
     */
    public function into($environment)
    {
        $this->environment =& $environment;
        return $this;
    }
    /**
     * Return the contextual environment
     * 
     * @return mixed the environment normalize within the context
     */
    public function where()
    {
        return $this->normalize($this->environment);
    }
    /**
     * Set the context moment
     * 
     * @param mixed $moment an optional indication of while context proceed
     * 
     * @return object $this for chaining
     */
    public function during($moment)
    {
        $this->moment =& $moment;
        return $this;
    }
    /**
     * Return contextual moment
     * 
     * @return mixed moment normalized within the the context
     */
    public function when()
    {
        return $this->normalize($this->moment);
    }
    /**
     * Describe a context property, used to normalize context values
     * 
     * @param string $what  a name of a context property
     * @param mixed  $value the value of a property (should be a scalar)
     * 
     * @return object $this for chaining
     */
    public function describe($what, $value)
    {
        $this->descriptions[$what] =& $value;
        return $this;
    }
    /**
     * Pop out a description from context
     * 
     * @param string $what a name of a context property
     * 
     * @return object $this for chaining
     */
    public function popOut($what)
    {
        unset($this->descriptions[$what]);
        return $this;
    }
    /**
     * retrieve normalized informations about context
     * 
     * @param string $what    the name of a context property
     * @param mixed  $default the default value if a property not exists
     * 
     * @return mixed a property or default normalized within the context
     */
    public function about($what, $default='')
    {
        return $this->normalize($this->aboutStatic($what, $default));
    }
    /**
     * retrieve informations about context without normalize them
     * 
     * @param string $what    the name of a context property
     * @param mixed  $default the default value if a property not exists
     * 
     * @return mixed a description or default
     */
    public function &aboutStatic($what, $default='')
    {
        if (isset($this->descriptions[$what])) { 
            $default =& $this->descriptions[$what];
        }
        return $default;
    }
    /**
     * retrieve datas values depending on context properties defined in description
     * override in child to provide changes on datas.
     * 
     * @param mixed $datas the datas to normalize
     * 
     * @return mixed normalized datas
     */
    abstract public function normalize($datas);
}
