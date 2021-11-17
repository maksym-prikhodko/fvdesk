<?php
if ( ! function_exists('link_to'))
{
    function link_to($url, $title = null, $attributes = array(), $secure = null)
    {
        return app('html')->link($url, $title, $attributes, $secure);
    }
}
if ( ! function_exists('link_to_asset'))
{
    function link_to_asset($url, $title = null, $attributes = array(), $secure = null)
    {
        return app('html')->linkAsset($url, $title, $attributes, $secure);
    }
}
if ( ! function_exists('link_to_route'))
{
    function link_to_route($name, $title = null, $parameters = array(), $attributes = array())
    {
        return app('html')->linkRoute($name, $title, $parameters, $attributes);
    }
}
if ( ! function_exists('link_to_action'))
{
    function link_to_action($action, $title = null, $parameters = array(), $attributes = array())
    {
        return app('html')->linkAction($action, $title, $parameters, $attributes);
    }
}
