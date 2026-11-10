<?php

namespace Microblog\Auth;

/* Sessão no PHP
Funcionalidade usada para controlar o acesso a determinada
Páginas e recursos de um site/aplicação.

Exemplos: áreas administrativas, painel de controle, área de cliente/aluno

Nessas áreas, o acesso depende de alguma forma de autenticação, como login e senha, biometria, por dois fatores etc.
 */


final class ControleDeAcesso
{  
    // Impedir a criação de objeto/instância
    private function __construct(){}
 
    // Inicia a sessão se ela ainda não estiver ativa
    private static function iniciarSessao(): void

    {


        // Se o status da sessão não for ativa (ou se sessão está intativa)
      if(session_status() !== PHP_SESSION_ACTIVE) {

    //   Então, iniciamos a sessão 
        session_start();
      }
    }

    private static function encerrarSessão(): void
    {
        $_SESSION = [];  // Limpar os dados do usuário armazenados na sessão
        session_destroy(); // destrói/ remove quaisquer dados da sessão no navegador
    }

    public static function exigirLogin(): void
    {
        self::iniciarSessao();

        //  Se não existir na sessão uma variável chamada id,
        // Significa que o usuário não está logado


        if(!isset($_SESSION['id'])){
            // encerrar a sessão (ainda programar)
            self::encerrarSessão();

            header("Location: ../login.php?acesso_proibido");
              exit;
        }
    }
}