# Resumo do histórico da branch

## Contexto

Este resumo considera os commits da branch `01_aula_0311''` que não estão em `main`, até o commit `82a32ad` (03/11/2026). Nesse intervalo foram identificados quatro commits, com foco na organização das classes PHP e na listagem de usuários no painel administrativo.

## Principais pontos trabalhados

### Autoload e organização do código

- Foi adicionada a configuração do Composer para autoload PSR-4, associando o namespace `Microblog\` à pasta `src/`.
- Os arquivos de modelos, enums e helpers passaram a declarar namespaces, com os imports necessários entre essas classes.
- A pasta `vendor/` foi incluída no `.gitignore`, evitando versionar as dependências e arquivos gerados pelo Composer.

### Serviço de usuários

- Foi criado `UsuarioServico`, responsável por obter a conexão com o banco de dados e listar usuários.
- O método `listarTodos()` consulta os campos `id`, `nome`, `email` e `tipo` na tabela `usuarios`, ordenando o resultado pelo nome.
- Falhas de consulta são registradas no log e convertidas em uma mensagem de erro amigável para a camada de apresentação.

### Painel administrativo

- A página de usuários deixou de exibir linhas estáticas de exemplo e passou a carregar os dados por meio do serviço.
- O contador do painel agora reflete a quantidade retornada pela consulta.
- A página apresenta uma mensagem quando ocorre erro ao carregar os usuários e escapa valores exibidos no HTML.
- As ações de atualizar e excluir continuam disponíveis para cada usuário listado.

## Commits considerados

1. `f101f9c` — Adicionar autoloading PSR-4 do Composer.
2. `0a5c5ab` — Adiciona serviço de usuário.
3. `d46b48a` — Adicionar listagem de usuário com tratamento de erros.
4. `82a32ad` — Listar usuários dinamicamente no painel de administração.
