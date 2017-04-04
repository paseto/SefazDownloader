<?php

namespace SefazDownloader;

class HTMLReader
{
    public function read($html)
    {
        $r = array();

        $dom = new \DOMDocument();
        $dom->encoding = 'utf-8';
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $legend = array('Dados Gerais', 'Dados da NF-e', 'Emitente', 'Destinatário', 'Emissão', 'Dados do Emitente', 'Dados do Destinatário', 'Totais', 'ICMS', 'Dados do Transporte', 'Transportador', 'Volumes', 'Informações Adicionais', 'Informações Complementares de Interesse do Contribuinte', 'ICMS Normal e ST', 'Imposto Sobre Produtos Industrializados', 'PIS', 'COFINS');

        $tags = $xpath->query('//fieldset');
        $tagCount = 0;
        foreach ($tags as $tag) {
            ++$tagCount;
            $fname = 'tag'.$tagCount;
            foreach ($legend as $leg) {
                if (strstr($tag->nodeValue, $leg)) {
                    $filename = $dom->saveHTML($tag);
                    $fo = fopen($fname.'.html', 'w+');
                    fwrite($fo, $filename);
                    $ff = file_get_contents($fname.'.html');
                    $ignore = 0;
                    if ($leg == 'Volumes') {
                        $ignore = 1;
                    }
                    $r[self::beautify($leg)] = self::readTags($ff, $ignore);
                }
            }
            if (is_file($fname)) {
                unlink($fname);
            }
        }

        return $r;
    }

    public function readTags($html, $ignore = 0)
    {
        $nfe = array();
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $label = '';

        $items = $dom->getElementsByTagName('td');
        $content = array();
        for ($i = 0; $i < $items->length; ++$i) {
            $labels = $dom->getElementsByTagName('label');
            if (is_object($labels->item($i + $ignore))) {
                $label = $labels->item($i + $ignore)->nodeValue;
                $spans = $dom->getElementsByTagName('span');
                $span = $spans->item($i)->nodeValue;
                $tlabel = (html_entity_decode(utf8_decode(($label))));
                $nfe[self::beautify($tlabel)] = utf8_decode(str_replace(array("\r", "\n"), '', ($span)));
            }
        }

        return $nfe;
    }

    public static function removeAcentos($value, $normalize = 'n')
    {
        $from = 'áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ';
        $to = 'aaaaeeiooouucAAAAEEIOOOUUC';

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

    public function beautify($string)
    {
        $r = ucwords($string);
        $s = str_replace(array("\r", "\n", ' ', '/'), '', $r);
        $t = self::removeAcentos($s);

        return $t;
    }
}
