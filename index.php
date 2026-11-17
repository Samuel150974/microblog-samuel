<?php
use Microblog\Helpers\Utils;
use Microblog\Services\NoticiaServico;

require_once __DIR__ . '/vendor/autoload.php';

$mensagemErro = '';
$destaques = [];

try {
    $destaques = (new NoticiaServico())->listarDestaques();
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = 'Não foi possível carregar as notícias em destaque.';
}

require_once __DIR__ . '/includes/cabecalho.php';
?>

<div class="row my-1 mx-md-n1">
    <?php if ($mensagemErro !== ''): ?>
        <div class="col-12">
            <p class="alert alert-danger text-center"><?= Utils::escaparHtml($mensagemErro) ?></p>
        </div>
    <?php elseif ($destaques === []): ?>
        <div class="col-12">
            <p class="text-center">Não há notícias em destaque.</p>
        </div>
    <?php else: ?>
        <?php foreach ($destaques as $noticia): ?>
            <div class="col-md-6 my-1 px-md-1">
                <article class="card shadow-sm h-100">
                    <a href="noticia.php?id=<?= Utils::escaparHtml($noticia['id']) ?>" class="card-link text-decoration-none">
                        <img
                            src="images/<?= Utils::escaparHtml($noticia['imagem']) ?>"
                            class="card-img-top"
                            alt="<?= Utils::escaparHtml($noticia['titulo']) ?>">
                        <div class="card-body">
                            <h2 class="fs-4 card-title"><?= Utils::escaparHtml($noticia['titulo']) ?></h2>
                            <p class="card-text"><?= Utils::escaparHtml($noticia['resumo']) ?></p>
                        </div>
                    </a>
                </article>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/todas.php';
require_once __DIR__ . '/includes/rodape.php';
?>