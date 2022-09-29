<?php
    class Conexao {
        private $conexaoBanco;
        private string $host;
        private string $usuario;
        private string $senha;
        private string $banco;

        public function __construct(string $host, string $usuario, string $senha, string $banco){
            $this->host = $host;
            $this->usuario = $usuario;
            $this->senha =  $senha;
            $this->banco = $banco;
        }

        public function conectar(){
            try {
                $this->conexaoBanco = new PDO('mysql:host='.$this->host.';', $this->usuario, $this->senha);
                $this->conexaoBanco->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch(PDOException $e){
                echo 'Erro: '.$e->getMessage();
            }
        }

        public function desconectar(){
            unset($this->conexao);
        }

        public function query($comando){
            try {
                $resultado = $this->conexaoBanco->query($comando);
                $rows = $resultado->fetchAll( PDO::FETCH_ASSOC );
                return $rows;
            }
            catch(PDOException $e){
                echo 'Erro: '.$e->getMessage();
            }
        }
    }

    class Objeto {

    }
?>