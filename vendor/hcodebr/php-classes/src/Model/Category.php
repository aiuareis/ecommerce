<?php
/**
 * Created by PhpStorm.
 * User: Aiua Reis Queiroz
 * Date: 19/01/2019
 * Time: 20:31
 */

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;

class Category extends Model
{

    //Função para listar todos as Categorias do Banco
    public static function listAll(){
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
    }

    public function save(){
        $sql = new Sql();
        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)",
            array(
                ":idcategory"=>$this->getIdcategory(),
                ":descategory"=>$this->getDescategory()
            ));
        $this->setData($results[0]);
        Category::updateFile();
    }

    public function get($idcategory){
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
            ':idcategory'=>$idcategory
        ));
        $this->setData($results[0]);

    }

    public function delete(){
        $sql = new Sql();
        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
            ':idcategory'=>$this->getIdcategory()
        ));
        Category::updateFile();
    }

    public static function updateFile(){
        $categories = Category::listAll();
        $html = [];
        foreach ($categories as $row){
            array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
        }
        //file_put_contents = função que altera um arquivo
        //$_SERVER['DOCUMENT_ROOT' = carrega o diretorio raiz da aplicação
        //DIRECTORY_SEPARATOR = Carrega o separador de acordo com o servidor windows ou linux
        //implode = transforma um array em string
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html",
            implode('', $html));
    }
    //Função que tras os produtos de acordo com a categoria
    public function getProducts($related = true){
        $sql = New Sql();

        if($related === true){
            return $sql->select("SELECT * FROM tb_products WHERE idproduct IN(
                                            SELECT a.idproduct 
                                            FROM tb_products a
                                            INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                                            WHERE b.idcategory = :idcategory)",
                                            [
                                                ':idcategory'=>$this->getIdcategory()
                                            ]);
        } else{
            return $sql->select("SELECT * FROM tb_products WHERE idproduct NOT IN(
                                            SELECT a.idproduct 
                                            FROM tb_products a
                                            INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                                            WHERE b.idcategory = :idcategory)",
                [
                    ':idcategory'=>$this->getIdcategory()
                ]);
        }
    }

    //Função para paginação
    public function getProductsPage($page=1, $itensPerPage = 3){
        //start = de onde deve partir a paginação
        $start = ($page - 1) * $itensPerPage;

        $sql = new Sql();

        //carrega a quantidade de linhas
        $results = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS * FROM 
            tb_products tp
            INNER JOIN tb_productscategories tp1 ON tp.idproduct = tp1.idproduct
            INNER JOIN tb_categories tc ON tp1.idcategory = tc.idcategory
            WHERE
            tc.idcategory = :idcategory
            LIMIT $start,$itensPerPage",
            [
                ':idcategory'=>$this->getIdcategory()
            ]);
        //Quantidade total de linhas
        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal");

        return[
            //dados do produto
            'data'=>Product::checkList($results),
            //Quantidade total de registros
            'total'=>(int)$resultTotal[0]["nrtotal"],
            //Quantidade de páginas
            'pages'=>ceil($resultTotal[0]["nrtotal"] / $itensPerPage)
        ];
    }

    //Força para receber obrigatoriamente um objeto
    public function addProduct(Product $product){
        $sql = new Sql();
        $sql->query("INSERT INTO tb_productscategories (idcategory, idproduct)
                              VALUES (:idcategory, :idproduct)", [
                                  'idcategory'=>$this->getIdcategory(),
                                    'idproduct'=>$product->getIdproduct()
        ]);
    }

    //Força para receber obrigatoriamente um objeto
    public function removeProduct(Product $product){
        $sql = new Sql();
        $sql->query("DELETE FROM tb_productscategories 
                              WHERE idcategory = :idcategory
                              AND idproduct = :idproduct", [
            'idcategory'=>$this->getIdcategory(),
            'idproduct'=>$product->getIdproduct()
        ]);
    }





}