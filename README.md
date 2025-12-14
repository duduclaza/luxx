# TOTEM LUXX - Sistema de Autoatendimento

Sistema SaaS para totens de autoatendimento em bares, restaurantes e eventos.

## 📋 Funcionalidades

- 🍳 **Cozinha**: Kanban de pedidos (Novos → Preparando → Pronto)
- 🍺 **Bar**: Kanban com detalhes e chamada de clientes
- 📋 **Cardápio**: Menu digital com carrinho de compras
- 🎫 **Bilheteria**: Venda de ingressos
- 📺 **Painel**: Exibição de clientes para retirada
- ⚙️ **Admin**: Configurações e relatórios

## 🚀 Instalação

### Requisitos
- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10.3+
- Apache com mod_rewrite

### Passo a Passo

1. **Faça upload dos arquivos para seu servidor**
   
   Suba todos os arquivos para a pasta `public_html` ou configure o DocumentRoot para a pasta `public/`

2. **Configure o .env**
   
   ```bash
   cp .env.example .env
   ```
   
   Edite o arquivo `.env` com suas configurações:
   ```
   DB_HOST=localhost
   DB_DATABASE=nome_do_banco
   DB_USERNAME=seu_usuario
   DB_PASSWORD=sua_senha
   ```

3. **Crie o banco de dados**
   
   ```bash
   php database/migrations.php
   ```
   
   Ou acesse via navegador: `seusite.com/database/migrations.php`
   
   **⚠️ IMPORTANTE**: Delete o arquivo migrations.php após executar!

4. **Configure permissões**
   
   ```bash
   chmod 755 public/
   chmod 644 .env
   ```

5. **Acesse o sistema**
   
   - URL: `https://blue-moose-166502.hostingersite.com`
   - Email: `du.claza@gmail.com`
   - Senha: `admin123`
   - PIN Admin: `1234`

   **⚠️ MUDE A SENHA E PIN IMEDIATAMENTE!**

## 📁 Estrutura de Pastas

```
TOTEM LUXX/
├── app/
│   ├── api/           # APIs REST
│   ├── pages/         # Páginas PHP
│   │   ├── admin/     # Painel administrativo
│   │   └── modulos/   # Módulos do totem
│   ├── views/         # Templates (futuro)
│   └── helpers.php    # Funções utilitárias
├── config/
│   └── database.php   # Configuração do banco
├── database/
│   └── migrations.php # Criação das tabelas
├── public/            # DocumentRoot
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   ├── img/
│   │   └── audio/
│   ├── .htaccess
│   └── index.php      # Entry point
├── .env.example
└── README.md
```

## 🔧 Configuração no Hostinger

1. Acesse o **hPanel** do Hostinger
2. Vá em **Gerenciador de Arquivos**
3. Suba os arquivos para `public_html`
4. Configure o `.env` com os dados do banco MySQL
5. Execute as migrations

### Banco de Dados MySQL
- Host: `localhost` (ou o fornecido pelo Hostinger)
- Crie um banco pelo hPanel em **Bancos de Dados MySQL**
- Use as credenciais no `.env`

## 💳 Integração Mercado Pago

Cada cliente configura seu próprio token no painel Admin:
1. Acesse `/admin/configuracoes`
2. Insira o **Access Token** do Mercado Pago
3. Insira a **Public Key**

Para obter as credenciais:
1. Acesse [developers.mercadopago.com](https://developers.mercadopago.com)
2. Crie uma aplicação
3. Copie as credenciais de produção

## 📱 Uso nos Totens

1. Faça login no sistema
2. Selecione o módulo desejado (Cozinha, Bar, etc.)
3. O módulo ficará em tela cheia
4. Para sair, insira o PIN do administrador

## 🔒 Segurança

- Senhas criptografadas com bcrypt
- Sessões seguras
- Proteção contra SQL Injection (PDO prepared statements)
- XSS Prevention (sanitização de inputs)
- CSRF tokens (em desenvolvimento)

## 📞 Suporte

Desenvolvido por Clayton (du.claza@gmail.com)

---

© 2024 TOTEM LUXX. Todos os direitos reservados.
