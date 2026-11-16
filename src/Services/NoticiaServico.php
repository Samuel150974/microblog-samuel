<?php
namespace Microblog\Services;

use Exception;
use Microblog\Database\ConexaoBD;
use Microblog\Enums\TipoUsuario;
use Microblog\Helpers\Utils;
use Microblog\Models\Noticia;
use PDO;
use PDOException;

final class NoticiaServico
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = ConexaoBD::getConexao();
    }

    public function listarTodos(int $usuarioId, TipoUsuario $tipoUsuario): array
    {
        try {
            if ($tipoUsuario === TipoUsuario::ADMIN) {
                $sql = "SELECT n.id, n.data, n.titulo, n.destaque, u.nome AS autor
                        FROM noticias n
                        LEFT JOIN usuarios u ON n.usuario_id = u.id
                        ORDER BY n.data DESC";
                $consulta = $this->conexao->query($sql);
            } else {
                $sql = "SELECT n.id, n.data, n.titulo, n.destaque
                        FROM noticias n
                        WHERE n.usuario_id = :usuario_id
                        ORDER BY n.data DESC";
                $consulta = $this->conexao->prepare($sql);
                $consulta->bindValue(":usuario_id", $usuarioId, PDO::PARAM_INT);
                $consulta->execute();
            }

            return $consulta->fetchAll();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao listar notícias");
        }
    }

    public function buscarPorId(int $id, int $usuarioId, TipoUsuario $tipoUsuario): ?array
    {
        try {
            if ($tipoUsuario === TipoUsuario::ADMIN) {
                $sql = "SELECT n.id, n.data, n.titulo, n.texto, n.resumo, n.imagem,
                               n.destaque, n.usuario_id, n.categoria_id, u.nome AS autor
                        FROM noticias n
                        LEFT JOIN usuarios u ON n.usuario_id = u.id
                        WHERE n.id = :id";
                $consulta = $this->conexao->prepare($sql);
                $consulta->bindValue(":id", $id, PDO::PARAM_INT);
            } else {
                $sql = "SELECT id, data, titulo, texto, resumo, imagem, destaque,
                               usuario_id, categoria_id
                        FROM noticias
                        WHERE id = :id AND usuario_id = :usuario_id";
                $consulta = $this->conexao->prepare($sql);
                $consulta->bindValue(":id", $id, PDO::PARAM_INT);
                $consulta->bindValue(":usuario_id", $usuarioId, PDO::PARAM_INT);
            }

            $consulta->execute();
            return $consulta->fetch() ?: null;
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao buscar notícia por ID");
        }
    }

    public function inserir(Noticia $noticia): void
    {
        $sql = "INSERT INTO noticias (titulo, texto, resumo, imagem, destaque, usuario_id, categoria_id) VALUES(:titulo, :texto, :resumo, :imagem, :destaque, :usuario_id, :categoria_id)";
           
        try {
            $consulta = $this->conexao->prepare($sql);

            $consulta->bindValue(":titulo", $noticia->getTitulo());
            $consulta->bindValue(":texto", $noticia->getTexto());
            $consulta->bindValue(":resumo", $noticia->getResumo());
            $consulta->bindValue(":imagem", $noticia->getImagem());
            $consulta->bindValue(":destaque", $noticia->getDestaque()->value);
            $consulta->bindValue(":usuario_id", $noticia->getUsuarioId());
            $consulta->bindValue(":categoria_id", $noticia->getCategoriaId());

            $consulta->execute();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception(("Erro ao inserir notícia"));
        }

    }

    public function atualizar(Noticia $noticia, TipoUsuario $tipoUsuario): void
    {
        if ($noticia->getId() === null) {
            throw new \InvalidArgumentException("A notícia precisa ter um ID para ser atualizada.");
        }

        $sql = "UPDATE noticias
                SET titulo = :titulo, texto = :texto, resumo = :resumo,
                    imagem = :imagem, destaque = :destaque, categoria_id = :categoria_id
                WHERE id = :id";

        if ($tipoUsuario === TipoUsuario::EDITOR) {
            $sql .= " AND usuario_id = :usuario_id";
        }

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":titulo", $noticia->getTitulo());
            $consulta->bindValue(":texto", $noticia->getTexto());
            $consulta->bindValue(":resumo", $noticia->getResumo());
            $consulta->bindValue(":imagem", $noticia->getImagem());
            $consulta->bindValue(":destaque", $noticia->getDestaque()->value);
            $consulta->bindValue(
                ":categoria_id",
                $noticia->getCategoriaId(),
                $noticia->getCategoriaId() === null ? PDO::PARAM_NULL : PDO::PARAM_INT
            );
            $consulta->bindValue(":id", $noticia->getId(), PDO::PARAM_INT);

            if ($tipoUsuario === TipoUsuario::EDITOR) {
                $consulta->bindValue(":usuario_id", $noticia->getUsuarioId(), PDO::PARAM_INT);
            }

            $consulta->execute();

            if ($consulta->rowCount() === 0 && $this->buscarPorId($noticia->getId(), $noticia->getUsuarioId(), $tipoUsuario) === null) {
                throw new Exception("Notícia não encontrada ou sem permissão para atualizá-la.");
            }
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao atualizar notícia");
        }
    }

    public function excluir(int $id, int $usuarioId, TipoUsuario $tipoUsuario): void
    {
        $sql = "DELETE FROM noticias WHERE id = :id";

        if ($tipoUsuario === TipoUsuario::EDITOR) {
            $sql .= " AND usuario_id = :usuario_id";
        }

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":id", $id, PDO::PARAM_INT);

            if ($tipoUsuario === TipoUsuario::EDITOR) {
                $consulta->bindValue(":usuario_id", $usuarioId, PDO::PARAM_INT);
            }

            $consulta->execute();

            if ($consulta->rowCount() !== 1) {
                throw new Exception("Notícia não encontrada ou sem permissão para excluí-la.");
            }
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao excluir notícia");
        }
    }
    
}
































?>