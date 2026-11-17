Resumo do histórico da branch
Branch analisada: 09_aula-1611

Período dos commits: 03/11/2026 a 16/11/2026

Principais pontos trabalhados

Estrutura do projeto e autoload
O projeto foi organizado como um portal didático em PHP para notícias, com áreas pública e administrativa, acesso a MySQL/MariaDB por PDO, modelos, enums, helpers e materiais SQL. O Composer passou a mapear o namespace `Microblog\` para `src/`, permitindo o autoload PSR-4 das classes.

Gestão de usuários
Foi criado `UsuarioServico` para listar, inserir, buscar, atualizar e excluir usuários com consultas preparadas. As páginas administrativas passaram a usar dados reais, validar os formulários, preservar os valores após erros e registrar falhas técnicas. Senhas são armazenadas com hash; na edição, deixar o campo em branco mantém a senha atual.

Autenticação e autorização
Foi implementada autenticação por sessão, com login e logout, restrição de páginas e operações administrativas e proteção contra a exclusão da própria conta. Admins têm acesso às operações administrativas; editores não recebem permissões de administrador.

Gestão de categorias
Foi implementada a listagem de categorias e os fluxos administrativos de cadastro, edição e exclusão, com validação, consultas PDO e tratamento de erros.

Cadastro e listagem de notícias
`NoticiaServico` passou a usar a conexão compartilhada e ganhou persistência para notícias. O formulário de inserção carrega categorias, valida título, texto, resumo, categoria e destaque, permite o envio de imagens e preserva os valores após erros. A data e o ID são gerados pelo banco. A listagem é escopada por perfil: admins veem todas as notícias e seus autores; editores veem somente as próprias, sem a coluna de autor.

Atualização e exclusão de notícias
O fluxo de edição carrega a notícia pelo ID e pelo escopo de acesso do usuário. As alterações são enviadas por POST e validadas; se nenhuma imagem nova for enviada, a imagem atual é mantida. A categoria pode ficar sem valor, de acordo com a nulabilidade de `categoria_id` no banco e no modelo `Noticia`.

A exclusão parte do ID da notícia na listagem, que usa o modal compartilhado de confirmação, e redireciona à lista após a operação. O serviço restringe atualização e exclusão de editores às próprias notícias; admins podem operar sobre todas.

Estado resultante
A branch contém os fluxos de usuários, autenticação, categorias e cadastro/listagem de notícias, além do carregamento da notícia para edição. Na árvore de trabalho atual, as alterações também completam a persistência da edição e a exclusão de notícias. Os arquivos modificados ainda não commitados devem ser considerados ao revisar ou continuar o trabalho.
