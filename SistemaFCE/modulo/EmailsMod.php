<?php
namespace pQn\SistemaFCE\modulo;


class EmailsMod extends BaseMod {
    public static function enviarMail($para,$asunto,$textoHTML,$de=null,$textoSimple=null,$cc=null,$bcc=null)
    {
        if(!isset($textoSimple))
        {
            $h2t = new \Html2Text($textoHTML,15); //agarra maximo 15 columnas
            $textoSimple = $h2t->convert();
        } 
        
        $crlf = "\n";
        $hdrs = array('Subject' => $asunto);
        
        if(isset($de))
            $hdrs['From']= $de;
        if(isset($cc))
            $hdrs['CC']= $cc;
        if(isset($bcc))
            $hdrs['BCC']= $bcc;

        $mime = new \Mail_mime($crlf);
    
        $mime->setTXTBody($textoSimple);
        $mime->setHTMLBody($textoHTML);

        $body = $mime->get();
        $hdrs = $mime->headers($hdrs);

        $mail =& \Mail::factory('mail');
        return $mail->send($para, $hdrs, $body);
    }
}
