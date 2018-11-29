<?php

 
# Carregando o framework Slim...
require 'vendor/autoload.php';

 
# Iniciando o objeto de manipulação da API SlimFramework
$app = new \Slim\Slim();
$app->response()->header('Content-Type', 'application/json;charset=utf-8');
 
# Função de teste de funcionamento da API...
$app->get('/', function () {
    echo "Bem-vindo a API do Sistema de Clientes";
});
 
# Função para obter dados da tabela 'cliente'...
$app->get('/clientes',function(){
 
    # Variável que irá ser o retorno (pacote JSON)...
    $retorno = array();
 
    # Abrir conexão com banco de dados...
    $conexao = new MySQLi("SERVIDOR","USUARIO_SERVIDOR","SENHA_DO_USUARIO","BANCO_DE_DADOS");
 
    # Validar se houve conexão...
    if(!$conexao){ echo "Não foi possível se conectar ao banco de dados"; exit;}
 
    # Selecionar todos os cadastros da tabela 'cliente'...
    $registros = $conexao->query("select * from cliente");
 
    # Transformando resultset em array, caso ache registros...
    if($registros->num_rows>0){
        while($cliente = $registros->fetch_array(MYSQL_BOTH)) {
            $registro = array(
                        "CODIGO"   => $cliente["CODIGO"],
                        "NOME"     => utf8_encode($cliente["NOME"]),
                        "TELEFONE" => $cliente["TELEFONE"],
                        "EMAIL"    => $cliente["EMAIL"],
                    );
            $retorno[] = $registro;
        }
    }
 
    # Encerrar conexão...
    $conexao->close();
 
    # Retornando o pacote (JSON)...
    $retorno = json_encode($retorno);
    echo $retorno;
 
});
 
# Executar a API (deixá-la acessível)...
$app->run();
?>