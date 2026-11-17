<?php

use Microblog\Auth\ControleDeAcesso;
use Microblog\Enums\TipoUsuario;
use Microblog\Helpers\Utils;
use Microblog\Services\NoticiaServico;

require_once __DIR__ . '/../vendor/autoload.php';

ControleDeAcesso::exigirLogin();

$id = Utils::normalizarEntrada($_GET['id'] ?? null, 'inteiro');
Utils::verificarId($id);

try {
    $idUsuario = Utils::normalizarEntrada($_SESSION['id'] ?? null, 'inteiro');
    if ($idUsuario <= 0) {
        throw new UnexpectedValueException('ID de usuário inválido na sessão.');
    }

    $tipoUsuario = TipoUsuario::from($_SESSION['tipo'] ?? '');
    $noticiaServico = new NoticiaServico();
    $noticiaServico->excluir($id, $idUsuario, $tipoUsuario);

    header('Location: noticias.php');
    exit;
} catch (Throwable $e) {
    Utils::registrarLog($e);
    Utils::alertaErro('Não foi possível excluir a notícia. Tente novamente.');
}
?>
