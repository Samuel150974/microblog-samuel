# Guia de desenvolvimento do Microblog

Este arquivo reúne o contexto necessário para trabalhar no projeto sem depender do histórico de conversas. Antes de alterar o código, confira os arquivos atuais: telas e fluxos podem estar em diferentes estágios de implementação.

## Visão geral

Microblog é um portal didático de notícias em PHP, com páginas públicas e um painel administrativo. O backend usa orientação a objetos, MySQL/MariaDB via PDO e autoload PSR-4 do Composer. A interface usa HTML, CSS, JavaScript e Bootstrap carregado por CDN.

## Ambiente e execução

- Requer PHP moderno. O projeto usa enums e tipos como `mixed`; o README especifica PHP 8.5+.
- Extensões PHP utilizadas: `pdo_mysql`, `mbstring` e `fileinfo`.
- Execute com Apache/PHP (por exemplo, XAMPP) e MySQL/MariaDB; acesse o projeto pelo servidor web local.
- A configuração da conexão está em `src/Database/ConexaoBD.php`. Ajuste host, banco e credenciais para o ambiente local; não registre credenciais pessoais ou de produção no repositório.
- O banco ativo esperado pelo código é `microblog_2026`. Para importação, confira `_materiais/microblog_2026_completo.sql`.
- `_materiais/modelagem-fisica.md` ainda cita `microblog_2025`; trate esse documento como referência histórica e confirme o SQL e o código ativos antes de alterar o esquema.
- Composer configura `Microblog\` para `src/` em `composer.json`. Após mudar namespaces ou adicionar classes, rode `composer dump-autoload`. Não edite arquivos gerados em `vendor/`.
- Não há scripts de teste ou dependências adicionais declarados em `composer.json`. Para validar sintaxe PHP, rode `php -l caminho/do/arquivo.php`; valide manualmente os fluxos afetados no ambiente web e banco local.

## Estrutura

- `src/Auth/`: autenticação por sessão e autorização.
- `src/Database/`: conexão PDO compartilhada.
- `src/Enums/`: tipos de usuário e destaque.
- `src/Helpers/`: normalização, escape, validação, upload, senha, datas e log.
- `src/Models/`: entidades `Usuario`, `Categoria` e `Noticia`.
- `src/Services/`: consultas e operações de banco por entidade.
- `admin/`: páginas e ações do painel; `admin/js/` contém scripts de confirmação e contador.
- Raiz: páginas públicas, incluindo início, notícia, notícias por categoria, busca e login.
- `includes/`: cabeçalhos, rodapés e blocos compartilhados.
- `images/`, `css/`, `js/`: assets públicos; `logs/erros.log`: log de exceções.
- `_materiais/`: dumps SQL e material didático.

## Modelo de dados

O dump `microblog_2026_completo.sql` define:

- `usuarios`: `id`, `nome`, `email`, `senha` (hash) e `tipo` (`admin` ou `editor`).
- `categorias`: `id` e `nome`.
- `noticias`: `id`, `data`, `titulo`, `texto`, `resumo`, `imagem`, `destaque` (`sim` ou `nao`), `usuario_id` e `categoria_id`. As referências a usuário e categoria podem ser nulas.

Compare sempre as colunas, tipos e constraints do dump com os modelos, serviços e formulários antes de mudar consultas ou validações. Não assuma que um dump inicial e o dump completo têm o mesmo conteúdo.

## Regras do domínio e fluxos

- `TipoUsuario` tem `ADMIN` e `EDITOR`; `Destaque` tem `SIM` e `NAO`. Armazene e compare seus valores de string usando `->value`; use `from()` ou `tryFrom()` para conversões apropriadas.
- Ambos os tipos de usuário autenticados podem gerenciar suas próprias notícias. Admins podem listar todas as notícias e ver o autor; editores devem ver somente as próprias, sem a coluna de autor. A consulta está em `NoticiaServico::listarTodos()` e é chamada por `admin/noticias.php`.
- O painel deve exigir login. Ações administrativas devem exigir permissão de admin quando aplicável. `ControleDeAcesso` mantém `id`, `nome` e `tipo` na sessão.
- Páginas PHP administrativas carregam `vendor/autoload.php` antes de instanciar ou chamar classes do namespace `Microblog`.
- `admin/noticia-atualiza.php` está atualmente com formulário e valores de exemplo; verifique sua implementação real antes de tratá-la como fluxo funcional.

## Convenções para mudanças

- Namespace e caminho seguem PSR-4: por exemplo, `Microblog\Services\NoticiaServico` fica em `src/Services/NoticiaServico.php`.
- Use nomes em português conforme o padrão existente, classes em PascalCase e métodos/propriedades/variáveis em camelCase.
- Modelos têm propriedades tipadas privadas e getters. Serviços encapsulam SQL e recebem modelos nas operações de escrita quando esse padrão fizer sentido.
- Use PDO e parâmetros preparados para valores. Admins e editores devem ter escopos de acesso distintos; não confie apenas em esconder links ou colunas na interface.
- Preserve os padrões de validação, mensagens em português, escape de saída com `Utils::escaparHtml()` e feedback dos formulários.
- Em falhas de banco, registre a exceção completa com `Utils::registrarLog()` e mostre ao usuário uma mensagem genérica; erros esperados de validação podem ser mostrados diretamente.
- Faça mudanças pequenas e relacionadas ao pedido. Atualize documentação diretamente afetada e valide os arquivos e fluxos modificados.
- Preserve configurações e alterações locais que não façam parte da tarefa; não reverta mudanças preexistentes.

