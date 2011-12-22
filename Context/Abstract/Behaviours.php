<?php
/**
 * File Context_Behaviours_Abstract.php
 *
 * PHP version 5.2
 *
 * @category Abstracts
 * @package  Context
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Context_Behaviours_Abstract.php
 *
 */

require_once __DIR__.DIRECTORY_SEPARATOR.'Context.php';
/**
 * This class add behaviours to context.
 * A context behaviour is an aliased/named function, method or class,
 * that benefits of descriptions and template replacements at call or construction.
 *  
 * Behaviours helps to :
 * - launch functions within the context with 'proceed' method.
 * - create objects within the context with 'construct' method. 
 *  
 * @category Abstracts
 * @package  Context
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     Context_Behaviours_Abstract
 *
 */

abstract class Context_Behaviours_Abstract extends Context_Abstract
{
    protected $behaviours= array();

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
            .serialize($this->behaviours)
            .serialize($this->descriptions)
        );
    }
    /**
     * For Functions and Classes that depends on context, you can add behaviours
     * to call functions or create objects within the context.
     * This method add a behaviour associated to a name.
     * 
     * @param string $name      the name of the behaviour
     * @param mixed  $behaviour function or class name as string | a method as array
     * 
     * @return object $this for chaining
     */
    public function addBehaviour($name, $behaviour)
    {
        $this->behaviours[$name] =& $behaviour;
        return $this;
    }
    /**
     * Delete a behaviour
     * 
     * @param string $name the name of the behaviour to delete
     * 
     * @return object $this for chaining 
     */
    public function delBehaviour($name)
    {
        unset($this->behaviours[$name]);
        return $this;
    }
    /**
     * test if behaviour exists
     * 
     * @param string $name the name of the behaviour
     * 
     * @return object $this for chaining 
     */
    public function hasBehaviour($name)
    {
        return isset($this->behaviours[$name]);
    }

    /**
     * return a normalized beahviour
     * 
     * @param string $name    the name of the behaviour
     * @param mixed  $default the default value to return if not exists
     * 
     * @return object $this for chaining 
     */
    public function getBehaviour($name, $default=null)
    {
        $name = $this->normalize($name);
        if ($this->hasBehaviour($name)) {
            $name = $this->behaviours[$name];
        } else {
            $name = $default;
        }
        return $this->normalize($name);
    }
    /**
     * Launch a behaviour as a function or create object with the given arguments.
     * Behaviour name and arguments are normalized to stay contextual
     * 
     * @param string $name the behaviour name to launch
     * @param array  $args arguments passed to the behaviour function
     * 
     * @return mixed the result of a function call or an object
     */
    public function proceed($name, $args=array())
    {
        $behaviour = $this->getBehaviour($name);
        if (is_callable($behaviour)) {
            return $this->normalize(
                call_user_func_array($behaviour, $this->normalize($args))
            );
        } else {
            $behaviour = new ReflectionClass($behaviour);
            return $this->normalize(
                $behaviour->newInstanceArgs($this->normalize($args))
            );
        }
    }

    /**
     * Identical as proceed but store result in descriptions 
     * and return it as is if exists. 
     * That permit to work with the same instance of a behaviour object.
     * 
     * @param string $name the behaviour name to launch
     * @param array  $args arguments passed to the behaviour function
     * 
     * @return mixed the result of a function call or an objet
     */
    public function &proceedOnce($name, $args=array())
    {
        $identity = "$name with ".serialize($args);
        if (!isset($this->descriptions[$identity])) {
            $this->describe($identity, $this->proceed($name, $args));
        }
        return $this->descriptions[$identity];
    }
    /**
     * Return if a behaviour is a class
     * 
     * @param string $name behaviour name
     * 
     * @return bool wether the behaviour seems to be a class 
     */
    public function isClass($name)
    {
        $behaviour = $this->getBehaviour($name);
        return is_string($behaviour) && class_exists($behaviour, true);
    }
    /**
     * Return if a behaviour is callable : method or function
     * 
     * @param string $name behaviour name
     * 
     * @return bool wether the behaviour seems to be a class 
     */
    public function isCallable($name)
    {
        return is_callable($this->getBehaviour($name));
    }
}