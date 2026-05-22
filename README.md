# fell.it 🌐

[![Live on Render](https://img.shields.io/badge/Live_on-Render-46E3B7?style=for-the-badge&logo=render)](https://fell-it-app.onrender.com)

> **Rede social de sentimentos** — compartilhe o que você sente, conecte-se com pessoas e forme clãs com quem pensa igual a você.

🌍 **Acesse o projeto online:** [https://fell-it-app.onrender.com](https://fell-it-app.onrender.com)

---

## 📋 Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Stack Tecnológica](#stack-tecnológica)
- [Estrutura de Pastas](#estrutura-de-pastas)
- [Banco de Dados](#banco-de-dados)
- [API — Rotas e Endpoints](#api--rotas-e-endpoints)
- [Autenticação e Sessão](#autenticação-e-sessão)
- [Variáveis de Ambiente](#variáveis-de-ambiente)
- [Como Iniciar o Projeto](#como-iniciar-o-projeto)
- [Rotas do Frontend](#rotas-do-frontend)
- [Componentes Principais](#componentes-principais)

---

## Sobre o Projeto

**fell.it** é uma rede social focada em expressão emocional. Os usuários publicam *feelings* (sentimentos), interagem com curtidas e comentários, formam **clãs** (grupos temáticos), adicionam amigos e recebem notificações em tempo real.

O projeto é **fullstack**, com frontend SPA em Vue 3 + Vite e backend REST API em Node.js + Express + TypeScript, persistindo dados em SQLite via Prisma ORM.

---

## Stack Tecnológica

### 🖥️ Frontend

| Tecnologia | Versão | Função |
|---|---|---|
| **Vue 3** | ^3.5.34 | Framework reativo (Composition API + `<script setup>`) |
| **Vite** | ^8.0.12 | Build tool e dev server (porta 5173) |
| **Vue Router** | ^4.6.4 | Roteamento SPA com guards de autenticação |
| **Pinia** | ^3.0.4 | Gerenciamento de estado global |
| **Axios** | ^1.16.0 | Cliente HTTP para chamadas à API |
| **Bootstrap 5** | ^5.3.8 | Framework CSS para layout e componentes |
| **Bootstrap Icons** | ^1.13.1 | Biblioteca de ícones SVG |
| **CSS Customizado** | — | Glassmorphism, dark mode, animações premium |

### ⚙️ Backend

| Tecnologia | Versão | Função |
|---|---|---|
| **Node.js** | ≥18.x | Runtime JavaScript/TypeScript |
| **Express** | ^5.2.1 | Framework web REST API (porta 3000) |
| **TypeScript** | ^6.0.3 | Tipagem estática no backend |
| **ts-node-dev** | ^2.0.0 | Hot-reload em desenvolvimento para TypeScript |
| **Prisma ORM** | ^5.22.0 | Acesso ao banco de dados com type-safety |
| **SQLite** | — | Banco de dados relacional embutido (arquivo `.db`) |
| **JWT (jsonwebtoken)** | ^9.0.3 | Autenticação stateless por token |
| **bcryptjs** | ^3.0.3 | Hash de senhas |
| **Multer** | ^2.1.1 | Upload de arquivos (avatar e wallpaper) |
| **CORS** | ^2.8.6 | Controle de acesso cross-origin |
| **dotenv** | ^17.4.2 | Variáveis de ambiente |

---

## Estrutura de Pastas

```
Projeto_fell.it/
│
├── kurta.db                    # Banco de dados SQLite (produção)
├── launcher.bat                # Script para iniciar o backend e servir o frontend compilado
├── start_app.vbs               # Script silencioso para chamar o launcher.bat
├── installer/                  # Arquivos e scripts para geração do instalador final
├── vite.config.js              # Configuração do Vite (proxy para /api → porta 3000)
├── package.json                # Dependências do frontend e scripts de build globais
│
├── src/                        # Frontend Vue 3
│   ├── main.js                 # Entry point: cria app, registra Pinia e Router
│   ├── App.vue                 # Componente raiz com NavBar e ToastProvider
│   ├── style.css               # Estilos globais (glassmorphism, dark mode, variáveis CSS)
│   │
│   ├── router/
│   │   └── index.js            # Rotas SPA com guards de autenticação (fellit_token)
│   │
│   ├── stores/
│   │   └── notificationStore.js # Store Pinia para notificações (polling 30s)
│   │
│   ├── components/
│   │   ├── NavBar.vue           # Barra de navegação global (z-index: 10000)
│   │   ├── PostCard.vue         # Card de sentimento (likes, comentários, share)
│   │   ├── CommentSection.vue   # Seção de comentários expansível
│   │   ├── CommentItem.vue      # Item individual de comentário (com respostas)
│   │   ├── FriendList.vue       # Lista de amigos do perfil
│   │   ├── ClanList.vue         # Lista de clãs do usuário
│   │   ├── UserSearch.vue       # Componente de busca de usuários
│   │   ├── ShareModal.vue       # Modal para compartilhar sentimentos
│   │   ├── ToastProvider.vue    # Sistema de alertas (posição: bottom-right)
│   │   └── LoginStatus.vue      # Indicador de status de sessão
│   │
│   └── views/
│       ├── LoginView.vue        # Tela de login
│       ├── RegisterView.vue     # Tela de cadastro
│       ├── HomeView.vue         # Feed global + Trending Top 3
│       ├── ProfileView.vue      # Perfil do usuário logado
│       ├── VisitorProfileView.vue # Perfil de outro usuário (add/remove friend)
│       ├── SearchView.vue       # Busca de usuários
│       ├── ClansView.vue        # Lista de todos os clãs
│       ├── CreateClanView.vue   # Criar novo clã
│       ├── ClanDetailView.vue   # Feed e membros de um clã específico
│       └── ManageClanView.vue   # Gerenciar clã (admin)
│
└── server/                     # Backend Express + TypeScript
    ├── .env                    # Variáveis de ambiente (DATABASE_URL, JWT_SECRET, PORT)
    ├── package.json            # Dependências do backend
    ├── tsconfig.json           # Configuração TypeScript
    │
    ├── prisma/
    │   └── schema.prisma       # Schema do banco de dados (modelos e relações)
    │
    └── src/
        ├── index.ts            # Entry point do servidor Express
        │
        ├── lib/
        │   └── prisma.ts       # Instância singleton do PrismaClient
        │
        ├── middlewares/
        │   └── auth.ts         # Middleware de verificação JWT
        │
        ├── routes/
        │   ├── authRoutes.ts       # /api/auth
        │   ├── feelingRoutes.ts    # /api/feelings
        │   ├── clanRoutes.ts       # /api/clans
        │   ├── profileRoutes.ts    # /api/profile
        │   ├── searchRoutes.ts     # /api/search
        │   ├── commentRoutes.ts    # /api/comments
        │   └── socialRoutes.ts     # /api/social
        │
        └── controllers/
            ├── authController.ts       # Login e registro
            ├── feelingController.ts    # CRUD de sentimentos, feed, trending
            ├── clanController.ts       # CRUD de clãs e membros
            ├── profileController.ts    # Perfil, amigos, atualização
            ├── friendshipController.ts # Solicitações de amizade
            ├── commentController.ts    # Comentários e respostas
            └── searchController.ts     # Busca de usuários
```

---

## Banco de Dados

**Arquivo:** `kurta.db` (SQLite) — localizado na raiz do projeto.

**ORM:** Prisma v5 — Schema em `server/prisma/schema.prisma`.

### Tabelas e Esquema

#### `user`
| Campo | Tipo | Descrição |
|---|---|---|
| `user_id` | INT PK | ID único do usuário |
| `first_name` | TEXT | Primeiro nome |
| `last_name` | TEXT | Sobrenome |
| `email` | TEXT | E-mail (único) |
| `phone` | BIGINT | Telefone |
| `birthdate` | DATETIME | Data de nascimento |
| `profile_pic` | TEXT | Caminho do avatar |
| `wallpaper_pic` | TEXT | Caminho do wallpaper |
| `created_at` | DATETIME | Data de criação |

#### `login`
| Campo | Tipo | Descrição |
|---|---|---|
| `login_id` | INT PK | ID único |
| `user` | INT FK → user | ID do usuário |
| `password` | TEXT | Senha (hash bcrypt) |
| `level_acess` | TEXT | Nível de acesso (`user`, `admin`) |
| `created_at` | DATETIME | Data de criação |

#### `feeling`
| Campo | Tipo | Descrição |
|---|---|---|
| `feeling_id` | INT PK | ID único do sentimento |
| `feeling` | TEXT | Texto do sentimento |
| `user` | INT FK → user | Autor |
| `visibility` | TEXT | `public` / `private` |
| `cla_id` | INT FK → clan | Clã associado (NULL = feed global) |
| `created_at` | DATETIME | Data de publicação |

#### `coments`
| Campo | Tipo | Descrição |
|---|---|---|
| `coment_id` | INT PK | ID único |
| `coment` | TEXT | Texto do comentário |
| `user` | INT FK → user | Autor |
| `feeling` | INT FK → feeling | Sentimento comentado |
| `parent_id` | INT FK → coments | Resposta a outro comentário (NULL = raiz) |
| `created_at` | DATETIME | Data |

#### `likes`
| Campo | Tipo | Descrição |
|---|---|---|
| `like_id` | INT PK | ID único |
| `user_id` | INT FK → user | Usuário que curtiu |
| `feeling_id` | INT FK → feeling | Sentimento curtido |
| `created_at` | DATETIME | Data |
> Chave única: `(user_id, feeling_id)` — um like por usuário por sentimento.

#### `clan`
| Campo | Tipo | Descrição |
|---|---|---|
| `clan_id` | INT PK | ID único |
| `name_clan` | TEXT | Nome do clã |
| `description` | TEXT | Descrição |
| `clan_pic` | TEXT | Imagem do clã |
| `visibility` | TEXT | `public` / `private` |
| `created_at` | DATETIME | Data de criação |

#### `clan_member`
| Campo | Tipo | Descrição |
|---|---|---|
| `member_id` | INT PK | ID único |
| `clan_id` | INT FK → clan | Clã |
| `user_id` | INT FK → user | Membro |
| `role` | TEXT | `admin` / `aldeao` |
| `joined_at` | DATETIME | Data de entrada |
> Chave única: `(clan_id, user_id)`.

#### `friendship`
| Campo | Tipo | Descrição |
|---|---|---|
| `friendship_id` | INT PK | ID único |
| `sender_id` | INT FK → user | Quem enviou a solicitação |
| `receiver_id` | INT FK → user | Quem recebeu |
| `status` | TEXT | `pending` / `accepted` / `rejected` |
| `created_at` | DATETIME | Data |
> Chave única: `(sender_id, receiver_id)`.

#### `notification`
| Campo | Tipo | Descrição |
|---|---|---|
| `notif_id` | INT PK | ID único |
| `user_id` | INT FK → user | Destinatário |
| `sender_id` | INT FK → user | Remetente |
| `notif_type` | TEXT | `friend_request` / `clan_join` / etc. |
| `reference_id` | INT | ID do objeto referenciado (ex: `friendship_id`) |
| `is_read` | BOOLEAN | Lida ou não |
| `created_at` | DATETIME | Data |

#### `share`
| Campo | Tipo | Descrição |
|---|---|---|
| `share_id` | INT PK | ID único |
| `share` | TEXT | Texto adicional ao compartilhar |
| `user` | INT FK → user | Quem compartilhou |
| `feeling` | INT FK → feeling | Sentimento compartilhado |
| `clan` | INT FK → clan | Clã de destino (0 = feed global) |
| `created_at` | DATETIME | Data |

---

## API — Rotas e Endpoints

Base URL: `http://localhost:3000`

### 🔐 Auth — `/api/auth`

| Método | Endpoint | Descrição |
|---|---|---|
| POST | `/api/auth/login` | Login — retorna `token` JWT e dados do usuário |
| POST | `/api/auth/register` | Cadastro de novo usuário |

### 💬 Feelings — `/api/feelings`

| Método | Endpoint | Descrição |
|---|---|---|
| GET | `/api/feelings/global?visitorId=` | Feed global (sentimentos públicos, sem clã) |
| GET | `/api/feelings/trending` | Top 3 sentimentos mais curtidos |
| GET | `/api/feelings/user/:id?visitorId=` | Sentimentos de um usuário específico |
| GET | `/api/feelings/clan/:id?visitorId=` | Sentimentos de um clã |
| POST | `/api/feelings/create` | Criar novo sentimento |
| POST | `/api/feelings/like` | Curtir / descurtir sentimento |

### 👤 Profile — `/api/profile`

| Método | Endpoint | Descrição |
|---|---|---|
| GET | `/api/profile?target_id=&my_id=` | Dados do perfil + status de amizade + clãs |
| POST | `/api/profile/update` | Atualizar perfil (multipart: avatar, wallpaper) |
| GET | `/api/profile/friends?user_id=` | Lista de amigos aceitos |
| POST | `/api/profile/remove-friend` | Remover amigo |

### 🏰 Clãs — `/api/clans`

| Método | Endpoint | Descrição |
|---|---|---|
| GET | `/api/clans` | Listar todos os clãs |
| GET | `/api/clans/:id` | Detalhes de um clã + membros |
| POST | `/api/clans/create` | Criar novo clã |
| POST | `/api/clans/join` | Entrar em um clã |
| POST | `/api/clans/leave` | Sair de um clã |
| POST | `/api/clans/update` | Atualizar dados do clã (admin) |
| POST | `/api/clans/kick` | Remover membro (admin) |

### 🤝 Social — `/api/social`

| Método | Endpoint | Descrição |
|---|---|---|
| POST | `/api/social/add-friend` | Enviar solicitação de amizade |
| POST | `/api/social/accept-friend` | Aceitar solicitação |
| POST | `/api/social/reject-friend` | Rejeitar solicitação |
| GET | `/api/social/notifications?user_id=` | Listar notificações do usuário |
| POST | `/api/social/notifications/read` | Marcar notificação como lida |

### 💬 Comentários — `/api/comments`

| Método | Endpoint | Descrição |
|---|---|---|
| GET | `/api/comments/:feeling_id` | Buscar comentários de um sentimento |
| POST | `/api/comments/create` | Criar comentário ou resposta |
| DELETE | `/api/comments/:id` | Deletar comentário |

### 🔍 Busca — `/api/search`

| Método | Endpoint | Descrição |
|---|---|---|
| GET | `/api/search?q=` | Buscar usuários por nome |

---

## Autenticação e Sessão

- **JWT** — token gerado no login, armazenado em `localStorage` com chave `fellit_token`.
- **Dados do usuário** — armazenados em `localStorage` com chave `fellit_user` (objeto com `id`, `first_name`, `last_name`, `profile_pic`).
- **Guard de rotas** — o Vue Router verifica `fellit_token` antes de cada navegação. Rotas protegidas redirecionam para `/login` se não autenticado.
- **Axios Interceptor** — injeta automaticamente o header `Authorization: Bearer <token>` em todas as requisições.

---

## Variáveis de Ambiente

---

## Como Iniciar o Projeto

### Pré-requisitos

- **Node.js** ≥ 18.x
- **npm** ≥ 9.x

### 1. Instalar dependências

```bash
# Frontend (raiz do projeto)
npm install

# Backend
cd server
npm install
```

### 2. Configurar o ambiente

```bash
# Crie o arquivo de variáveis de ambiente
cp server/.env.example server/.env
# Edite server/.env com os valores corretos
```

### 3. Gerar o Prisma Client

```bash
cd server
npx prisma generate
```

### 4. Iniciar o projeto

#### Modo Produção (Recomendado)

O backend Express serve os arquivos estáticos do frontend compilado (`dist/`). Para preparar e iniciar o ambiente de produção:

```bash
# Na raiz do projeto, instale dependências, faça o build e gere o Prisma:
npm run build:full
```

```bat
# Execute o script launcher.bat para subir o servidor e abrir no navegador:
launcher.bat
```
> Alternativamente, você pode usar `start_app.vbs` para iniciar sem manter uma janela de console do Windows aberta. A aplicação será aberta automaticamente em: **http://localhost:3000**

#### Modo Desenvolvimento (Hot-reload)

**Terminal 1 — Frontend:**
```bash
# Na raiz do projeto
npm run dev
# Disponível em: http://localhost:5173
```

**Terminal 2 — Backend:**
```bash
cd server
npm run dev
# Disponível em: http://localhost:3000 (Apenas API)
```

---

## Rotas do Frontend

| Caminho | View | Auth | Descrição |
|---|---|---|---|
| `/` | — | — | Redireciona para `/login` |
| `/login` | LoginView | ❌ | Tela de login |
| `/register` | RegisterView | ❌ | Cadastro de usuário |
| `/home` | HomeView | ✅ | Feed global + Trending |
| `/profile` | ProfileView | ✅ | Perfil do usuário logado |
| `/user/:id` | VisitorProfileView | ✅ | Perfil de outro usuário |
| `/search` | SearchView | ✅ | Buscar usuários |
| `/clans` | ClansView | ✅ | Lista de clãs |
| `/clan/create` | CreateClanView | ✅ | Criar clã |
| `/clan/:id` | ClanDetailView | ✅ | Ver clã + feed |
| `/clan/:id/manage` | ManageClanView | ✅ | Gerenciar clã (admin) |

---

## Componentes Principais

| Componente | Descrição |
|---|---|
| `NavBar.vue` | Barra de navegação global. z-index: 10000. Dropdown de notificações com polling a cada 30s. |
| `PostCard.vue` | Exibe um sentimento com avatar, nome, texto, curtidas, comentários e botão de share. |
| `CommentSection.vue` | Seção colapsável de comentários de um PostCard. |
| `CommentItem.vue` | Comentário individual com suporte a respostas aninhadas. |
| `ToastProvider.vue` | Sistema de alertas fixos no canto inferior direito. Uso: `window.$toast.add('msg', 'success'|'error'|'info')`. |
| `FriendList.vue` | Lista de amigos do perfil com avatares e navegação. |
| `ClanList.vue` | Grade de clãs com imagem, nome e link de acesso. |
| `UserSearch.vue` | Input de busca em tempo real de usuários. |
| `ShareModal.vue` | Modal para compartilhar um sentimento em um clã. |

---

## Proxy de Desenvolvimento

O Vite está configurado em `vite.config.js` para redirecionar as chamadas de API automaticamente:

```js
server: {
  proxy: {
    '/api': { target: 'http://localhost:3000', changeOrigin: true },
    '/assets': { target: 'http://localhost:3000', changeOrigin: true },
    '/app': { target: 'http://localhost:8000', changeOrigin: true }
  }
}
```

> Isso significa que `axios.get('/api/feelings/global')` no frontend é redirecionado para `http://localhost:3000/api/feelings/global` em desenvolvimento sem precisar configurar CORS.

---

## Comandos Úteis

```bash
# Gerar Prisma Client após mudanças no schema
cd server && npx prisma generate

# Visualizar o banco de dados no Prisma Studio
cd server && npx prisma studio

# Inspecionar o schema atual do banco
cd server && npx prisma db pull

# Rodar script de debug do banco
cd server && npx ts-node src/debug_db.ts

# Build do frontend para produção
npm run build

# Preview do build de produção
npm run preview
```

---

*Projeto desenvolvido com ❤️ — fell.it © 2026*
