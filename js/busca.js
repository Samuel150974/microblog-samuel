'use strict';

const formularioBusca = document.getElementById('form-busca');
const campoBusca = document.getElementById('campo-busca');
const containerResultados = document.getElementById('resultados');

if (formularioBusca && campoBusca && containerResultados) {
    let temporizadorBusca;
    let requisicaoAtual;

    const ocultarResultados = () => {
        containerResultados.hidden = true;
        containerResultados.innerHTML = '';
        containerResultados.setAttribute('aria-busy', 'false');
    };

    const pesquisar = async (termo) => {
        if (requisicaoAtual) {
            requisicaoAtual.abort();
        }

        const controleRequisicao = new AbortController();
        requisicaoAtual = controleRequisicao;
        containerResultados.hidden = false;
        containerResultados.setAttribute('aria-busy', 'true');
        containerResultados.innerHTML = '<p class="text-muted mb-0">Pesquisando...</p>';

        try {
            const resposta = await fetch('resultados.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: new URLSearchParams({ busca: termo }),
                signal: controleRequisicao.signal
            });

            if (!resposta.ok) {
                throw new Error(`A pesquisa falhou com o status ${resposta.status}.`);
            }

            const html = await resposta.text();
            if (requisicaoAtual !== controleRequisicao || controleRequisicao.signal.aborted) {
                return;
            }
            containerResultados.innerHTML = html;
        } catch (erro) {
            if (erro.name === 'AbortError') {
                return;
            }

            if (requisicaoAtual !== controleRequisicao) {
                return;
            }

            console.error('Não foi possível carregar os resultados da pesquisa.', erro);
            containerResultados.innerHTML = '<p class="text-danger mb-0">Não foi possível realizar a pesquisa.</p>';
        } finally {
            if (requisicaoAtual === controleRequisicao) {
                containerResultados.setAttribute('aria-busy', 'false');
            }
        }
    };

    formularioBusca.addEventListener('submit', (evento) => {
        evento.preventDefault();
    });

    campoBusca.addEventListener('input', () => {
        window.clearTimeout(temporizadorBusca);
        const termo = campoBusca.value.trim();

        if (termo === '') {
            if (requisicaoAtual) {
                requisicaoAtual.abort();
                requisicaoAtual = null;
            }
            ocultarResultados();
            return;
        }

        if (requisicaoAtual) {
            requisicaoAtual.abort();
            requisicaoAtual = null;
        }
        containerResultados.hidden = false;
        containerResultados.setAttribute('aria-busy', 'true');
        containerResultados.innerHTML = '<p class="text-muted mb-0">Pesquisando...</p>';

        temporizadorBusca = window.setTimeout(() => pesquisar(termo), 250);
    });

    document.addEventListener('click', (evento) => {
        if (!formularioBusca.contains(evento.target) && !containerResultados.contains(evento.target)) {
            containerResultados.hidden = true;
        }
    });
}

