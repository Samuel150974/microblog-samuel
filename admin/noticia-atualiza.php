<?php

use Microblog\Auth\ControleDeAcesso;
use Microblog\Enums\TipoUsuario;
use Microblog\Helpers\Utils;
use Microblog\Services\CategoriaServico;
use Microblog\Services\NoticiaServico;

require_once __DIR__ . '/../vendor/autoload.php';

ControleDeAcesso::exigirLogin();

$id = Utils::normalizarEntrada($_GET['id'] ?? null, 'inteiro');
Utils::verificarId($id);

$mensagemErro = '';
$noticia = null;
$listaDeCategorias = [];
$tipoUsuario = null;

try {
    $idUsuario = Utils::normalizarEntrada($_SESSION['id'] ?? null, 'inteiro');
    if ($idUsuario <= 0) {
        throw new UnexpectedValueException('ID de usuário inválido na sessão.');
    }

    $tipoUsuario = TipoUsuario::from($_SESSION['tipo'] ?? '');
    $noticiaServico = new NoticiaServico();
    $noticia = $noticiaServico->buscarPorId($id, $idUsuario, $tipoUsuario);

    if ($noticia === null) {
        $mensagemErro = 'Notícia não encontrada ou você não tem permissão para acessá-la.';
    } else {
        $categoriaServico = new CategoriaServico();
        $listaDeCategorias = $categoriaServico->listarTodos();
    }
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = 'Não foi possível carregar os dados da notícia.';
}

$exibirAutor = $tipoUsuario === TipoUsuario::ADMIN;
$nomeImagem = $noticia === null ? '' : basename((string) $noticia['imagem']);

require_once __DIR__ . '/../includes/cabecalho-admin.php';
?>

<div class="row">
    <article class="col-12 bg-white rounded shadow my-1 py-4">
        <h2 class="text-center">Atualizar dados da notícia</h2>

        <?php if ($mensagemErro !== ''): ?>
            <div class="alert alert-danger text-center">
                <?= Utils::escaparHtml($mensagemErro) ?>
            </div>
        <?php endif; ?>

        <?php if ($noticia !== null && $mensagemErro === ''): ?>
            <form class="mx-auto w-75" action="" method="post" id="form-atualizar" name="form-atualizar" enctype="multipart/form-data">
                <?php if ($exibirAutor): ?>
                    <p class="text-muted">
                        Autor: <?= Utils::escaparHtml($noticia['autor'] ?? 'Autor não identificado') ?>
                    </p>
                <?php endif; ?>

                <p class="text-muted">
                    Data: <?= Utils::escaparHtml(Utils::formataData($noticia['data'])) ?>
                </p>

                <div class="mb-3">
                    <label class="form-label" for="categoria">Categoria:</label>
                    <select class="form-select" name="categoria" id="categoria">
                        <?php if ($noticia['categoria_id'] === null): ?>
                            <option value="" selected>Sem categoria</option>
                        <?php endif; ?>

                        <?php foreach ($listaDeCategorias as $itemCategoria): ?>
                            <option
                                value="<?= Utils::escaparHtml($itemCategoria['id']) ?>"
                                <?= (int) $itemCategoria['id'] === (int) ($noticia['categoria_id'] ?? 0) ? 'selected' : '' ?>>
                                <?= Utils::escaparHtml($itemCategoria['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="titulo">Título:</label>
                    <input
                        value="<?= Utils::escaparHtml($noticia['titulo']) ?>"
                        class="form-control"
                        type="text"
                        id="titulo"
                        name="titulo">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="texto">Texto:</label>
                    <textarea class="form-control" name="texto" id="texto" cols="50" rows="6"><?= Utils::escaparHtml($noticia['texto']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="resumo">Resumo (máximo de 300 caracteres):</label>
                    <span id="maximo" class="badge bg-danger">0</span>
                    <textarea class="form-control" name="resumo" id="resumo" cols="50" rows="2" maxlength="300"><?= Utils::escaparHtml($noticia['resumo']) ?></textarea>
                </div>

                <div class="mb-3">
                    <div class="row">
                        <div class="col-5">
                            <label for="imagem-existente" class="form-label">Imagem atual:</label>
                            <input
                                value="<?= Utils::escaparHtml($nomeImagem) ?>"
                                class="form-control"
                                type="text"
                                id="imagem-existente"
                                readonly
                                disabled>
                        </div>
                        <div class="col-7 text-end">
                            <img
                                src="../images/<?= Utils::escaparHtml($nomeImagem) ?>"
                                alt="Imagem da notícia"
                                class="img-fluid rounded">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="imagem" class="form-label">Caso queira mudar, selecione outra imagem:</label>
                    <input class="form-control" type="file" id="imagem" name="imagem" accept="image/jpeg, image/gif, image/png, image/webp">
                </div>

                <div class="mb-3">
                    <p>Deixar a notícia em destaque?</p>
                    <input
                        type="radio"
                        class="btn-check"
                        name="destaque"
                        id="option1"
                        autocomplete="off"
                        value="nao"
                        <?= $noticia['destaque'] === 'nao' ? 'checked' : '' ?>>
                    <label class="btn btn-outline-danger" for="option1">Não</label>

                    <input
                        type="radio"
                        class="btn-check"
                        name="destaque"
                        id="option2"
                        autocomplete="off"
                        value="sim"
                        <?= $noticia['destaque'] === 'sim' ? 'checked' : '' ?>>
                    <label class="btn btn-outline-success" for="option2">Sim</label>
                </div>

                <button class="btn btn-primary" type="button" disabled>
                    <i class="bi bi-arrow-clockwise"></i> Atualizar
                </button>
            </form>

            <script src="js/contador.js"></script>
        <?php endif; ?>
    </article>
</div>

<?php require_once __DIR__ . '/../includes/rodape-admin.php'; ?>
