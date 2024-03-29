<?php

/**
 * Base class for admin page operations.
 * Class PluginPagesBase
 */
class PluginBase
{
    const PREFIX = 'dwc_';

    const  DONATION_TYPES_TABLE_NAME = "dwc_donation_types";
    const  DONATIONS_TABLE_NAME = "dwc_donations";
    const  DONATION_ITEMS_TABLE_NAME = 'dwc_donation_items';

    public function menu_operations()
    {

    }

    public function style_script_reg_operations()
    {

    }

    public function page_controller()
    {

    }

    /**
     * Instead of putting prefixes for every method name that needs for wordpress, this method strips prefix,
     * searches in public methods and if there's one, calls the method.
     * @param $name string undefined function name. It should start with the value defined in the PREFIX constant.
     * @param $arguments mixed method arguments to run.
     */
    public function __call($name, $arguments)
    {
        if (strpos($name, self::PREFIX) === 0) {
            $realMethodName = substr($name, strlen(self::PREFIX));
            if (self::isEligibleForMethodCall($realMethodName)) {
                call_user_func_array(array($this, $realMethodName), $arguments);
            } else {
                exit("Method `$name` is not eligible for public call!");
            }
        } else {
            exit("Method `$name` not found!");
        }
    }

    /**
     * Searches in public methods and returns if given method is public, but not static.
     * @param $name string
     * @return bool
     */
    public static function isEligibleForMethodCall($name)
    {
        $result = false;
        try {
            $reflection = new \ReflectionClass(get_called_class());
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            if (is_array($methods)) {
                foreach ($methods as $method) {
                    if ($method->name == $name && !$method->isStatic() && $method->isPublic()) {
                        $result = true;
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            $result = false;
        } finally {
            return $result;
        }
    }
}

