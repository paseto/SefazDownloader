<?php

namespace sefazd;

class SefazDownloader {

    public function __construct() {
        
    }

    public function downloadXmlSefaz($txtCaptcha, $chNFe, $CNPJ, $PathCertificado, $PassCertificado) {

        $CNPJ;

        $PathCertificado;

        $PassCertificado;
        
        $url = "http://www.nfe.fazenda.gov.br/portal/consulta.aspx?tipoConsulta=completa&tipoConteudo=XbSeqxE8pl8=";

        $cookie = 'cookies1.txt';

        $useragent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, TRUE);        
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        
        $postfields = array();
        $postfields['__EVENTTARGET'] = "";
        $postfields['__EVENTARGUMENT'] = "";
        $postfields['__VIEWSTATE'] = $_SESSION['viewstate'];
        $postfields['__VIEWSTATEGENERATOR'] = $_SESSION['stategen'];
        $postfields['__EVENTVALIDATION'] = $_SESSION['eventValidation'];

        $postfields['ctl00$txtPalavraChave'] = "";

        $postfields['ctl00$ContentPlaceHolder1$txtChaveAcessoCompleta'] = $chNFe;
        $postfields['ctl00$ContentPlaceHolder1$txtCaptcha'] = $txtCaptcha;
        $postfields['ctl00$ContentPlaceHolder1$btnConsultar'] = 'Continuar';
        $postfields['ctl00$ContentPlaceHolder1$token'] = $_SESSION['token'];
        $postfields['hiddenInputToUpdateATBuffer_CommonToolkitScripts'] = '1';
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        $html = curl_exec($ch); // Get result after login page.
        curl_close($ch);

        $ch = curl_init();
        $url_det_nfe = 'http://www.nfe.fazenda.gov.br/portal/consultaCompleta.aspx?tipoConteudo=XbSeqxE8pl8=';

        curl_setopt($ch, CURLOPT_URL, $url_det_nfe);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, TRUE);        
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);        

        $html = curl_exec($ch); // Get result after login page.
        curl_close($ch);


        preg_match('~Chave de Acesso~', $html, $tagTeste);
        preg_match('~<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="(.*?)" />~', $html, $viewstate);
        preg_match('~<input type="hidden" name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value="(.*?)" />~', $html, $stategen);
        preg_match('~<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="(.*?)" />~', $html, $eventValidation);
        $stategen = $stategen[1];
        $viewstate = $viewstate[1];
        $eventValidation = $eventValidation[1];

        $tagDownload = '';
        if (!empty($tagTeste)){
            $tagDownload = $tagTeste[0];
        }
        ////        try {
