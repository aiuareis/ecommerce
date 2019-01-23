<?php
/**
 * Created by PhpStorm.
 * User: Aiua Reis Queiroz
 * Date: 09/01/2019
 * Time: 22:16
 */

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Mailer;
use Hcode\Model;
use mysql_xdevapi\Exception;

class User extends Model
{
    //As funções de get e set vem do arquivo Model, que preenche isso dinamicamente

    const SESSION = "User";
    //Chave de criptografia, deve ter no mínimo 16 letras ou multipos de 8
    const SECRET = "HcodePhp7_Secret";

    protected $fields = [
        "iduser", "idperson", "deslogin", "despassword", "inadmin", "desemail", "nrphone", "dtergister", "desperson"
    ];
    //Função de login
    public static function login($login, $password){
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN" => $login
        ));

        if (count($results) === 0){
            throw new \Exception("Usuario inexistente ou senha inválida.");
        }

        $data = $results[0];

        if(password_verify($password, $data["despassword"]) === true){
            $user = new User();
            $user->setData($data);
            $_SESSION[User::SESSION] = $user->getValues();
            return $user;
        } else {
            throw new \Exception("Usuario inexistente ou senha inválida.");
        }

    }
    //Função que verifica se o usuário está logado
    public static function verifyLogin($inadmin = true){
        if(//Se a sessão não existe
            !isset($_SESSION[User::SESSION])
            || //Se ela for falsa
            !$_SESSION[User::SESSION]
            || //Se o id não for maior que zero
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
            || //Se ele é administrador
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
        ){
            header("Location: /admin/login");
            exit;
        }

    }
    //Função para fazer o logout
    public static function logout(){
        $_SESSION[User::SESSION] = null;
    }
    //Função para listar todos os usuários
    public static function listAll(){
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
    }
    //Função para salvar um cadastro
    public function save(){
        $sql = new Sql();
        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",
            array(
            ":desperson"=>$this->getDesperson(),
            ":deslogin"=>$this->getDeslogin(),
            ":despassword"=>$this->getDespassword(),
            ":desemail"=>$this->getDesemail(),
            ":nrphone"=>$this->getNrphone(),
            ":inadmin"=>$this->getInadmin()
        ));
        $this->setData($results[0]);
    }
    //Pegar dado de um usuário específico
    public function get($iduser){
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING (idperson) WHERE a.iduser = :iduser",
            array(
               ":iduser"=>$iduser
            ));
        $this->setData($results[0]);
    }
    //Função para atualizar os dados
    public function update(){
        $sql = new Sql();
        $results =$sql->select("CALL sp_usersupdate_save(:iduser,:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",
            array(
                ":iduser"=>$this->getIduser(),
                ":desperson"=>$this->getDesperson(),
                ":deslogin"=>$this->getDeslogin(),
                ":despassword"=>$this->getDespassword(),
                ":desemail"=>$this->getDesemail(),
                ":nrphone"=>$this->getNrphone(),
                ":inadmin"=>$this->getInadmin()
            ));
        $this->setData($results[0]);
    }

    //Função para deletar um registro
    public function delete(){
        $sql = new Sql();
        $sql->query("CALL sp_users_delete(:iduser)",
            array(
                ":iduser"=>$this->getIduser()
            ));
    }

    //Função para executar o comando para iniciar o processo de recuperar uma senha
    public static function getForgot($email){
        $sql = new Sql();
        //Pega todos os dados do usuário das duas tabelas
        $results = $sql->select("
        SELECT *
        FROM tb_persons a
        INNER JOIN tb_users b USING(idperson)
        where a.desemail = :email", array(
            ":email"=>$email
        ));
        //Verifica se trouxe algum resultado, caso contrário exibe um erro
        if(count($results) === 0){
            throw new \Exception("Não foi possível recuperar a senha");
        }
        //Caso contenha um resultado, grava na tabela de log relacionada a recuperação de senha
        else{
            $data = $results[0];
            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
               ":iduser"=> $data["iduser"],
                //Função do PHP para pegar o id do usuário
                ":desip"=>$_SERVER["REMOTE_ADDR"]
            ));
            //Verifica se conseguiu gravar na tabela de log
            if (count($results2) === 0){
                throw new \Exception("Não foi possível recuperar a senha");
            }
            else{
                //recebe os dados da inserção vindo da procedure;
                $dataRecovery = $results2[0];
                //codifica na base 64 e encripta os dados;
                //openssl_encrypt recebe 4 parâmetros: Tipo da criptografia, Chave de criptografia, dados para criptografar, modo de criptografia;
                //$code = base64_encode(openssl_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));
                $code = base64_encode($dataRecovery["idrecovery"]);
                //Rota que será usada para validar o código enviado
                $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
                //Criando o email com os dados do construtor da Classe Mailer
                $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir sua Senha", "forgot",
                    array(
                       "name"=>$data["desperson"],
                       "link"=>$link
                    ));
                $mailer->send();

                return $data;

            }



        }
    }
    //Função que valida o código recebido via GET do link de recuperação de senha
    public static function validForgotDecrypt($code){
        //descriptografa o código vindo da URL
        $idrecovery = base64_decode($code);
        $sql = new Sql();
        $results = $sql->select("
        SELECT * 
        FROM db_ecommerce.tb_userspasswordsrecoveries a
        INNER JOIN db_ecommerce.tb_users b USING(iduser)
        INNER JOIN db_ecommerce.tb_persons c USING(idperson)
        WHERE
        -- Pega o Id gerado na recuperação da senha
        a.idrecovery = :idrecovery
        AND 
        -- Verifica se essa linha já foi usada em uma recuperação de senha
        a.dtrecovery IS NULL
        AND
        -- Verifica se já passou o intervalo de uma hora desde a solicitação da nova senha
        DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW()
        ",
            array(
            ":idrecovery"=>$idrecovery
        ));
        //Se não retonar nada do banco
        if(count($results) === 0){
            throw new \Exception("Não foi possível recuperar a senha");
            //Retorna os dados do usuário para o index
        } else{
            return $results[0];
        }

}

public static function setForgotUsed($idrecovery){
        $sql = new Sql();

        $sql->query("UPDATE db_ecommerce.tb_userspasswordsrecoveries SET
                              dtrecovery = NOW()
                              WHERE idrecovery = :idrecovery;", array(
                                  ":idrecovery"=>$idrecovery
        ));

}

public function setPassword($password){
        $sql = new Sql();
        $passCod = base64_encode($password);
        $sql->query("UPDATE db_ecommerce.tb_users SET
                              despassword = :password
                                WHERE
                                iduser = :iduser;", array(
                                    ":password"=>$password,
                                    ":iduser"=>$this->getIduser()));
}

}