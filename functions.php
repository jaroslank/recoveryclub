<?php 

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "db";

$connect = mysqli_connect( $db_host, $db_user, $db_pass, $db_name );

function login($connect) {
    if (isset($_POST['acessar']) AND !empty($_POST['email']) AND !
    empty($_POST['senha'])); {

        $email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
        $senha = sha1($_POST['senha']);
        $query = "SELECT * from usuarios WHERE email = '$email' AND 
            senha = '$senha' " ;
        $executar = mysqli_query($connect, $query);
        $return = mysqli_fetch_assoc($executar);

        if (!empty($return["email"])) {
            //echo "Bem-vindo " . $return['nome'];
            session_start();
            $_SESSION['nome']  = $return['nome'];
            $_SESSION['imagem']  = $return['imagem'];
            $_SESSION['data_cadastro'] = $return['data_cadastro'];
            $_SESSION['email']  = $return['email'];
            $_SESSION['id']  = $return['id'];
            $_SESSION['ativa']  = TRUE;
            header("location: painel.php");
        }else {
            echo "Usuário ou senha não encontrado(s)!";
        }
    }
}
function numUsuarios($connect){
    $mysql = "SELECT COUNT(*) as user_count FROM usuarios";

    $result = $connect->query($mysql);

    if ($result) {
        
        $row = $result->fetch_assoc();
        
        return $row['user_count'];
    } else {
        return null;
    }
}

function logout(){
    session_start();
    session_unset();
    session_destroy();
    header("location: login.php");
}

/* Seleciona(busca) um resultado com base no ID */
function buscaUnica($connect, $tabela, $id) {
    $query  = "SELECT * FROM $tabela where id =". (int) $id;
    $execute = mysqli_query($connect, $query);
    $result = mysqli_fetch_assoc($execute);
    return $result; 
}

function buscar( $connect, $tabela, $where = 1, $order = "") {
    if (!empty($order)) {
        $order = "ORDER BY $order";
    }
    $query = "SELECT * FROM $tabela WHERE $where $order";
    $execute = mysqli_query($connect, $query);
    $results = mysqli_fetch_all($execute, MYSQLI_ASSOC);
    return $results; 
}

function seguranca() {
    if (!isset($_SESSION['ativa'])) {
        header("location: login.php");
        exit();
    }
}

function inserirUsuarios($connect){
    
    if ((isset($_POST['cadastrar']) && !empty($_POST['email']) &&
        !empty( $_POST['senha'])) ) {
        $erros = array();
        $email = filter_input(INPUT_POST, "email",
            FILTER_VALIDATE_EMAIL);
        $nome = mysqli_real_escape_string($connect, $_POST["nome"]);
        $senha = sha1($_POST["senha"]);
        if ($_POST['senha'] != $_POST['repetesenha']) {
            $erros[] = "Senhas não conferem!";
        }
        $queryEmail = "SELECT email FROM usuarios WHERE email = '$
            email' ";
        $buscaEmail = mysqli_query($connect, $queryEmail);
        $verifica = mysqli_num_rows($buscaEmail);

        if (!empty($verifica)) {
            $erros[] = "E-mail já cadastrado!";
        }

        $imagem = !empty($_FILES['imagem']['name']) ? $_FILES['imagem']
        ['name'] : "";
        if (!empty($imagem)) {
            $caminho = "imagens/uploads/";
            $retornoUpload = uploadImage($caminho);
            if (is_array($retornoUpload)) {
                foreach( $retornoUpload as $erro) {
                    echo $erro;
                }
                $imagem = "";
            }else{
                $imagem = $retornoUpload;
            }
        }

        if (empty($erros)) {
            if(empty($imagem)) {
                $query = "INSERT INTO usuarios (nome, email, senha, data_cadastro)
                VALUES ('$nome', '$email', '$senha', NOW()) ";
            }else{
                $query = "INSERT INTO usuarios (imagem, nome, email, senha, data_cadastro)
                VALUES ('$imagem', '$nome', '$email', '$senha', NOW()) ";
            }
            
            $executar = mysqli_query($connect, $query);
            if ($executar) {
                echo "Usuário inserido com sucesso!";
            } else {
                echo "Erro ao inserir usuário...";
            }

        } else {
            foreach ($erros as $erro) {
                echo "<p>$erro</p>";
            }
        }
    }
}

function deletar($connect, $tabela, $id) {
    if (!empty($id)) {
        $query = "DELETE FROM $tabela WHERE id =". (int) $id;
        $execute = mysqli_query($connect, $query);
        if ($execute) {
            echo "Dado deletado com sucesso!";
        }else{
            echo "Erro ao deletar!";
        }
    }
}

