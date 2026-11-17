<?php

use Microblog\Auth\ControleDeAcesso;
use Microblog\Enums\TipoUsuario;
use Microblog\Helpers\Utils;
use Microblog\Services\NoticiaServico;

require_once __DIR__ . '/../vendor/autoload.php';

ControleDeAcesso::exigirLogin();

$mensagemErro = '';
$listaDeNoticias = [];
$tipoUsuario = null;

try {
    $idUsuario = Utils::normalizarEntrada($_SESSION['id'] ?? null, 'inteiro');
    if ($idUsuario <= 0) {
        throw new UnexpectedValueException('ID de usuário inválido na sessão.');
    }

    $tipoUsuario = TipoUsuario::from($_SESSION['tipo'] ?? '');
    $noticiaServico = new NoticiaServico();
    $listaDeNoticias = $noticiaServico->listarTodos($idUsuario, $tipoUsuario);
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = 'Não foi possível carregar a lista de notícias';
}

$exibirAutor = $tipoUsuario === TipoUsuario::ADMIN;

require_once __DIR__ . '/../includes/cabecalho-admin.php';
?>

<div class="row">
    <article class="col-12 bg-white rounded shadow my-1 py-4">
        <h2 class="text-center">
            Notícias <span class="badge bg-dark"><?= count($listaDeNoticias) ?></span>
        </h2>

        <?php if ($mensagemErro !== ''): ?>
            <div class="alert alert-danger text-center">
                <?= Utils::escaparHtml($mensagemErro) ?>
            </div>
        <?php endif; ?>

        <p class="text-center mt-5">
            <a class="btn btn-primary" href="noticia-insere.php">
                <i class="bi bi-plus-circle"></i> Inserir nova notícia
            </a>
        </p>

        <?php if ($mensagemErro === ''): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Título</th>
                            <th>Data</th>
                            <?php if ($exibirAutor): ?>
                                <th>Autor</th>
                            <?php endif; ?>
                            <th class="text-center">Destaque</th>
                            <th class="text-center" colspan="2">Operações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($listaDeNoticias === []): ?>
                            <tr>
                                <td colspan="<?= $exibirAutor ? 6 : 5 ?>" class="text-center">
                                    Nenhuma notícia cadastrada.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaDeNoticias as $noticia): ?>
                                <tr>
                                    <td><?= Utils::escaparHtml($noticia['titulo']) ?></td>
                                    <td><?= Utils::escaparHtml(Utils::formataData($noticia['data'])) ?></td>
                                    <?php if ($exibirAutor): ?>
                                        <td><?= Utils::escaparHtml($noticia['autor'] ?? 'Autor não identificado') ?></td>
                                    <?php endif; ?>
                                    <td class="text-center">
                                        <?= Utils::escaparHtml($noticia['destaque'] === 'sim' ? 'Sim' : 'Não') ?>
                                    </td>
                                    <td class="text-center">
                                        <a class="btn btn-warning btn-sm" href="noticia-atualiza.php?id=<?= Utils::escaparHtml($noticia['id']) ?>">
                                            <i class="bi bi-pencil"></i> Atualizar
                                        </a>
                                    </td>
                                    <td>
                                        <a class="btn btn-danger excluir btn-sm" href="noticia-exclui.php?id=<?= Utils::escaparHtml($noticia['id']) ?>">
                                            <i class="bi bi-trash"></i> Excluir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>
</div>

<script src="js/confirm.js"></script>
<?php require_once __DIR__ . '/../includes/rodape-admin.php'; ?>
