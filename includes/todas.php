<?php
use Microblog\Helpers\Utils;
use Microblog\Services\NoticiaServico;

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $todasAsNoticias = (new NoticiaServico())->listarPublicas();
    $erroAoListarNoticias = false;
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $todasAsNoticias = [];
    $erroAoListarNoticias = true;
}
?>

<hr class="my-5 w-50 mx-auto">

<div class="row my-1">
    <div class="col-12 px-md-1">
        <div class="list-group">
            <h2 class="fs-6 text-center text-muted">Todas as notícias</h2>

            <?php if ($erroAoListarNoticias): ?>
                <p class="alert alert-danger text-center">Não foi possível carregar as notícias.</p>
            <?php elseif ($todasAsNoticias === []): ?>
                <p class="text-center">Nenhuma notícia cadastrada.</p>
            <?php else: ?>
                <?php foreach ($todasAsNoticias as $noticia): ?>
                    <a href="noticia.php?id=<?= Utils::escaparHtml($noticia['id']) ?>" class="list-group-item list-group-item-action">
                        <h3 class="fs-6">
                            <time datetime="<?= Utils::escaparHtml($noticia['data']) ?>">
                                <?= Utils::escaparHtml(Utils::formataData($noticia['data'])) ?>
                            </time>
                            - <?= Utils::escaparHtml($noticia['titulo']) ?>
                        </h3>
                        <p><?= Utils::escaparHtml($noticia['resumo']) ?></p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
