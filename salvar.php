<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <script src="js/Cookie.js"></script>
    <?php
        include('./lib.php');

        $con = new Conexao("localhost", "root", "admin", "db_ponto");
        $con->conectar();
        echo "<br><br>";

        if(!empty($_COOKIE['objetoPai'])){
            echo "Objeto Pai<br>";

            $infoPai = $_COOKIE['objetoPai'];
            
            $infoPai = explode("<n>", $infoPai);
            $nomePai = $infoPai[1]; // NOME
            echo "Nome: ".$nomePai;  
            
            echo "<br>";

            $infoPai = explode("<p>", $infoPai[2]);
            $posicaoPai = $infoPai[1]; // POSICAO
            echo "Posicao: ".$posicaoPai;
            
            echo "<br>";

            $infoPai = explode("<r>", $infoPai[2]);
            $rotacaoPai = $infoPai[1]; // ROTACAO
            echo "Rotacao: ".$rotacaoPai;

            echo "<br>";

            $idPai = $con->query("select count(*) as tamanho from db_pontos.objetopai")[0]['tamanho'];
            $con->query("INSERT INTO db_pontos.objetopai (id, nome, posicao, rotacao) VALUES ('".$idPai."', '".$nomePai."', '".$posicaoPai."', '".$rotacaoPai."');");

            $cont = 0;
            while(!empty($_COOKIE['objeto_'.$cont])){
                $cont += 1;
            }

            for ($i=0; $i < $cont; $i++) { 
                echo "<br>";
                echo "Objeto Filho (".($i).")<br>";

                $infoFilho = $_COOKIE['objeto_'.$i];
                
                $infoFilho = explode("<n>", $infoFilho);
                $nomeFilho = $infoFilho[1]; // NOME
                echo "Nome: ".$nomeFilho;  

                echo "<br>";

                $infoFilho = explode("<l>", $infoFilho[2]);
                $linkFilho = $infoFilho[1]; // NOME
                echo "Link: ".$linkFilho;
                
                echo "<br>";

                $infoFilho = explode("<p>", $infoFilho[2]);
                $posicaoFilho = $infoFilho[1]; // POSICAO
                echo "Posicao: ".$posicaoFilho;
                
                echo "<br>";

                $infoFilho = explode("<r>", $infoFilho[2]);
                $rotacaoFilho = $infoFilho[1]; // ROTACAO
                echo "Rotacao: ".$rotacaoFilho;

                echo "<br>";

                $infoFilho = explode("<cl>", $infoFilho[2]);
                $corFilho = $infoFilho[1]; // ROTACAO
                echo "Cor: ".$corFilho;

                echo "<br>";

                $con->query("INSERT INTO db_pontos.objeto (nome, tipo, posicao, rotacao, cor, idPai) VALUES ('".$nomeFilho."', '".$linkFilho."', '".$posicaoFilho."', '".$rotacaoFilho."', '".$corFilho."', '".$idPai."');");
                echo '<script>Cookie.deleteCookie("objeto_'.$i.'");</script>';
            }
            echo '<script>Cookie.deleteCookie("objetoPai");</script>';
            header('Location: /');
        }
    ?>
</body>
</html>