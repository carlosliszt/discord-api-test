# Discord API Test

Este projeto é uma integração entre um servidor Minecraft e o Discord, permitindo a vinculação de contas de usuários via OAuth2. Utiliza PHP, Bootstrap, Predis (Redis), Guzzle e Dotenv.

## Funcionalidades
- Autenticação OAuth2 com Discord
- Vinculação de conta Minecraft com Discord
- Publicação de eventos em canal Redis
- Interface de sucesso estilizada com Bootstrap e estética inspirada no Discord

## Requisitos
- PHP >= 7.4
- Composer
- MySQL
- Redis
- Servidor web (Apache, Nginx, etc)

## Instalação
1. Clone o repositório:
   ```bash
   git clone https://github.com/seu-usuario/discord-api-test.git
   cd discord-api-test
   ```
2. Instale as dependências:
   ```bash
   composer install
   ```
3. Copie o arquivo de exemplo de variáveis de ambiente e configure:
   ```bash
   cp .env.example .env
   # Edite o arquivo .env com suas credenciais do Discord
   ```
4. Configure o banco de dados MySQL e o Redis conforme necessário.

## Configuração do Discord
- Crie uma aplicação em https://discord.com/developers/applications
- Adicione um OAuth2 Redirect URI (ex: `http://localhost:8000/`)
- Copie o Client ID e Client Secret para o arquivo `.env`

## Estrutura do Projeto
- `public/index.php`: Endpoint principal para autenticação e vinculação
- `public/success.php`: Página de sucesso após vinculação
- `public/success.html`: Página de sucesso estática (não utilizada se usar success.php)
- `src/`: Código-fonte adicional (se necessário)
- `vendor/`: Dependências do Composer

## Como Usar
1. Inicie o servidor web apontando para a pasta `public/`.
2. Acesse a URL de autenticação do Discord.
3. Após a autenticação, o usuário será redirecionado e a vinculação será processada.
4. Em caso de sucesso, uma página estilizada mostrará os dados do usuário Discord.

## Personalização
- O layout da página de sucesso pode ser alterado em `public/success.php`.
- As variáveis de ambiente são configuradas no `.env`.

## Licença
MIT

---

> Projeto para fins de estudo e integração entre Minecraft e Discord. Não utilize tokens ou credenciais sensíveis em produção sem as devidas proteções.

