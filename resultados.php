<?php
use Microblog\Helpers\Utils;
use Microblog\Services\NoticiaServico;

require_once __DIR__ . '/vendor/autoload.php';

$termo = Utils::normalizarEntrada($_POST['busca'] ?? '');

if (!is_string($termo) || $termo === '') {
    http_response_code(400);
    ?>
    <p class="text-muted mb-0">Digite um termo para pesquisar.</p>
    <?php
    exit;
}

if (mb_strlen($termo) > 100) {
    http_response_code(400);
    ?>
    <p class="text-muted mb-0">A pesquisa deve ter no máximo 100 caracteres.</p>
    <?php
    exit;
}

try {
    $resultados = (new NoticiaServico())->pesquisarPublicamente($termo);
} catch (Throwable $e) {
    Utils::registrarLog($e);
    http_response_code(500);
    ?>
    <p class="text-danger mb-0">Não foi possível realizar a pesquisa.</p>
    <?php
    exit;
}
?>

<h2 class="fs-5">
    Resultados: <span class="badge rounded-pill text-bg-success"><?= count($resultados) ?></span>
</h2>
<div class="list-group">
    <?php if ($resultados === []): ?>
        <p class="text-muted mb-0">Nenhuma notícia encontrada.</p>
    <?php else: ?>
        <?php foreach ($resultados as $noticia): ?>
            <a class="list-group-item list-group-item-action" href="noticia.php?id=<?= Utils::escaparHtml($noticia['id']) ?>">
                <?= Utils::escaparHtml($noticia['titulo']) ?>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