//            $tagDownload = $tagTeste[0];
//        } catch (\Exception $e) {
//            throw new \Exception('Não foi possível fazer o download do XML, por favor atualize o captcha e tente novamente (sessão expirada)');
//        }

        if ($tagDownload == "Chave de Acesso") {

            $url_download = "http://www.nfe.fazenda.gov.br/portal/consultaCompleta.aspx?tipoConteudo=XbSeqxE8pl8=";

            if (!file_exists($PathCertificado . $CNPJ . '_priKEY.pem') ||
                    !file_exists($PathCertificado . $CNPJ . '_priKEY.pem') ||
                    !file_exists($PathCertificado . $CNPJ . '_priKEY.pem')) {
                throw new \Exception('Certificado digital não encontrado na pasta: ' . $PathCertificado . '!');
            }

            $ch_download = curl_init();
            curl_setopt($ch_download, CURLOPT_URL, $url_download);
            curl_setopt($ch_download, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch_download, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch_download, CURLOPT_COOKIEJAR, $cookie);
            curl_setopt($ch_download, CURLOPT_COOKIEFILE, $cookie);
            curl_setopt($ch_download, CURLOPT_HEADER, TRUE);
            // this with CURLOPT_SSLKEYPASSWD 
            curl_setopt($ch_download, CURLOPT_SSLKEY, $PathCertificado . $CNPJ . '_priKEY.pem');
            // The --cacert option
            curl_setopt($ch_download, CURLOPT_CAINFO, $PathCertificado . $CNPJ . '_certKEY.pem');
            // The --cert option
            curl_setopt($ch_download, CURLOPT_SSLCERT, $PathCertificado . $CNPJ . '_pubKEY.pem');
            // Cert pass
            curl_setopt($ch_download, CURLOPT_SSLCERTPASSWD, $PassCertificado);
            curl_setopt($ch_download, CURLOPT_FOLLOWLOCATION, FALSE);
            curl_setopt($ch_download, CURLOPT_REFERER, $url_download);
            //curl_setopt($ch_download, CURLOPT_VERBOSE, 1);

            curl_setopt($ch_download, CURLOPT_CONNECTTIMEOUT, 50);
            curl_setopt($ch_download, CURLOPT_TIMEOUT, 400); //timeout in seconds
            // Log
            //curl_setopt($ch_download, CURLOPT_STDERR, fopen("dump", "wb"));
            curl_setopt($ch_download, CURLOPT_USERAGENT, $useragent);
            // Collecting all POST fields
            $postfields_download = array();
            $postfields_download['__EVENTTARGET'] = "";
            $postfields_download['__EVENTARGUMENT'] = "";
            $postfields_download['__VIEWSTATE'] = $viewstate;
            $postfields_download['__VIEWSTATEGENERATOR'] = $stategen;
            $postfields_download['__EVENTVALIDATION'] = $eventValidation;
            $postfields_download['ctl00$txtPalavraChave'] = '';
            $postfields_download['ctl00$ContentPlaceHolder1$btnDownload'] = 'Download do documento*';
            $postfields_download['ctl00$ContentPlaceHolder1$abaSelecionada'] = '';
            $postfields_download['hiddenInputToUpdateATBuffer_CommonToolkitScripts'] = 1;

            curl_setopt($ch_download, CURLOPT_POST, 1);
            curl_setopt($ch_download, CURLOPT_POSTFIELDS, $postfields_download);

            $response = curl_exec($ch_download); // Get result after login page.

            $download_link_arr = array();

            $header_size = curl_getinfo($ch_download, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);            

            curl_close($ch_download);
            
            preg_match_all('/Location: (.*?)\r\n/sm', $header, $download_link_arr);
            $download_link_ = $download_link_arr[1];

            $download_link = $download_link_[0];

            $ch_download = curl_init();
            curl_setopt($ch_download, CURLOPT_URL, $download_link);
            curl_setopt($ch_download, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch_download, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch_download, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch_download, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch_download, CURLOPT_COOKIEJAR, $cookie);
            curl_setopt($ch_download, CURLOPT_COOKIEFILE, $cookie);

            // this with CURLOPT_SSLKEYPASSWD 
            curl_setopt($ch_download, CURLOPT_SSLKEY, $PathCertificado . $CNPJ . '_priKEY.pem');
            // The --cacert option
            curl_setopt($ch_download, CURLOPT_CAINFO, $PathCertificado . $CNPJ . '_certKEY.pem');
            // The --cert option
            curl_setopt($ch_download, CURLOPT_SSLCERT, $PathCertificado . $CNPJ . '_pubKEY.pem');
            // Cert pass
            curl_setopt($ch_download, CURLOPT_SSLCERTPASSWD, $PassCertificado);
            //curl_setopt($ch_download, CURLOPT_VERBOSE, 1);
            curl_setopt($ch_download, CURLOPT_CONNECTTIMEOUT, 50);

            $response_xml = curl_exec($ch_download);
            //Optional write output
            $fo = fopen($chNFe . '.xml', 'w+');
            fwrite($fo, $response_xml);

            curl_close($ch_download);

            return $response_xml;
        } else {
            return "erro";
        }
    }

    /**
     * Load captcha image
     * @return string image URL
     */
    public function loadCaptcha() {

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
        
        $html = $result;
        
        $fo = fopen($chNFe . '.html', 'w+');
        fwrite($fo, $html);

        curl_close($ch);
        return $html;
    }

}
