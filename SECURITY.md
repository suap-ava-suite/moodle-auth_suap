# Política de Segurança

## Versões com Suporte

Apenas a versão mais recente do plugin recebe correções de segurança.

| Versão | Com suporte       |
|--------|-------------------|
| 4.5.x  | ✅ Sim (atual)    |
| < 4.5  | ❌ Não            |

## Relatando uma Vulnerabilidade

Se você descobriu uma vulnerabilidade de segurança neste plugin, **não abra uma issue pública**. Siga as etapas abaixo:

1. **Envie um e-mail** para a equipe de desenvolvimento do IFRN descrevendo o problema:
   - Assunto: `[SECURITY] moodle-auth_suap – <resumo breve>`
   - Descrição detalhada da vulnerabilidade
   - Passos para reprodução
   - Impacto potencial
   - Versão do plugin e do Moodle afetadas
   - (Opcional) Sugestão de correção ou prova de conceito

2. **Aguarde a confirmação.** Você receberá um retorno em até **5 dias úteis** confirmando o recebimento e indicando os próximos passos.

3. **Processo de correção.** Após a confirmação, trabalharemos em conjunto para validar, corrigir e divulgar a vulnerabilidade de forma responsável. O prazo-alvo para disponibilizar uma correção é de **30 dias** após a confirmação.

4. **Divulgação coordenada.** A vulnerabilidade será divulgada publicamente somente após a publicação de uma versão corrigida, salvo acordo diferente com o pesquisador.

## Escopo

Este projeto é um plugin de **autenticação** para o Moodle, que integra o fluxo OAuth2 do SUAP. As vulnerabilidades de interesse incluem, mas não se limitam a:

- Falhas no fluxo de autenticação OAuth2 (`authenticate.php`) que permitam personificação ou bypass de login
- Vazamento ou manipulação do `access_token`/`verify_token_url` usados na troca de credenciais com o SUAP
- Geração ou validação indevida de token de webservice via `dispatch.php`
- Escalada de privilégios ou burla da capacidade `auth/suap:updatepicture`
- Exposição indevida de dados pessoais sincronizados do SUAP (nome, foto, campos de perfil customizados)
- Injeção SQL ou execução remota de código no contexto do plugin
- Cross-Site Scripting (XSS) ou Cross-Site Request Forgery (CSRF) introduzidos pelo plugin

Vulnerabilidades no **Moodle core**, no `auth_oauth2` ou em outros plugins devem ser reportadas diretamente ao [programa de segurança do Moodle](https://moodle.org/security/).

## Boas Práticas para Quem Usa o Plugin

- Mantenha o plugin sempre atualizado para a versão mais recente.
- Mantenha o Moodle e o `auth_oauth2` atualizados, aplicando todos os patches de segurança oficiais.
- Configure o `verify_token_url` e demais segredos OAuth2 fora do código-fonte, nunca os exponha em logs ou repositórios.
- Conceda a capacidade `auth/suap:updatepicture` apenas aos papéis estritamente necessários.
- Garanta que exista pelo menos um usuário administrador com autenticação alternativa antes de restringir o login a este método.
- Restrinja o acesso ao endpoint `dispatch.php` a integrações confiáveis sempre que possível.

## Créditos

Agradecemos a todos que contribuem para a segurança deste projeto de forma responsável.

---

© 2026 Kelson da Costa Medeiros – Licença [GNU GPL v3 ou superior](http://www.gnu.org/copyleft/gpl.html)
