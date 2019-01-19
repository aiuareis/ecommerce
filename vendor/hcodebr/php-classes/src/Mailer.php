<?php
/**
 * Created by PhpStorm.
 * User: Aiua Reis Queiroz
 * Date: 15/01/2019
 * Time: 23:16
 */

namespace Hcode;

//Usando a redenderização de template do HTML com o Rain TPL
use Rain\Tpl;

class Mailer
{
    const USERNAME = "aiuarqueiroz@gmail.com";
    const PASSWORD = "1346798520";
    const NAME_FROM = "Loja Virtual";

    private $mail;

    public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
    {
        //Configuração necessária para usar o Rain\Tpl como renderizador do html
        $config = array(
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"] . "/views/email/",
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/",
            "debug"         => false // set to false to improve the speed
        );

        Tpl::configure( $config );

        $tpl = new Tpl;

        //$data recebe todos os dados do template
        //Metodo assign vai setar todos os dados no tamplate
        foreach ($data as $key => $value){
            $tpl->assign($key, $value);
        }
        //Recebe o html, o true é para ele jogar dentro da variavel e não na tela
        $html = $tpl->draw($tplName, true);
        //Devido o a classe está no escopo principal tem que colocar a contrabarra
        //Create a new PHPMailer instance
        $this->mail = new \PHPMailer;

        //Tell PHPMailer to use SMTP
        $this->mail->isSMTP();

        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $this->mail->SMTPDebug = 0;

        //Ask for HTML-friendly debug output
        $this->mail->Debugoutput = 'html';

        //Set the hostname of the mail server
        $this->mail->Host = 'smtp.gmail.com';
        // use
        // $this->mail->Host = gethostbyname('smtp.gmail.com');
        // if your network does not support SMTP over IPv6

        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $this->mail->Port = 587;

        //Set the encryption system to use - ssl (deprecated) or tls
        $this->mail->SMTPSecure = 'tls';

        //Whether to use SMTP authentication
        $this->mail->SMTPAuth = true;

        //Username to use for SMTP authentication - use full email address for gmail
        $this->mail->Username = Mailer::USERNAME;

        //Password to use for SMTP authentication
        $this->mail->Password = Mailer::PASSWORD;

        //Set who the message is to be sent from
        $this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);

        //Set an alternative reply-to address
        //$this->mail->addReplyTo($toAddress, $toName);

        //Set who the message is to be sent to
        $this->mail->addAddress($toAddress, $toName);

        //Set the subject line
        $this->mail->Subject = $subject;

        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $this->mail->msgHTML($html);

        //Replace the plain text body with one created manually
        $this->mail->AltBody = 'This is a plain-text message body';

        //Attach an image file
        //$this->mail->addAttachment('images/phpmailer_mini.png');

    }

    //Metodo que envia o e-mail
    public function send(){
        return $this->mail->send();
    }



}