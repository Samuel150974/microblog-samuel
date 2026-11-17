<?php
use Microblog\Helpers\Utils;
use Microblog\Services\CategoriaServico;
use Microblog\Services\NoticiaServico;

require_once __DIR__ . '/vendor/autoload.php';

Utils::verificarId($_GET['id'] ?? null);
$idCategoria = (int) $_GET['id'];
$categoriaAtual = null;
$noticiasDaCategoria = [];
$erroAoCarregarCategoria = false;

try {
    $categoriaAtual = (new CategoriaServico())->buscarPorId($idCategoria);
    if ($categoriaAtual !== null) {
        $noticiasDaCategoria = (new NoticiaServico())->listarPorCategoria($idCategoria);
    } else {
        http_response_code(404);
    }
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $erroAoCarregarCategoria = true;
}

require_once __DIR__ . '/includes/cabecalho.php';
?>

<div class="row my-1 mx-md-n1">
    <article class="col-12">
        <?php if ($erroAoCarregarCategoria): ?>
            <p class="alert alert-danger text-center">Não foi possível carregar as notícias desta categoria.</p>
        <?php elseif ($categoriaAtual === null): ?>
            <p class="alert alert-warning text-center">Categoria não encontrada.</p>
        <?php else: ?>
            <h2 class="text-center">
                Notícias sobre <span class="badge bg-primary"><?= Utils::escaparHtml($categoriaAtual['nome']) ?></span>
            </h2>

            <div class="row my-1">
                <div class="col-12 px-md-1">
                    <div class="list-group">
                        <?php if ($noticiasDaCategoria === []): ?>
                            <p class="text-center">Nenhuma notícia cadastrada nesta categoria.</p>
                        <?php else: ?>
                            <?php foreach ($noticiasDaCategoria as $noticia): ?>
                                <a href="noticia.php?id=<?= Utils::escaparHtml($noticia['id']) ?>" class="list-group-item list-group-item-action">
                                    <h3 class="fs-6"><?= Utils::escaparHtml($noticia['titulo']) ?></h3>
                                    <p>
                                        <time datetime="<?= Utils::escaparHtml($noticia['data']) ?>">
                                            <?= Utils::escaparHtml(date('d/m/Y', strtotime($noticia['data']))) ?>
                                        </time>
                                        - <?= Utils::escaparHtml($noticia['autor'] ?? 'Equipe Microblog') ?>
                                    </p>
                                    <p><?= Utils::escaparHtml($noticia['resumo']) ?></p>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </article>
</div>

<?php
require_once __DIR__ . '/includes/todas.php';
require_once __DIR__ . '/includes/rodape.php';
?>
