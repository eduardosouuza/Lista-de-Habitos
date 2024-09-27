<?php

/**
 * Função que converte os parâmetros
 * de requisições HTTP POST e PUT em um Hábito.
 *
 * @return array O hábito convertido em um array associativo.
 */
function f_parametro_to_habito() {
    // Obtém o conteúdo da requisição
    $dados = file_get_contents("php://input");
    // Converte o JSON recebido em um array associativo e retorna
    return json_decode($dados, true);
}

/**
 * Função que retorna uma conexão com o banco de dados.
 *
 * @return mysqli A conexão com o banco de dados.
 */
function f_obtem_conexao() {
    // Parâmetros de conexão
    $servidor = "localhost";
    $usuario = "root";
    $senha = "";
    $bancodedados = "listadehabito";

    // Cria uma conexão com o banco de dados
    $conexao = new mysqli($servidor, $usuario, $senha, $bancodedados);

    // Verifica a conexão
    if ($conexao->connect_error) {
        die("Falha na conexão: " . $conexao->connect_error);
    }
    return $conexao;
}

/**
 * Função que retorna os hábitos.
 */
function f_select_habito() {
    // Inicializa a cláusula WHERE
    $queryWhere = " WHERE ";
    $primeiroParametro = true;
    $parametrosGet = array_keys($_GET);

    // Loop através dos parâmetros GET para construir a cláusula WHERE
    foreach ($parametrosGet as $param) {
        if (!$primeiroParametro) {
            $queryWhere .= " AND "; // Adiciona AND entre os parâmetros
        }
        $primeiroParametro = false;
        // Escapa o parâmetro para evitar injeção de SQL
        $queryWhere .= $param . " = '" . mysqli_real_escape_string(f_obtem_conexao(), $_GET[$param]) . "'";
    }

    // Monta a query SQL inicial
    $sql = "SELECT id, nome, status FROM habito";
    // Adiciona a cláusula WHERE se houver parâmetros
    if ($queryWhere != " WHERE ") {
        $sql .= $queryWhere;
    }

    // Obtém a conexão com o banco de dados
    $conexao = f_obtem_conexao();
    // Executa a query
    $resultado = $conexao->query($sql);

    // Verifica se a query retornou registros
    if ($resultado->num_rows > 0) {
        $jsonHabitoArray = array(); // Array para armazenar os hábitos
        while ($registro = $resultado->fetch_assoc()) {
            // Adiciona cada registro ao array de resultados
            $jsonHabitoArray[] = $registro;
        }
        // Retorna os resultados como JSON
        echo json_encode($jsonHabitoArray);
    } else {
        // Retorna um array JSON vazio se nenhum registro foi encontrado
        echo json_encode(array());
    }

    // Fecha a conexão com o banco de dados
    $conexao->close();
}

/**
 * Insere um novo hábito na tabela habito.
 */
function f_insert_habito() {
    // Obtém o hábito a partir dos parâmetros da requisição
    $habito = f_parametro_to_habito();
    // Escapa o nome do hábito
    $nome = mysqli_real_escape_string(f_obtem_conexao(), $habito["nome"]);

    // Monta a query de inserção
    $sql = "INSERT INTO habito (nome) VALUES ('$nome')";
    // Obtém a conexão com o banco de dados
    $conexao = f_obtem_conexao();

    // Executa a query e verifica se houve erro
    if (!($conexao->query($sql) === TRUE)) {
        $conexao->close();
        die("Erro: " . $sql . "<br>" . $conexao->error); // Imprime erro se ocorrer
    }

    // Adiciona o ID gerado e o status ao hábito
    $habito["id"] = mysqli_insert_id($conexao);
    $habito["status"] = "A"; // Status inicial como 'A' (Ativo)
    // Retorna o hábito inserido como JSON
    echo json_encode($habito);
    // Fecha a conexão com o banco de dados
    $conexao->close();
}

/**
 * Atualiza um hábito existente.
 */
function f_update_habito() {
    // Obtém o hábito a partir dos parâmetros da requisição
    $habito = f_parametro_to_habito();
    $id = $habito["id"];
    // Escapa os dados do hábito
    $nome = mysqli_real_escape_string(f_obtem_conexao(), $habito["nome"]);
    $status = mysqli_real_escape_string(f_obtem_conexao(), $habito["status"]);

    // Monta a query de atualização
    $sql = "UPDATE habito SET status = '$status', nome = '$nome' WHERE id = $id";
    // Obtém a conexão com o banco de dados
    $conn = f_obtem_conexao();

    // Executa a query e verifica se houve erro
    if (!($conn->query($sql) === TRUE)) {
        $conn->close();
        die("Erro ao atualizar: " . $conn->error); // Imprime erro se ocorrer
    }

    // Retorna o hábito atualizado como JSON
    echo json_encode($habito);
    // Fecha a conexão com o banco de dados
    $conn->close();
}

/**
 * Exclui um hábito existente.
 */
function f_delete_habito() {
    // Obtém o ID do hábito a ser deletado
    $id = (int)$_GET["id"]; // Cast para inteiro para segurança
    // Monta a query de deleção
    $sql = "DELETE FROM habito WHERE id = $id";
    // Obtém a conexão com o banco de dados
    $conn = f_obtem_conexao();

    // Executa a query e verifica se houve erro
    if (!($conn->query($sql) === TRUE)) {
        die("Erro ao deletar: " . $conn->error); // Imprime erro se ocorrer
    }
    // Fecha a conexão
    $conn->close();
}

// A variável de servidor REQUEST_METHOD contém o método HTTP da requisição
$metodo = $_SERVER['REQUEST_METHOD'];

// Verifica qual ação tomar com base no método HTTP
switch ($metodo) {
    case "GET": // Se a requisição for GET, chama a função de seleção
        f_select_habito();
        break;
    case "POST": // Se for POST, chama a função de inserção
        f_insert_habito();
        break;
    case "PUT": // Se for PUT, chama a função de atualização
        f_update_habito();
        break;
    case "DELETE": // Se for DELETE, chama a função de deleção
        f_delete_habito();
        break;
}
?>
