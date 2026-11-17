<?php
use Microblog\Helpers\Utils;
use Microblog\Services\NoticiaServico;

require_once __DIR__ . '/vendor/autoload.php';

Utils::verificarId($_GET['id'] ?? null);
$id = (int) $_GET['id'];
$mensagemErro = '';
$noticia = null;

try {
    $noticia = (new NoticiaServico())->buscarPublicaPorId($id);
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = 'Não foi possível carregar esta notícia.';
}

if ($noticia === null && $mensagemErro === '') {
    http_response_code(404);
}

require_once __DIR__ . '/includes/cabecalho.php';
?>

<div class="row my-1 mx-md-n1">
    <article class="col-12">
        <?php if ($mensagemErro !== ''): ?>
            <p class="alert alert-danger text-center"><?= Utils::escaparHtml($mensagemErro) ?></p>
        <?php elseif ($noticia === null): ?>
            <p class="alert alert-warning text-center">Notícia não encontrada.</p>
        <?php else: ?>
            <h2><?= Utils::escaparHtml($noticia['titulo']) ?></h2>
            <p class="font-weight-light">
                <time datetime="<?= Utils::escaparHtml($noticia['data']) ?>">
                    <?= Utils::escaparHtml(date('d/m/Y', strtotime($noticia['data']))) ?>
                </time>
                - <?= Utils::escaparHtml($noticia['autor'] ?? 'Equipe Microblog') ?>
            </p>
            <img
                src="images/<?= Utils::escaparHtml($noticia['imagem']) ?>"
                alt="<?= Utils::escaparHtml($noticia['titulo']) ?>"
                class="float-start pe-2 img-fluid">
            <div class="ajusta-texto"><?= nl2br(Utils::escaparHtml($noticia['texto'])) ?></div>
        <?php endif; ?>
    </article>
</div>

<?php
require_once __DIR__ . '/includes/todas.php';
require_once __DIR__ . '/includes/rodape.php';
?>
