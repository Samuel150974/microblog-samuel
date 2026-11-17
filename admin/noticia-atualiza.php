<?php

use Microblog\Auth\ControleDeAcesso;
use Microblog\Enums\Destaque;
use Microblog\Enums\TipoUsuario;
use Microblog\Helpers\Utils;
use Microblog\Helpers\Validacoes;
use Microblog\Models\Noticia;
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
$titulo = '';
$texto = '';
$resumo = '';
$destaque = '';
$categoriaId = null;
$formularioDisponivel = false;

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
        $titulo = $noticia['titulo'];
        $texto = $noticia['texto'];
        $resumo = $noticia['resumo'];
        $destaque = $noticia['destaque'];
        $categoriaId = $noticia['categoria_id'] === null ? null : (int) $noticia['categoria_id'];

        $categoriaServico = new CategoriaServico();
        $listaDeCategorias = $categoriaServico->listarTodos();
        $formularioDisponivel = true;
    }
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = 'Não foi possível carregar os dados da notícia.';
}

if ($noticia !== null && $mensagemErro === '' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $titulo = Utils::normalizarEntrada($_POST['titulo'] ?? '');
        $texto = Utils::normalizarEntrada($_POST['texto'] ?? '');
        $resumo = Utils::normalizarEntrada($_POST['resumo'] ?? '');
        $destaque = Utils::normalizarEntrada($_POST['destaque'] ?? '');
        $categoriaInformada = Utils::normalizarEntrada($_POST['categoria'] ?? '');

        Validacoes::validarTitulo($titulo);
        Validacoes::validarTexto($texto);
        Validacoes::validarResumo($resumo);
        Validacoes::validarDestaque($destaque);

        $categoriaId = null;
        if ($categoriaInformada !== '') {
            $categoriaInformada = Utils::normalizarEntrada($categoriaInformada, 'inteiro');
            Validacoes::validarCategoria($categoriaInformada);
            $categoriaExiste = false;
            foreach ($listaDeCategorias as $itemCategoria) {
                if ((int) $itemCategoria['id'] === $categoriaInformada) {
                    $categoriaExiste = true;
                    break;
                }
            }

            if (!$categoriaExiste) {
                throw new InvalidArgumentException('Selecione uma categoria válida.');
            }

            $categoriaId = $categoriaInformada;
        }

        $nomeDaImagem = $noticia['imagem'];
        $arquivoImagem = $_FILES['imagem'] ?? null;
        if (is_array($arquivoImagem) && ($arquivoImagem['error'] ?? null) !== UPLOAD_ERR_NO_FILE) {
            $nomeDaImagem = Utils::upload($arquivoImagem);
        }

        $noticiaAtualizada = new Noticia(
            $titulo,
            $texto,
            $resumo,
            $nomeDaImagem,
            Destaque::from($destaque),
            $idUsuario,
            $categoriaId,
            $id
        );
        $noticiaServico->atualizar($noticiaAtualizada, $tipoUsuario);

        header('Location: noticias.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $mensagemErro = $e->getMessage();
    } catch (Throwable $e) {
        Utils::registrarLog($e);
        $mensagemErro = 'Não foi possível atualizar a notícia. Tente novamente.';
    }
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

        <?php if ($noticia !== null && $formularioDisponivel): ?>
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
                        <option value="" <?= $categoriaId === null ? 'selected' : '' ?>>Sem categoria</option>

                        <?php foreach ($listaDeCategorias as $itemCategoria): ?>
                            <option
                                value="<?= Utils::escaparHtml($itemCategoria['id']) ?>"
                                <?= (int) $itemCategoria['id'] === $categoriaId ? 'selected' : '' ?>>
                                <?= Utils::escaparHtml($itemCategoria['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="titulo">Título:</label>
                    <input
                        value="<?= Utils::escaparHtml($titulo) ?>"
                        class="form-control"
                        type="text"
                        id="titulo"
                        name="titulo">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="texto">Texto:</label>
                    <textarea class="form-control" name="texto" id="texto" cols="50" rows="6"><?= Utils::escaparHtml($texto) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="resumo">Resumo (máximo de 300 caracteres):</label>
                    <span id="maximo" class="badge bg-danger">0</span>
                    <textarea class="form-control" name="resumo" id="resumo" cols="50" rows="2" maxlength="300"><?= Utils::escaparHtml($resumo) ?></textarea>
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
                        <?= $destaque === 'nao' ? 'checked' : '' ?>>
                    <label class="btn btn-outline-danger" for="option1">Não</label>

                    <input
                        type="radio"
                        class="btn-check"
                        name="destaque"
                        id="option2"
                        autocomplete="off"
                        value="sim"
                        <?= $destaque === 'sim' ? 'checked' : '' ?>>
                    <label class="btn btn-outline-success" for="option2">Sim</label>
                </div>

                <button class="btn btn-primary" type="submit" name="atualizar">
                    <i class="bi bi-arrow-clockwise"></i> Atualizar
                </button>
            </form>

            <script src="js/contador.js"></script>
        <?php endif; ?>
    </article>
</div>

<?php require_once __DIR__ . '/../includes/rodape-admin.php'; ?>
