<?php
ini_set('display_errors', 1);
session_start();
require_once 'vendor/autoload.php';

//$_SESSION['captcha'] = '';

use SefazDownloader\SefazDownloader;
use SefazDownloader\HTMLReader;

$sd = new SefazDownloader();

$a = filter_input(INPUT_POST, 'action');
if ($a == 'getDoc') {
    $chave = filter_input(INPUT_POST, 'chave');
    $captcha = filter_input(INPUT_POST, 'captcha');
    $_SESSION['captcha'] = $captcha;
    //$html = SefazDownloader::getResult($chave, $captcha);

    $cnpj = "86933033000100";

    $path = "/var/www/sefazd/";

    $pass = "245792457";

    $xml = SefazDownloader::downloadXmlSefaz($captcha, $chave, $cnpj, $path, $pass);

    if ($xml == 'erro') {
        $html = SefazDownloader::getResult($chave, $captcha);
        $match = strstr($html, 'Dados da NF-e');
        if (!$match) {
            HTMLReader::read($html);
        } else {
            echo "Nenhuma nota encontrada.";
        }
        $_SESSION['captcha'] = '';
    }else{
        //processa xml upload
    }

    //HTMLReader::read($html);
//    echo "<br>-- html resposta 1 -- <br/>" . $html;
//
//    $match = strstr($html, 'Dados da NF-e');
//    echo "<br>-- match = " . $match . " -- <br/>";
//    if (!$match) {
//        echo "<br>-- Não foi encontrada a tag chave de acesso, requisitando nova chave -- <br/>";
//        $dom = new DOMDocument();
//        $dom->loadHTML($html);
//        $xpath = new DOMXPath($dom);
//        $tags = $xpath->query('//input[@name="ctl00$ContentPlaceHolder1$txtCaptcha"]');
//        foreach ($tags as $tag) {
//            $ncaptcha = (trim($tag->getAttribute('value')));
//            if ($ncaptcha != $captcha) {
//                $captcha = $ncaptcha;
//                echo "<br>-- Captcha encontrada: $captcha -- <br/>";
//                $html = SefazDownloader::getResult($chave, $captcha);
//                echo "<br>-- html resposta 2 -- <br/>" . $html;
//            } else {
//                $_SESSION['captcha'] = '';
//                echo "<br>-- mesmo captcha encontrado -- <br/>";
//            }
//        }
//    }
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title></title>
  </head>
  <body>
    <form method="post" action="index.php">
      <input type="hidden" name="action" value="getDoc" />
      <?php
      if (empty($_SESSION['captcha'])) {
          ?>
          <img src="<?php echo $sd->loadCaptcha(); ?>" />
          <input type="text" name="captcha" placeholder="captcha" />
          <?php
      } else {
          echo '<input type="text" name="captcha" placeholder="captcha" value="' . $_SESSION['captcha'] . '" />';
      }
      ?>            
      <input type="text" name="chave" placeholder="chave" />
      <input type="submit" value="Pesquisar" />
    </form>    
  </body>
</html>


<?php ?>