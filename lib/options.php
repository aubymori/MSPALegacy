<?php
function get_option($opt, $default = null)
{
    if (isset($_COOKIE[$opt]))
        return $_COOKIE[$opt];
    return $default;
}

function get_bool_option($opt, $default = false)
{
    $val = get_option($opt);
    if (is_null($val))
        return $default;
    return $val == "true";
}

function set_option($opt, $value = null)
{
    setcookie($opt, $value, time() + 34560000, "/"); // 400 days
    $_COOKIE[$opt] = $value;
}

function set_bool_option($opt, $value = false)
{
    set_option($opt, $value ? "true" : "false");
}