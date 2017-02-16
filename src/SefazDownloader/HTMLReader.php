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

        $legend = array("Dados Gerais", "Dados da NF-e", "Emitente", "Destinatário", "Emissão", "Dados do Emitente", "Dados do Destinatário", "Totais", "ICMS", "Dados do Transporte", "Transportador", "Volumes", "Informações Adicionais", "Informações Complementares de Interesse do Contribuinte", "ICMS Normal e ST", "Imposto Sobre Produtos Industrializados", "PIS", "COFINS");
        //$legend = array("Dados Gerais", "Dados da NF-e", "Emitente", "Destinatário", "Emissão", "Dados do Emitente", "Dados do Destinatário", "Totais", "ICMS", "Dados do Transporte", "Transportador", "Volumes", "Informações Adicionais", "Informações Complementares de Interesse do Contribuinte", "Dados dos Produtos e Serviços", "ICMS Normal e ST", "Imposto Sobre Produtos Industrializados", "PIS", "COFINS");
        //$legend = array("Dados dos Produtos e Serviços");

        //$tag = $xpath->query('//input[@name="ctl00$ContentPlaceHolder1$txtCaptcha"]');
        $tags = $xpath->query('//fieldset');
        foreach ($tags as $tag) {
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
                    $ignore = 0;
                    if ($leg == "Volumes") {
                        $ignore = 1;
                    }
                    $r[HTMLReader::beautify($leg)] = HTMLReader::readTags($ff, $ignore);
                }
            }
        }
        return $r;
    }

    public function readTags($html, $ignore = 0) {

        $nfe = array();
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $label = '';

        //get all H1
        $items = $dom->getElementsByTagName('td');
        $content = array();
        for ($i = 0; $i < $items->length; $i++) {            
//            foreach ($items->item($i)->childNodes as $node) {                
//                $nv = '';
//                if ($node->nodeName == "label"){
//                    $content[] = [$node->nodeName => $node->nodeValue];
//                    $nv = $node->nodeValue;
//                }
//                if ($node->nodeName == "span"){
//                    $content[$nv] = [$node->nodeName => $node->nodeValue];
//                }
//            }

            $labels = $dom->getElementsByTagName('label');
            //for ($j = 0; $j < $labels->length; $j++) {
            $label = $labels->item($i + $ignore)->nodeValue;
            //}            
            $spans = $dom->getElementsByTagName('span');
            //for ($k = 0; $k < $spans->length; $k++) {
            $span = $spans->item($i)->nodeValue;
            //echo $label." => ".$span."<br/>";
            //$nfe[trim(utf8_decode($label))] = utf8_decode($span);
            $tlabel = (html_entity_decode(utf8_decode(($label))));            
            $nfe[HTMLReader::beautify($tlabel)] = utf8_decode(str_replace(array("\r", "\n"), "", ($span)));
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

    public function beautify($string) {
        $r = ucwords($string);
        $s = str_replace(array("\r", "\n", " ", "/"), "", $r);
        $t = HTMLReader::removeAcentos($s);
        return $t;
    }

}
