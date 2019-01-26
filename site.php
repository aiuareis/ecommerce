<?php
/**
 * Created by PhpStorm.
 * User: Aiua Reis Queiroz
 * Date: 23/01/2019
 * Time: 22:31
 */

use Hcode\Page;
use Hcode\Model\Category;


$app->get('/', function() {
    $page = new Page();
    $page->setTpl("index");
});

$app->get("/categories/:idcategory", function ($idcategory){
    $category = new Category();
    $category->get((int)$idcategory);
    $page = new Page();
    $page->setTpl("category", array(
        'category'=>$category->getValues(),
        'products'=>[]
    ));
});