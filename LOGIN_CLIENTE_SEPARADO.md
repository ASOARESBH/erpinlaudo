# Login Separado para Clientes - V6.1

## 📋 Resumo

Agora o sistema possui **duas páginas de login separadas**:

1. **login.php** - Login administrativo (administradores e usuários internos)
2. **login_cliente.php** - Login exclusivo para clientes (Portal do Cliente)

---

## 🎯 O Que Mudou

### **Antes (V6.0)**
- Uma única página de login para todos
- Clientes e administradores usavam a mesma tela
- Confusão sobre qual login usar

### **Agora (V6.1)**
- **Duas páginas de login separadas**
- Design diferenciado para cada público
- Redirecionamento automático correto
- Bloqueio de clientes no login administrativo

---

## 🔐 Páginas de Login

### **1. Login Administrativo (login.php)**

**URL**: `http://seudominio.com/login.php`

**Quem usa**: Administradores e usuários internos da INLAUDO

**Credenciais**:
- E-mail: financeiro@inlaudo.com.br
- Senha: Admin259087@

**Design**:
- Cor azul (#2563eb)
- Layout split-screen
- Logo INLAUDO
- Link para Portal do Cliente no rodapé

**Funcionalidades**:
- Login com e-mail e senha
- Bloqueio automático de clientes
- Redirecionamento para dashboard administrativo (index.php)
- Mensagem de erro se cliente tentar acessar

### **2. Login do Cliente (login_cliente.php)**

**URL**: `http://seudominio.com/login_cliente.php`

**Quem usa**: Clientes da INLAUDO

**Credenciais**:
- CNPJ: Apenas números (ex: 12345678000190)
- Senha: 123 (padrão, pode ser alterada)

**Design**:
- Cor verde (#10b981)
- Layout split-screen
- Logo INLAUDO
- Informações de primeiro acesso

**Funcionalidades**:
- Login com CNPJ (apenas números)
- Validação de CNPJ (11 ou 14 dígitos)
- Redirecionamento para Portal do Cliente (portal_cliente.php)
- Dicas de primeiro acesso
- Link para suporte

---

## 🚀 Como Usar

### **Para Administradores**

1. Acesse: `http://seudominio.com/login.php`
2. Digite e-mail e senha
3. Clique em "Entrar no Sistema"
4. Será redirecionado para o dashboard administrativo

### **Para Clientes**

1. Acesse: `http://seudominio.com/login_cliente.php`
2. Digite CNPJ (apenas números, sem pontos ou traços)
3. Digite senha (padrão: 123)
4. Clique em "Entrar no Portal"
5. Será redirecionado para o Portal do Cliente

---

## 🔒 Segurança

### **Bloqueio de Acesso Cruzado**

**Clientes tentando acessar login administrativo**:
- Sistema detecta tipo de usuário
- Exibe mensagem: "Clientes devem acessar pelo Portal do Cliente"
- Fornece link direto para login_cliente.php

**Administradores no login do cliente**:
- Não conseguem fazer login (query busca apenas tipo 'cliente')
- Devem usar login.php

### **Validações**

**login.php**:
- Verifica se é cliente antes de permitir login
- Bloqueia clientes com mensagem amigável
- Apenas admin/usuario podem acessar

**login_cliente.php**:
- Verifica se tipo_usuario = 'cliente'
- Verifica se cliente_id está preenchido
- Apenas clientes podem acessar

---

## 🎨 Design

### **Login Administrativo (Azul)**

```
Cor principal: #2563eb (azul)
Gradiente: #2563eb → #1e40af
Estilo: Profissional, corporativo
Público: Interno
```

### **Login do Cliente (Verde)**

```
Cor principal: #10b981 (verde)
Gradiente: #10b981 → #059669
Estilo: Amigável, acessível
Público: Externo (clientes)
```

---

## 📂 Arquivos

### **Novos**:
1. `login_cliente.php` - Login exclusivo para clientes

### **Atualizados**:
1. `login.php` - Bloqueio de clientes + link para login_cliente.php

---

## 🔄 Fluxo de Redirecionamento

### **Cliente tenta acessar login.php**

```
Cliente digita CNPJ em login.php
↓
Sistema verifica tipo de usuário
↓
Detecta tipo = 'cliente'
↓
Exibe erro: "Clientes devem acessar pelo Portal do Cliente"
↓
Cliente clica no link
↓
Redireciona para login_cliente.php
↓
Cliente faz login corretamente
↓
Redireciona para portal_cliente.php
```

### **Administrador tenta acessar login_cliente.php**

```
Admin digita e-mail em login_cliente.php
↓
Query busca apenas tipo = 'cliente'
↓
Não encontra usuário
↓
Exibe erro: "CNPJ ou senha incorretos"
↓
Admin deve usar login.php
```

---

## 📱 Responsividade

Ambas as páginas são **100% responsivas**:

**Desktop**:
- Layout split-screen (2 colunas)
- Logo à esquerda, formulário à direita

**Mobile**:
- Layout em coluna única
- Logo no topo
- Formulário abaixo

---

## 🐛 Solução de Problemas

### Cliente não consegue acessar login_cliente.php

**Verificações**:
1. ✅ URL está correta? (login_cliente.php)
2. ✅ Arquivo foi enviado para o servidor?
3. ✅ CNPJ tem 11 ou 14 dígitos?
4. ✅ Senha é 123?

### Cliente tenta usar login.php

**Solução**:
- Sistema bloqueia automaticamente
- Exibe link para login_cliente.php
- Cliente clica e acessa corretamente

### Administrador tenta usar login_cliente.php

**Solução**:
- Sistema não encontra usuário (query busca apenas clientes)
- Administrador deve usar login.php

---

## ✅ Checklist de Instalação

- [ ] Upload do arquivo `login_cliente.php`
- [ ] Atualização do arquivo `login.php`
- [ ] Teste de login administrativo (login.php)
- [ ] Teste de login do cliente (login_cliente.php)
- [ ] Teste de bloqueio cruzado
- [ ] Link entre as páginas funcionando

---

## 📊 Comparação

| Característica | login.php | login_cliente.php |
|---|---|---|
| **Público** | Interno | Externo (clientes) |
| **Cor** | Azul (#2563eb) | Verde (#10b981) |
| **Login** | E-mail | CNPJ |
| **Senha** | Personalizada | Padrão: 123 |
| **Destino** | index.php | portal_cliente.php |
| **Tipo Usuário** | admin, usuario | cliente |
| **Bloqueio** | Bloqueia clientes | Bloqueia admin/usuario |

---

## 🎯 Benefícios

### **Separação Clara**:
- ✅ Clientes sabem onde acessar
- ✅ Administradores têm login próprio
- ✅ Sem confusão

### **Segurança**:
- ✅ Bloqueio de acesso cruzado
- ✅ Validações específicas
- ✅ Queries isoladas

### **UX Melhorada**:
- ✅ Design específico para cada público
- ✅ Mensagens personalizadas
- ✅ Dicas contextuais

### **Profissionalismo**:
- ✅ Identidade visual separada
- ✅ Experiência otimizada
- ✅ Comunicação clara

---

## 🔗 Links Úteis

**Login Administrativo**: `http://seudominio.com/login.php`  
**Login do Cliente**: `http://seudominio.com/login_cliente.php`  
**Portal do Cliente**: `http://seudominio.com/portal_cliente.php`  
**Dashboard Admin**: `http://seudominio.com/index.php`

---

## 📞 Suporte

Para problemas técnicos:
- Consulte a documentação completa
- Verifique os logs de erro
- Entre em contato: suporte@inlaudo.com.br

---

## 🎉 Conclusão

O sistema agora possui **login separado para clientes**, proporcionando:

- ✅ Melhor experiência do usuário
- ✅ Segurança aprimorada
- ✅ Identidade visual clara
- ✅ Navegação intuitiva

---

**Versão**: 6.1 (Login Separado)  
**Data**: 22/12/2025  
**Sistema**: ERP INLAUDO  
**Status**: ✅ Pronto para Uso
