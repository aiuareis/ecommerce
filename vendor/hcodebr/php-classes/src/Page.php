<?php
/**
 * Created by PhpStorm.
 * User: Aiua Reis Queiroz
 * Date: 08/01/2019
 * Time: 16:53
 */

namespace Hcode;

use Rain\Tpl;

class Page
{
    private $tpl;
    private $options = [];
    private $defalts = [
        "header"=>true,
        "footer"=>true,
        "data"=>[]
    ];

    public function __construct($opts = array(), $tpl_dir = "/views/")
    {
        $this->options = array_merge($this->defalts, $opts);
        //Configuração necessária para usar o Rain\Tpl como renderizador do html
        $config = array(
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"] . $tpl_dir,
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/",
            "debug"         => false // set to false to improve the speed
        );

        Tpl::configure( $config );

        $this -> tpl = new Tpl;

        $this->setData($this->options["data"]);

        if ($this->options["header"] === true) $this->tpl->draw("header");
    }

    private function setData($data = array())
    {
        foreach ($data as $key => $value){
            $this->tpl->assign($key, $value);}
    }

    public function setTpl($name, $data = array(), $returnHTML = false)
    {
        $this->setData($data);

        return $this->tpl->draw($name, $returnHTML);
    }


    public function __destruct()
    {
        if ($this->options["footer"] === true) $this->tpl->draw("footer");
    }

}