function updateUser($connect) {
    if (isset($_POST['atualizar']) && !empty($_POST['email'])) {
        $erros = array();
        
        
        //Valida e sanitiza os inputs
        $id = filter_input(INPUT_POST,'id', FILTER_VALIDATE_INT);
        $email = filter_input(INPUT_POST,'email', FILTER_VALIDATE_EMAIL);
        $nome = mysqli_real_escape_string($connect, $_POST['nome']);
        $data = mysqli_real_escape_string($connect, $_POST['data_cadastro']);
        $senha = "";


        //Valida os campos dos inputs
        if (empty($data)) {
            $erros[] = "Preencha a data de cadastro.";
        }
        if  (empty($email)) {
            $erros[] = "Preencha seu e-mail corretamente!";
        }
        if  (strlen($nome) < 3) {
            $erros[] = "Preencha seu nome corretamente.";
        }
        if  (!empty($_POST['senha'])) {
           if($_POST['senha'] == $_POST['repetesenha']) {
                $senha = sha1($_POST['senha']);
           } else {
                $erros[] = "Senhas não conferem!";
            }
        }

        //Checa se o e-mail já é utilizado por outro usuário
        $queryEmailAtual = "SELECT email FROM usuarios WHERE id = $id";
        $buscaEmailAtual = mysqli_query($connect, $queryEmailAtual);
        $returnEmail = mysqli_fetch_assoc($buscaEmailAtual);

        $queryEmail = "SELECT email FROM usuarios WHERE email = '$email' AND email <> '". $returnEmail['email'] . "'";
        $buscaEmail = mysqli_query($connect, $queryEmail);
        $verifica = mysqli_num_rows($buscaEmail);

        if($verifica > 0) {
            $erros[] = "E-mail já cadastrado.";
        }


        $imagem = !empty($_FILES['imagem']['name']) ? $_FILES['imagem']['name'] : "";
        $retornoUpload = "";
        if (!empty($imagem)) {
            $caminho = "imagens/uploads/";
            $retornoUpload = uploadImage($caminho);
            if (is_array($retornoUpload)) {
                foreach( $retornoUpload as $erro) {
                    $erros[] = $erro;
                }
                $imagem = "";
            } else {
                $imagem = $retornoUpload;
            }
        }


        //Atualiza o usuário se não houver erros.
        if (empty($erros)) {
            $query = "UPDATE usuarios SET nome = '$nome', email = '$email', data_cadastro = '$data'";
            
            if (!empty($imagem)) {
                $query .= ", imagem = '$imagem'";
            }
            
            if (!empty($senha)) {
                $query .= ", senha = '$senha'";
            }
            
            $query .= " WHERE id = $id;";
            echo $query;
            echo $imagem;
            $executar = mysqli_query($connect, $query);
            if ($executar) {
                if (is_array($retornoUpload)) {
                    echo "Usuário atualizado com sucesso. Porém, a imagem não pode ser atualizada.";
                }else{
                    echo "Usuário atualizado com sucesso.";
                }
            } else {
                echo "Erro ao atualizar usuário...";
            }
        } else {
            foreach ($erros as $erro) {
                echo "<p>$erro</p>";
            }
        }
    }
}

function uploadImage($caminho) {
    if(!empty($_FILES['imagem']['name'])) {

        $nomeImagem = $_FILES['imagem']['name'];
        $tipo = $_FILES['imagem']['type'];
        $nomeTemporario = $_FILES['imagem']['tmp_name'];
        $tamanho = $_FILES['imagem']['size'];
        $erros = array();
        
        $tamanhoMaximo = 1024 * 1024 * 5; //5MB
        if ($tamanho > $tamanhoMaximo) {
            $erros[] = "Seu arquivo execedo o tamanho máximo permitido.<br>";
        }

        $arquivosPermitidos = ["png", "jpg", "jpeg"];
        $extensao = pathinfo($nomeImagem, PATHINFO_EXTENSION);
        if (!in_array($extensao, $arquivosPermitidos)) {
            $erros[] = "Arquivo não permitido.<br>";
        }

        $typesPermitidos = ["image/png", "image/jpg", "image/jpeg"];
        if (!in_array($tipo, $typesPermitidos)) {
            $erros[] = "Tipo de arquivo não permitido.<br>";
        }

        if(!empty($erros)) {
            foreach ($erros as $erro) {
                echo $erro;
            }
        }else{
            $hoje = date("d-m-Y_h-i");
            $caminho = "img/uploads/";
            if (!is_dir($caminho)) {
                mkdir($caminho, 0755, true);
            }

            $nomeImagemUnico = $hoje . '-' . $nomeImagem;

            if (move_uploaded_file($nomeTemporario, $caminho . $nomeImagemUnico)) {
                return $nomeImagemUnico;
            } else {
                return FALSE;
            }
        }
    } else {
        echo "Nenhum arquivo foi selecionado.<br>";
    }
}