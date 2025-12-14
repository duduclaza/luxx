# TOTEM LUXX - Sistema de Autoatendimento

Sistema SaaS para totems de autoatendimento em bares, restaurantes e eventos.

## 🚀 Stack Tecnológico

- **Backend**: Node.js + Express
- **Frontend**: HTML5, CSS3, Tailwind CSS
- **Banco de Dados**: MariaDB (Hostinger Remoto)
- **Deploy**: Vercel

## 📋 Funcionalidades

- 🍳 **Cozinha**: Kanban de pedidos (Novos → Preparando → Pronto)
- 🍺 **Bar**: Kanban com detalhes e chamada de clientes
- 📋 **Cardápio**: Menu digital com carrinho de compras
- 🎫 **Bilheteria**: Venda de ingressos
- 📺 **Painel**: Exibição de clientes para retirada
- ⚙️ **Admin**: Configurações e relatórios

## 🛠️ Instalação Local

### 1. Clone o repositório
```bash
git clone <seu-repo>
cd totem-luxx
```

### 2. Instale as dependências
```bash
npm install
```

### 3. Configure o ambiente
```bash
cp .env.example .env
```

Edite o `.env` com as credenciais do seu banco MariaDB no Hostinger:
```
DB_HOST=seu_host.hostinger.com
DB_PORT=3306
DB_DATABASE=u230868210_totemluxx
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

SESSION_SECRET=uma_chave_secreta_muito_longa_e_segura
```

### 4. Execute o servidor
```bash
npm run dev
```

Acesse: http://localhost:3000

## 🚀 Deploy no Vercel

### 1. Instale o Vercel CLI
```bash
npm i -g vercel
```

### 2. Faça login
```bash
vercel login
```

### 3. Deploy
```bash
vercel --prod
```

### 4. Configure as variáveis de ambiente no Vercel
No dashboard do Vercel, adicione as variáveis:
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `SESSION_SECRET`
- `NODE_ENV=production`

## 📁 Estrutura do Projeto

```
TOTEM LUXX/
├── config/
│   └── database.js      # Conexão MariaDB
├── routes/
│   ├── api/
│   │   ├── auth.js      # API de autenticação
│   │   ├── pedidos.js   # API de pedidos
│   │   ├── chamadas.js  # API de chamadas
│   │   └── produtos.js  # API de produtos
│   └── pages.js         # Rotas de páginas
├── public/
│   ├── login.html
│   ├── modulos.html
│   ├── cozinha.html
│   ├── bar.html
│   ├── cardapio.html
│   ├── bilheteria.html
│   ├── painel.html
│   └── assets/
├── server.js            # Entry point Express
├── vercel.json          # Configuração Vercel
├── package.json
└── .env.example
```

## 🔐 Credenciais Padrão

- **Email**: du.claza@gmail.com
- **Senha**: admin123
- **PIN Admin**: 1234

⚠️ **MUDE ESSAS CREDENCIAIS IMEDIATAMENTE!**

## 📞 Suporte

Desenvolvido por Clayton (du.claza@gmail.com)

---

© 2024 TOTEM LUXX. Todos os direitos reservados.
