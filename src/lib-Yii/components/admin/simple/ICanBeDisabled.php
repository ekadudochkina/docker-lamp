<?php
namespace Hs\Admin\Simple;

/**
 * Description of ICanBeDisabled
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
interface ICanBeDisabled
{
    function isEnabled();
    
    function enable();
    
    function disable();
}
