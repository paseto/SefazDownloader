<?php

namespace sefazd;

class SefazDownloader {

    public function __construct() {
        
    }

    /**
     * Load captcha image
     * @return string image URL
     */
    public function loadCaptcha() {

        session_start();

        $url = "http://www.nfe.fazenda.gov.br/portal/consulta.aspx?tipoConsulta=completa&tipoConteudo=XbSeqxE8pl8=";
        $cookie = 'cookies1.txt';
        $useragent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'); // $_SERVER['HTTP_USER_AGENT'];

        /* Get __VIEWSTATE & __EVENTVALIDATION */
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

        $html = curl_exec($ch);

        curl_close($ch);

        $_viewstate = array();
        $_stategen = array();
        $_eventValidation = array();
        $_sstoken = array();
        $_captcha = array();

        preg_match('~<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="(.*?)" />~', $html, $_viewstate);
        preg_match('~<input type="hidden" name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value="(.*?)" />~', $html, $_stategen);
        preg_match('~<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="(.*?)" />~', $html, $_eventValidation);
        preg_match('~<input type="hidden" name="ctl00\$ContentPlaceHolder1\$token" id="ctl00_ContentPlaceHolder1_token" value="(.*?)" />~', $html, $_sstoken);
        preg_match('~<img id=\"ctl00_ContentPlaceHolder1_imgCaptcha\" src=\"(.*)\" style~', $html, $_captcha);


        $stategen = $_stategen[1];
        $_SESSION['stategen'] = $stategen;

        $token = $_sstoken[1];
        $_SESSION['token'] = $token;

        $viewstate = $_viewstate[1];
        $_SESSION['viewstate'] = $viewstate;

        $eventValidation = $_eventValidation[1];
        $_SESSION['eventValidation'] = $eventValidation;

        $captcha = $_captcha[1];

        return $captcha;
    }

    
    /**
     * Curl response from webservice
     * @param type $chNFe
     * @param type $txtCaptcha
     * @return type
     */
    public function getResult($chNFe, $txtCaptcha = NULL) {

        if ($txtCaptcha == NULL) {
            $txtCaptcha = $_SESSION['captcha'];
        }

        $url = "http://www.nfe.fazenda.gov.br/portal/consulta.aspx?tipoConsulta=completa&tipoConteudo=XbSeqxE8pl8=";

        $cookie = 'cookies1.txt';

        $useragent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'); // $_SERVER['HTTP_USER_AGENT'];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

        session_start();
        $postfields = array();
        $postfields['__EVENTTARGET'] = "";
        $postfields['__EVENTARGUMENT'] = "";
        $postfields['__VIEWSTATE'] = $_SESSION['viewstate'];
        $postfields['__VIEWSTATEGENERATOR'] = $_SESSION['stategen'];
        $postfields['__EVENTVALIDATION'] = $_SESSION['eventValidation'];
        $postfields['ctl00$ContentPlaceHolder1$txtChaveAcessoCompleta'] = $chNFe;
        $postfields['ctl00$ContentPlaceHolder1$txtCaptcha'] = $txtCaptcha;
        $postfields['ctl00$ContentPlaceHolder1$btnConsultar'] = 'Continuar';
        $postfields['ctl00$ContentPlaceHolder1$token'] = $_SESSION['token'];
        $postfields['hiddenInputToUpdateATBuffer_CommonToolkitScripts'] = '1';

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        $result = curl_exec($ch);

        //$html = utf8_encode($result);
        $html = $result;

        //Optional write output
        $fo = fopen($chNFe . '.html', 'w+');
        fwrite($fo, $html);

        curl_close($ch);
        return $html;
    }

}
