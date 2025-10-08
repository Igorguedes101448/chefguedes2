# ChefGuedes - Site de ReceitasChefGuedes - Receitas (PHP + MySQL)



Um site moderno de partilha de receitas desenvolvido em PHP com MySQL, focado em usabilidade e design responsivo.Projeto demo ChefGuedes: site de partilha de receitas.



## CaracterísticasRequisitos:

- PHP 8+

- ✅ Autenticação de utilizadores (registo, login, logout)- MySQL / MariaDB

- ✅ CRUD completo de receitas com upload de imagens- Composer (opcional)

- ✅ Sistema de comentários e avaliações (5 estrelas)

- ✅ Painel administrativo completoInstalação rápida:

- ✅ Design responsivo (mobile-first)1. Copiar pasta para seu servidor web (ex: c:\\wamp64\\www\\chefguedes2)

- ✅ Segurança: CSRF protection, password hashing, prepared statements2. Criar base de dados e executar `migrations/create_tables.sql` e `migrations/seed.sql`

- ✅ Estado online/offline de utilizadores3. Editar `config/database.php` com credenciais DB

- ✅ Tema verde e branco moderno4. Aceder via `http://localhost/chefguedes2/`



## Requisitos do SistemaAdmin de teste inserido em seed.sql (email: admin@chefguedes.local, password: Admin123!)


- PHP 8.0 ou superior
- MySQL 5.7 ou MariaDB 10.2+
- Servidor web (Apache/Nginx)
- Extensões PHP: PDO, PDO_MySQL, GD (para imagens)

## Instalação

### 1. Clonar/Extrair o projeto
```bash
# Se usar git
git clone [repo-url] chefguedes2

# Ou extrair o zip para a pasta do servidor web
# Exemplo WAMP: c:\wamp64\www\chefguedes2
```

### 2. Configurar a Base de Dados

#### Criar a base de dados:
```sql
CREATE DATABASE chefguedes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Executar as migrações:
```bash
# No MySQL/phpMyAdmin, executar os ficheiros na seguinte ordem:
1. migrations/create_tables.sql
2. migrations/seed.sql (dados de exemplo)
```

### 3. Configurar Conexão da BD

Editar o ficheiro `config/database.php`:
```php
<?php
return [
    'host' => '127.0.0.1',     // Host da BD
    'dbname' => 'chefguedes',  // Nome da BD
    'user' => 'root',          // Utilizador da BD
    'pass' => '',              // Password da BD
    'charset' => 'utf8mb4',
];
```

### 4. Definir Permissões

Assegurar que as pastas têm permissões de escrita:
```bash
# Linux/Mac
chmod 755 uploads/
chmod 755 uploads/avatars/
chmod 755 uploads/recipes/

# Windows - definir permissões de escrita para IIS_IUSRS/EVERYONE
```

### 5. Aceder ao Site

Navegar para: `http://localhost/chefguedes2/`

## Contas de Teste

### Administrador
- **Email:** admin@chefguedes.local
- **Password:** Admin123!

### Utilizador Regular
- **Email:** user@example.com
- **Password:** User123!

## Estrutura do Projeto

```
chefguedes2/
├── public/                 # Ficheiros públicos
│   ├── index.php          # Homepage
│   ├── login.php          # Página de login
│   ├── register.php       # Página de registo
│   ├── recipe.php         # CRUD de receitas
│   ├── profile.php        # Perfil de utilizador
│   ├── admin.php          # Painel admin
│   ├── logout.php         # Logout
│   ├── assets/            # CSS, JS, imagens
│   └── api/               # Endpoints API
├── src/
│   ├── Controllers/       # Lógica de negócio
│   ├── Models/           # Modelos de dados
│   ├── Views/            # Templates HTML
│   └── Utils/            # Utilitários (CSRF, etc.)
├── config/               # Configurações
├── migrations/           # Scripts SQL
├── uploads/              # Ficheiros enviados
└── README.md
```

## Funcionalidades Principais

### Para Utilizadores
- Registar conta com foto de perfil
- Criar, editar e eliminar receitas
- Upload de imagens para receitas
- Comentar e avaliar receitas (1-5 estrelas)
- Pesquisar receitas por ingredientes/categorias
- Perfil personalizado com preferências

### Para Administradores
- Dashboard com estatísticas
- Gerir utilizadores (ativar/desativar/promover)
- Moderar receitas
- Gerir comentários reportados
- Acesso a todas as funcionalidades

## Testes Manuais

### 1. Teste de Registo
1. Ir para `/register.php`
2. Preencher formulário com dados válidos
3. Fazer upload de uma foto (opcional)
4. Verificar que conta é criada com sucesso

### 2. Teste de Login
1. Ir para `/login.php`
2. Usar credenciais de teste ou conta criada
3. Verificar redirecionamento para homepage
4. Confirmar que menu mostra opções de utilizador logado

### 3. Teste de Receitas
1. Com utilizador logado, clicar em "Criar Receita"
2. Preencher todos os campos obrigatórios
3. Fazer upload de uma imagem
4. Publicar receita
5. Verificar que aparece na homepage
6. Testar edição e eliminação (apenas autor/admin)

### 4. Teste de Comentários e Avaliações
1. Entrar numa receita
2. Dar uma avaliação (1-5 estrelas)
3. Escrever um comentário
4. Verificar que aparecem na página
5. Testar "reportar comentário"

### 5. Teste de Admin
1. Fazer login como admin
2. Aceder a `/admin.php`
3. Verificar dashboard com estatísticas
4. Testar gestão de utilizadores
5. Testar eliminação de receitas
6. Verificar comentários reportados

### 6. Teste Responsivo
1. Abrir site no telemóvel/tablet
2. Verificar que layout se adapta
3. Testar formulários em ecrãs pequenos
4. Confirmar que botões têm tamanho adequado

## Segurança Implementada

- **SQL Injection:** Prepared statements em todas as queries
- **CSRF:** Tokens CSRF em todos os formulários
- **Passwords:** Hash com `password_hash()` (bcrypt)
- **Upload:** Validação de tipo MIME e extensão
- **Sessions:** Regeneração de ID após login
- **Input:** Sanitização com `htmlspecialchars()`

## Problemas Conhecidos

1. **Email de verificação:** Não implementado (requer configuração SMTP)
2. **Rate limiting:** Não implementado (recomendado em produção)
3. **Cache:** Sem cache de imagens/conteúdo
4. **CDN:** Imagens servidas localmente

## Melhorias Futuras

- [ ] Sistema de favoritos
- [ ] Lista de compras automática
- [ ] Exportar receitas para PDF
- [ ] Notificações push
- [ ] API REST completa
- [ ] Integração com redes sociais
- [ ] Sugestões por IA
- [ ] Modo PWA (instalável)

## Suporte

Para questões técnicas ou bugs, contactar o desenvolvedor ou criar issue no repositório.

---

**ChefGuedes** - Desenvolvido com ❤️ usando PHP, MySQL e muito café ☕