<?php 

use Microblog\Helpers\Utils;
use Microblog\Services\UsuarioServico;
use Microblog\Auth\ControleDeAcesso;
require_once __DIR__ . '/../vendor/autoload.php';
ControleDeAcesso::exigirAdmin();

$id = Utils::normalizarEntrada($_GET['id'] ?? null, 'inteiro');
Utils::verificarId($id);

try{
    $usuario = new UsuarioServico();
    $usuario->excluir($id);
    header("location:usuarios.php");
    exit;
} catch (Throwable $e) {
    Utils::registrarLog($e);
    Utils::alertaErro("Não foi possível excluir o usuário. Tente novamente");
}






















require_once __DIR__ . '/../includes/cabecalho-admin.php'; 










?>


