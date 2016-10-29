<?php

namespace SefazDownloader;

class HTMLReader {

    public function __construct() {
        
    }

    public function read($html) {
        $r = array();

        $dom = new \DOMDocument();
        $dom->encoding = 'utf-8';
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $legend = array("Dados Gerais", "Dados da NF-e", "Emitente", "Destinatário", "Emissão", "Dados do Emitente", "Dados do Destinatário", "Totais", "ICMS", "Dados do Transporte", "Transportador", "Volumes", "Informações Adicionais", "Informações Complementares de Interesse do Contribuinte", "Dados dos Produtos e Serviços", "ICMS Normal e ST", "Imposto Sobre Produtos Industrializados", "PIS", "COFINS");

        //$tag = $xpath->query('//input[@name="ctl00$ContentPlaceHolder1$txtCaptcha"]');
        $tags = $xpath->query('//fieldset');
        echo "<pre>";
        foreach ($tags as $tag) {
            //echo '"'.trim($tag->nodeValue).'", ';
            //if (strstr($tag->nodeValue, "Dados Gerais")) {            
            foreach ($legend as $leg) {
                if (strstr($tag->nodeValue, $leg)) {
                    $filename = $dom->saveHTML($tag);
                    $fname = trim("xx");
                    $fo = fopen($fname . ".html", "w+");
                    fwrite($fo, $filename);
                    $ff = file_get_contents($fname . ".html");
                    //echo htmlentities($filename);
                    //HTMLReader::readNode($ff);
                    $r[HTMLReader::beautify($leg)] = HTMLReader::readTags($ff);
                }
            }

            //echo "<pre>";
            //print_r($tag);
            //print_r($tag->nodeValue);
            //$captcha = (trim($tag->getAttribute('value')));            
            //$html = SefazDownloader::getResult($_POST['chave'], $captcha);
        }
        print_r($r);
        echo "</pre>";
    }

    public function readTags($html) {

        $nfe = array();
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $label = '';

        //get all H1
        $items = $dom->getElementsByTagName('td');
        //display all H1 text
        for ($i = 0; $i < $items->length; $i++) {
            $labels = $dom->getElementsByTagName('label');
            //for ($j = 0; $j < $labels->length; $j++) {
            $label = $labels->item($i)->nodeValue;
            //}

            $spans = $dom->getElementsByTagName('span');
            //for ($k = 0; $k < $spans->length; $k++) {
            $span = $spans->item($i)->nodeValue;
            //$nfe[trim(utf8_decode($label))] = utf8_decode($span);
            $tlabel = (html_entity_decode(utf8_decode(($label))));
            $nfe[HTMLReader::beautify($tlabel)] = utf8_decode(str_replace(array("\r", "\n"), "",($span)));
            //}
            //       echo $items->item($i)->nodeValue . "<br/>";
        }
        return $nfe;
    }

    public static function removeAcentos($value, $normalize = 'n') {
        $from = "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ";
        $to = "aaaaeeiooouucAAAAEEIOOOUUC";

        $keys = array();
        $values = array();
        preg_match_all('/./u', $from, $keys);
        preg_match_all('/./u', $to, $values);
        $mapping = array_combine($keys[0], $values[0]);
        $value = strtr($value, $mapping);
        if ($normalize == 'u') {
            $value = strtoupper(strtolower($value));
        }
        if ($normalize == 'l') {
            $value = strtolower($value);
        }
        return $value;
    }

    public function beautify($string){        
        $r = ucwords($string);
        $s = str_replace(array("\r", "\n", " ","/"), "", $r);
        $t = HTMLReader::removeAcentos($s);
        return $t;
    }
}
