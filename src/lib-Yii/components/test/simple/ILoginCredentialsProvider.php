<?php
namespace Hs\Test\Simple;

/**
 *
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
interface ILoginCredentialsProvider
{
     function getUserName();

     function getPassword();

     function getLoginRoute();

     function getLoginInputSelector();

     function getPasswordInputSelector();

     function getSubmitLoginFormSelector();
}
