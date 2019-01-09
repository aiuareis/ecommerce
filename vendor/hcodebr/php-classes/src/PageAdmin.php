<?php
/**
 * Created by PhpStorm.
 * User: Aiua Reis Queiroz
 * Date: 09/01/2019
 * Time: 09:30
 */

namespace Hcode;


class PageAdmin extends Page
{

    public function __construct(array $opts = array(), $tpl_dir = "/views/admin/")
    {
        parent::__construct($opts, $tpl_dir);
    }

}