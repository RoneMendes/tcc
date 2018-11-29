<?php
// Definindo pacotes de retorno em padrгo JSON...
header ( 'Content-Type: application/json;charset=utf-8' );

require 'vendor/autoload.php';
// Create and configure Slim app
$config = [ 
		'settings' => [ 
				"determineRouteBeforeAppMiddleware" => true 
		] 
];
$app = new \Slim\App ( $config );

$app->get ( '/', function () {
	echo "Bem-vindo a API do Sistema de Clientes";
} );

// **************** Funзгo para obter dados da tabela 'cliente'...****************
$app->get ( '/clientes', function ($request, $response, $args) {
	
	// Variбvel que irб ser o retorno (pacote JSON)...
	$retorno = array ();
	
	// Abrir conexгo com banco de dados...
	$conexao = new MySQLi ( "localhost", "root", "", "tcc" );
	
	// Validar se houve conexгo...
	if (! $conexao) {
		echo "Nгo foi possнvel se conectar ao banco de dados";
		exit ();
	}
	
	// Selecionar todos os cadastros da tabela 'cliente'...
	$registros = $conexao->query ( "select * from clientes" );
	
	// Transformando resultset em array, caso ache registros...
	if ($registros->num_rows > 0) {
		while ( $cliente = $registros->fetch_assoc () ) {
			$registro = array (
					"codigo" => $cliente ["codigo"],
					"cpf" => $cliente ["cpf"],
					"nome" => utf8_encode ( $cliente ["nome"] ),
					"endereco" => $cliente ["endereco"],
					"estado" => $cliente ["estado"],
					"municipio" => $cliente ["municipio"],
					"telefone" => $cliente ["telefone"],
					"email" => $cliente ["email"],
					"senha" => $cliente ["senha"] 
			);
			$retorno [] = $cliente;
		}
	}
	
	// Encerrar conexгo...
	$conexao->close ();
	
	// Retornando o pacote (JSON)...
	// $retorno = json_encode($retorno);
	return $response->withJson ( $retorno );
} );

// **************** Funзгo para criar dados na tabela 'cliente'...****************
$app->post ( '/clientes/cadastrar', function ($request, $response, $args) {
	
	$dados = $request->getParsedBody ();
	// Abrir conexгo com banco de dados...
	$conexao = new MySQLi ( "localhost", "root", "", "tcc" );
	
	// Validar se houve conexгo...
	if (! $conexao) {
		echo "Nгo foi possнvel se conectar ao banco de dados";
		exit ();
	}
	
	// Selecionar todos os cadastros da tabela 'cliente'...
	
	$query = "INSERT INTO clientes VALUES (null,'{$dados['cpf']}','{$dados['nome']}','{$dados['endereco']}', '{$dados['estado']}', '{$dados['municipio']}', '{$dados['telefone']}', '{$dados['email']}', '{$dados['senha']}')";
	print_r ( $query );
	$conexao->query ( $query );
	
	$retorno = array (
			'codigo' => $conexao->insert_id 
	);
	
	return $response->withJson ( $retorno );
} );

// **************** Funзгo para deletar dados na tabela 'cliente'...****************

$app->delete ( '/clientes/deletar', function ($request, $response, $args) {
	
	$dados = $request->getParsedBody ();
	// Abrir conexгo com banco de dados...
	$conexao = new MySQLi ( "localhost", "root", "", "tcc" );
	
	// Validar se houve conexгo...
	if (! $conexao) {
		echo "Nгo foi possнvel se conectar ao banco de dados";
		exit ();
	}
	
	// Deletar cadastros da tabela 'cliente'...
	$query = "DELETE FROM clientes WHERE codigo = '{$dados['codigo']}'";
	// print_r($dados);
	$conexao->query ( $query );
	
	return $response->withJson ( $dados );
} );

// **************** Funзгo para alterar dados na tabela 'cliente'...****************
$app->put ( '/clientes/alterar', function ($request, $response, $args) {
	
	$dados = $request->getParsedBody ();
	// Abrir conexгo com banco de dados...
	$conexao = new MySQLi ( "localhost", "root", "", "tcc" );
	
	// Validar se houve conexгo...
	if (! $conexao) {
		echo "Nгo foi possнvel se conectar ao banco de dados";
		exit ();
	}
	
	// Inserir cadastros da tabela 'cliente'...
	
	$query = "UPDATE clientes SET cpf = '{$dados['cpf']}', nome = '{$dados['nome']}', endereco = '{$dados['endereco']}', estado = '{$dados['estado']}', municipio = '{$dados['municipio']}', telefone = '{$dados['telefone']}', email = '{$dados['email']}', senha = '{$dados['senha']}' WHERE codigo = '{$dados['codigo']}'";
	// print_r($dados);
	$conexao->query ( $query );
} );

// **************** Funзгo autenticar cliente ****************
$app->post ( '/autenticar', function ($request, $response, $args) {
	
	$dados = $request->getParsedBody ();
	// Abrir conexгo com banco de dados...
	$conexao = new MySQLi ( "localhost", "root", "", "tcc" );
	
	// Validar se houve conexгo...
	if (! $conexao) {
		echo "Nгo foi possнvel se conectar ao banco de dados";
		exit ();
	}
	
	$registros = $conexao->query ( "select * from clientes where email = '{$dados['email']}' and senha = '{$dados['senha']}'" );
	if ($registros->num_rows > 0) {
		$cliente = $registros->fetch_assoc ();
		return $response->withJson ( $cliente );
	} else {
		
		// $mensagem = array("Nгo foi possнvel autenticar");
		// return $response->withJson($mensagem);
		return "Nao foi possivel autenticar";
	}
} );

$app->options ( '/{routes:.+}', function ($request, $response, $args) {
	return $response;
} );

$app->add ( function ($req, $res, $next) {
	$response = $next ( $req, $res );
	return $response
			->withHeader ( 'Access-Control-Allow-Origin', '*' )
			->withHeader ( 'Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization' )
			->withHeader ( 'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS' );
} );

// Catch-all route to serve a 404 Not Found page if none of the routes match
// NOTE: make sure this route is defined last
$app->map ( [ 
		'GET',
		'POST',
		'PUT',
		'DELETE',
		'PATCH' 
], '/{routes:.+}', function ($req, $res) {
	$handler = $this->notFoundHandler; // handle using the default Slim page not found handler
	return $handler ( $req, $res );
} );

// Executar a API (deixб-la acessнvel)...
$app->run ();
?>