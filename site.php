<?php
/**
 * Created by PhpStorm.
 * User: Aiua Reis Queiroz
 * Date: 23/01/2019
 * Time: 22:31
 */

use Hcode\Page;
use Hcode\Model\Category;
use Hcode\Model\Product;


$app->get('/', function() {
    $products = Product::listAll();

    $page = new Page();
    $page->setTpl("index",[
        'products'=> Product::checkList($products)
    ]);
});

$app->get("/categories/:idcategory", function ($idcategory){
    //Verifica se o parâmetro da página está sendo passado na URL, se não é a 1
    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
    $category = new Category();
    $category->get((int)$idcategory);
    //carrega os dados dos produtos, númro total de itens e quantidade de páginas
    $pagination = $category->getProductsPage($page);
    //Passa os dados de link e página pra o html
    $pages = [];
    //Adiciona ao array pages as informações
    for($i=1; $i <= $pagination['pages']; $i++){
        array_push($pages, [
            'link'=>'/categories/'. $category->getIdcategory() . '?page=' . $i,
            'page'=>$i
        ]);
    }
    $page = new Page();
    $page->setTpl("category", array(
        'category'=>$category->getValues(),
        'products'=>$pagination["data"],
        'pages'=>$pages
    ));
});

$app->get("/products/:desurl", function ($desurl){
    $product = new Product();
    $product->getFromURL($desurl);
    $page = new \Hcode\Page();
    $page->setTpl("product-detail", [
        'product'=>$product->getValues(),
        'categories'=>$product->getCategories()
    ]);


});